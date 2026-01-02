<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $config = $this->record;
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Détails de la Configuration
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Utilisateur</p>
                    <p class="font-semibold">{{ $config->user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Période</p>
                    <p class="font-semibold">{{ ucfirst($config->period_type) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Taux de Gain</p>
                    <p class="font-semibold">{{ $config->win_rate }}%</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Paris par Jour</p>
                    <p class="font-semibold">{{ $config->daily_bet_count }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Plage de Mise</p>
                    <p class="font-semibold">{{ number_format($config->min_bet) }} - {{ number_format($config->max_bet) }} FCFA</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Statut</p>
                    <p>
                        @if($config->is_active)
                            <x-filament::badge color="success">Actif</x-filament::badge>
                        @else
                            <x-filament::badge color="danger">Inactif</x-filament::badge>
                        @endif
                    </p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Filtres
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2 block">Type de Période</label>
                    <select wire:model.live="periodType" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="daily">Journalier</option>
                        <option value="weekly">Hebdomadaire</option>
                        <option value="monthly">Mensuel</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2 block">Date de Début</label>
                    <input type="date" wire:model.live="startDate" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2 block">Date de Fin</label>
                    <input type="date" wire:model.live="endDate" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>
        </x-filament::section>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-filament::section class="h-full">
                <div class="text-center">
                    <p class="text-sm text-gray-500 mb-2">Total Paris</p>
                    <p class="text-3xl font-bold text-primary-600">{{ number_format($stats['total_bets']) }}</p>
                    <p class="text-xs text-gray-400 mt-2">
                        <span class="text-green-600">{{ number_format($stats['total_wins']) }}</span> gains /
                        <span class="text-red-600">{{ number_format($stats['total_losses']) }}</span> pertes
                    </p>
                </div>
            </x-filament::section>

            <x-filament::section class="h-full">
                <div class="text-center">
                    <p class="text-sm text-gray-500 mb-2">Taux de Gain</p>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($stats['avg_win_rate'], 1) }}%</p>
                    <p class="text-xs text-gray-400 mt-2">Moyenne</p>
                </div>
            </x-filament::section>

            <x-filament::section class="h-full">
                <div class="text-center">
                    <p class="text-sm text-gray-500 mb-2">Montant Total Misé</p>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_bet_amount'], 0) }}</p>
                    <p class="text-xs text-gray-400 mt-2">FCFA</p>
                </div>
            </x-filament::section>

            <x-filament::section class="h-full">
                <div class="text-center">
                    <p class="text-sm text-gray-500 mb-2">Résultat Net</p>
                    <p class="text-3xl font-bold {{ $stats['net_amount'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($stats['net_amount'], 0) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-2">FCFA</p>
                </div>
            </x-filament::section>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-filament::section>
                <x-slot name="heading">
                    Total Gains
                </x-slot>

                <p class="text-2xl font-bold text-green-600 mb-4">{{ number_format($stats['total_win_amount'], 0) }} FCFA</p>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    @php
                        $total = $stats['total_win_amount'] + $stats['total_loss_amount'];
                        $winPercent = $total > 0 ? ($stats['total_win_amount'] / $total) * 100 : 0;
                    @endphp
                    <div class="bg-green-600 h-3 rounded-full transition-all duration-500" style="width: {{ $winPercent }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ number_format($winPercent, 1) }}% du total</p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Total Pertes
                </x-slot>

                <p class="text-2xl font-bold text-red-600 mb-4">{{ number_format($stats['total_loss_amount'], 0) }} FCFA</p>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    @php
                        $lossPercent = $total > 0 ? ($stats['total_loss_amount'] / $total) * 100 : 0;
                    @endphp
                    <div class="bg-red-600 h-3 rounded-full transition-all duration-500" style="width: {{ $lossPercent }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ number_format($lossPercent, 1) }}% du total</p>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">
                Évolution des Revenus
            </x-slot>

            <div style="height: 400px;" wire:ignore x-data="chartComponent" @chart-data-updated.window="updateChart($event.detail.chartData)">
                <canvas id="demoChart"></canvas>
            </div>
        </x-filament::section>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chartComponent', () => ({
                chart: null,
                chartData: @json($this->getChartData()),

                init() {
                    console.log('Chart component initialized');
                    console.log('Chart data:', this.chartData);
                    this.$nextTick(() => {
                        this.createChart();
                    });

                    // Listen for Livewire updates
                    window.addEventListener('chartDataUpdated', (event) => {
                        console.log('Chart data updated via Livewire', event.detail);
                        this.updateChart(event.detail.chartData);
                    });
                },

                createChart() {
                    const ctx = document.getElementById('demoChart');
                    if (!ctx) {
                        console.error('Canvas element not found');
                        return;
                    }

                    // Destroy existing chart
                    if (this.chart) {
                        console.log('Destroying existing chart');
                        this.chart.destroy();
                        this.chart = null;
                    }

                    if (!this.chartData.labels || this.chartData.labels.length === 0) {
                        console.warn('No data available to display');
                        ctx.getContext('2d').clearRect(0, 0, ctx.width, ctx.height);
                        const parent = ctx.parentElement;
                        parent.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><p>Aucune donnée disponible pour la période sélectionnée</p></div>';
                        return;
                    }

                    console.log('Creating new chart with', this.chartData.labels.length, 'data points');

                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: this.chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += new Intl.NumberFormat('fr-FR', {
                                                    style: 'currency',
                                                    currency: 'XAF',
                                                    minimumFractionDigits: 0
                                                }).format(context.parsed.y);
                                            }
                                            return label;
                                        }
                                    }
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return new Intl.NumberFormat('fr-FR', {
                                                minimumFractionDigits: 0
                                            }).format(value) + ' FCFA';
                                        }
                                    }
                                }
                            },
                            interaction: {
                                mode: 'nearest',
                                axis: 'x',
                                intersect: false
                            }
                        }
                    });

                    console.log('Chart created successfully');
                },

                updateChart(newData) {
                    this.chartData = newData;
                    this.createChart();
                }
            }));
        });
    </script>
    @endpush
</x-filament-panels::page>
