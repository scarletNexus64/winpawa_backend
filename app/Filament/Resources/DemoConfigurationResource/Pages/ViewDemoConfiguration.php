<?php

namespace App\Filament\Resources\DemoConfigurationResource\Pages;

use App\Filament\Resources\DemoConfigurationResource;
use App\Models\DemoSimulatedData;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Carbon\Carbon;

class ViewDemoConfiguration extends ViewRecord
{
    protected static string $resource = DemoConfigurationResource::class;

    protected static string $view = 'filament.resources.demo-configuration-resource.pages.view-demo-configuration';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $periodType = 'daily';

    public function mount(int | string $record): void
    {
        parent::mount($record);

        // Auto-detect date range from available simulated data
        $dateRange = DemoSimulatedData::where('demo_configuration_id', $record)
            ->selectRaw('MIN(date) as min_date, MAX(date) as max_date')
            ->first();

        if ($dateRange && $dateRange->min_date && $dateRange->max_date) {
            $this->startDate = Carbon::parse($dateRange->min_date)->format('Y-m-d');
            $this->endDate = Carbon::parse($dateRange->max_date)->format('Y-m-d');
        } else {
            // Fallback to default range if no data exists
            $this->startDate = now()->subDays(30)->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getStats(): array
    {
        $query = DemoSimulatedData::where('demo_configuration_id', $this->record->id)
            ->where('period_type', $this->periodType);

        if ($this->startDate) {
            $query->where('date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->where('date', '<=', $this->endDate);
        }

        $data = $query->get();

        $totalBets = $data->sum('bet_count');
        $totalWins = $data->sum('win_count');
        $totalLosses = $data->sum('loss_count');

        return [
            'total_bets' => $totalBets,
            'total_wins' => $totalWins,
            'total_losses' => $totalLosses,
            'total_bet_amount' => $data->sum('total_bet_amount'),
            'total_win_amount' => $data->sum('total_win_amount'),
            'total_loss_amount' => $data->sum('total_loss_amount'),
            'net_amount' => $data->sum('net_amount'),
            'avg_win_rate' => $totalBets > 0 ? ($totalWins / $totalBets) * 100 : 0,
        ];
    }

    public function getChartData(): array
    {
        $query = DemoSimulatedData::where('demo_configuration_id', $this->record->id)
            ->where('period_type', $this->periodType)
            ->orderBy('date');

        if ($this->startDate) {
            $query->where('date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->where('date', '<=', $this->endDate);
        }

        $data = $query->get();

        return [
            'labels' => $data->map(fn($item) => Carbon::parse($item->date)->format('d/m/Y'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Mises',
                    'data' => $data->pluck('total_bet_amount')->map(fn($val) => (float)$val)->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Gains',
                    'data' => $data->pluck('total_win_amount')->map(fn($val) => (float)$val)->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Pertes',
                    'data' => $data->pluck('total_loss_amount')->map(fn($val) => (float)$val)->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Net',
                    'data' => $data->pluck('net_amount')->map(fn($val) => (float)$val)->toArray(),
                    'borderColor' => '#d946ef',
                    'backgroundColor' => 'rgba(217, 70, 239, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 3,
                ],
            ],
        ];
    }

    public function updatedPeriodType(): void
    {
        $this->dispatch('chartDataUpdated', chartData: $this->getChartData());
    }

    public function updatedStartDate(): void
    {
        $this->dispatch('chartDataUpdated', chartData: $this->getChartData());
    }

    public function updatedEndDate(): void
    {
        $this->dispatch('chartDataUpdated', chartData: $this->getChartData());
    }
}
