<?php

namespace App\Filament\Widgets;

use App\Models\DemoSimulatedData;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class DemoRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution des Gains/Pertes Simulées (30 derniers jours)';

    protected static ?int $sort = 11;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'daily';

    protected function getData(): array
    {
        $periodType = $this->filter ?? 'daily';

        // Get data for the last 30 days
        $startDate = now()->subDays(30);
        $endDate = now();

        $data = DemoSimulatedData::where('period_type', $periodType)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $labels = $data->map(fn ($item) => Carbon::parse($item->date)->format('d/m'))->toArray();
        $betAmounts = $data->pluck('total_bet_amount')->map(fn ($val) => (float)$val)->toArray();
        $winAmounts = $data->pluck('total_win_amount')->map(fn ($val) => (float)$val)->toArray();
        $lossAmounts = $data->pluck('total_loss_amount')->map(fn ($val) => (float)$val)->toArray();
        $netAmounts = $data->pluck('net_amount')->map(fn ($val) => (float)$val)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Mises',
                    'data' => $betAmounts,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Gains',
                    'data' => $winAmounts,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Pertes',
                    'data' => $lossAmounts,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Net',
                    'data' => $netAmounts,
                    'borderColor' => '#d946ef',
                    'backgroundColor' => 'rgba(217, 70, 239, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Quotidien',
            'weekly' => 'Hebdomadaire',
            'monthly' => 'Mensuel',
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(217, 70, 239, 0.1)',
                    ],
                    'ticks' => [
                        'callback' => null,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}
