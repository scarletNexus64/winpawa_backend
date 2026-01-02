<?php

namespace App\Console\Commands;

use App\Models\DemoConfiguration;
use App\Services\DemoSimulationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:generate
                            {--config= : ID de la configuration spécifique}
                            {--all : Générer pour toutes les configurations actives}
                            {--days=7 : Nombre de jours à générer}
                            {--clear : Effacer les données existantes avant de générer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Générer les données de simulation pour les configurations de démo';

    protected DemoSimulationService $service;

    public function __construct(DemoSimulationService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Génération des données de démonstration...');
        $this->newLine();

        $configurations = $this->getConfigurations();

        if ($configurations->isEmpty()) {
            $this->error('❌ Aucune configuration trouvée.');
            return Command::FAILURE;
        }

        $days = (int) $this->option('days');
        $clear = $this->option('clear');

        $this->info("📊 {$configurations->count()} configuration(s) à traiter");
        $this->newLine();

        $totalGenerated = 0;

        foreach ($configurations as $config) {
            $this->line("Processing: {$config->name} (User: {$config->user->name})");

            try {
                // Clear existing data if requested
                if ($clear) {
                    $deleted = $this->service->clearData($config);
                    $this->comment("  🗑️  Effacé {$deleted} enregistrements existants");
                }

                // Calculate date range
                $endDate = now();
                $startDate = $endDate->copy()->subDays($days - 1);

                // Update config dates if needed
                if (!$config->start_date || $config->start_date->gt($startDate)) {
                    $config->update(['start_date' => $startDate]);
                }

                // Generate data
                $data = $this->service->generateData($config, $startDate, $endDate);
                $totalGenerated += count($data);

                $this->info("  ✅ Généré {$days} jours de données ({$config->daily_bet_count} paris/jour)");

                // Generate weekly aggregates
                $weekStart = $startDate->copy()->startOfWeek();
                while ($weekStart->lte($endDate)) {
                    $this->service->generateWeeklyData($config, $weekStart);
                    $weekStart->addWeek();
                }
                $this->comment("  📅 Agrégats hebdomadaires générés");

                // Generate monthly aggregates
                $monthStart = $startDate->copy()->startOfMonth();
                while ($monthStart->lte($endDate)) {
                    $this->service->generateMonthlyData($config, $monthStart);
                    $monthStart->addMonth();
                }
                $this->comment("  📆 Agrégats mensuels générés");

                $this->newLine();
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur: {$e->getMessage()}");
                $this->newLine();
            }
        }

        $this->newLine();
        $this->info("🎉 Terminé! {$totalGenerated} jours de données générés au total.");

        return Command::SUCCESS;
    }

    protected function getConfigurations()
    {
        if ($configId = $this->option('config')) {
            return DemoConfiguration::where('id', $configId)->get();
        }

        if ($this->option('all')) {
            return DemoConfiguration::where('is_active', true)->get();
        }

        // Interactive selection
        $configs = DemoConfiguration::with('user')->get();

        if ($configs->isEmpty()) {
            return collect();
        }

        $choices = $configs->mapWithKeys(function ($config) {
            return [$config->id => "{$config->name} ({$config->user->name})"];
        })->toArray();

        $selected = $this->choice(
            'Quelle configuration voulez-vous traiter ?',
            ['all' => 'Toutes les configurations actives'] + $choices,
            'all'
        );

        if ($selected === 'all') {
            return DemoConfiguration::where('is_active', true)->get();
        }

        $selectedId = array_search($selected, $choices);
        return DemoConfiguration::where('id', $selectedId)->get();
    }
}
