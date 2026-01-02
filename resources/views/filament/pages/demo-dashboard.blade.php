<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::card>
            <form wire:submit.prevent="$refresh">
                {{ $this->form }}
            </form>
        </x-filament::card>

        @php
            $stats = $this->getStats();
            $config = $this->getConfiguration();
        @endphp

        @if($config)
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $config->name }}</h3>
                        <p class="text-sm text-gray-500">Utilisateur: {{ $config->user->name }}</p>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center gap-2">
                            @if($config->is_active)
                                <x-filament::badge color="success">Actif</x-filament::badge>
                            @else
                                <x-filament::badge color="danger">Inactif</x-filament::badge>
                            @endif
                            <span class="text-sm text-gray-500">
                                {{ $config->start_date->format('d/m/Y') }}
                                @if($config->end_date)
                                    - {{ $config->end_date->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </x-filament::card>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-filament::card>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Paris Totaux</p>
                        <p class="text-2xl font-bold">{{ number_format($stats['total_bets']) }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            <span class="text-green-600">{{ number_format($stats['total_wins']) }}</span> gagnes /
                            <span class="text-red-600">{{ number_format($stats['total_losses']) }}</span> perdus
                        </p>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Taux de Victoire</p>
                        <p class="text-2xl font-bold">{{ number_format($stats['avg_win_rate'], 1) }}%</p>
                        <p class="text-xs text-gray-400 mt-1">Moyenne de la periode</p>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Montant Mise</p>
                        <p class="text-2xl font-bold">{{ number_format($stats['total_bet_amount'], 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-400 mt-1">FCFA</p>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Resultat Net</p>
                        <p class="text-2xl font-bold {{ $stats['net_amount'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($stats['net_amount'], 0, ',', ' ') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">FCFA</p>
                    </div>
                </x-filament::card>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <x-filament::card>
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-500">Gains Totaux</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['total_win_amount'], 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-400 mt-1">FCFA</p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                            $total = $stats['total_win_amount'] + $stats['total_loss_amount'];
                            $winPercent = $total > 0 ? ($stats['total_win_amount'] / $total) * 100 : 0;
                        @endphp
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $winPercent }}%"></div>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-500">Pertes Totales</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($stats['total_loss_amount'], 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-400 mt-1">FCFA</p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                            $lossPercent = $total > 0 ? ($stats['total_loss_amount'] / $total) * 100 : 0;
                        @endphp
                        <div class="bg-red-600 h-2 rounded-full" style="width: {{ $lossPercent }}%"></div>
                    </div>
                </x-filament::card>
            </div>

            <x-filament::card>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold">Evolution des Gains et Pertes</h3>
                    <p class="text-sm text-gray-500">Visualisation temporelle des performances</p>
                </div>
                <div>
                    <canvas id="demoChart" height="100"></canvas>
                </div>
            </x-filament::card>

            @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('demoChart');
                    if (ctx) {
                        const chartData = @json($this->getChartData());

                        new Chart(ctx, {
                            type: 'line',
                            data: chartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
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
                    }
                });
            </script>
            @endpush
        @else
            <x-filament::card>
                <div class="text-center py-12">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Aucune configuration selectionnee</h3>
                    <p class="text-gray-500">Veuillez creer et selectionner une configuration pour voir les statistiques.</p>
                    <div class="mt-4">
                        <a href="{{ route('filament.admin.resources.demo-configurations.create') }}" class="text-primary-600 hover:text-primary-700">
                            Creer une configuration
                        </a>
                    </div>
                </div>
            </x-filament::card>
        @endif
    </div>
</x-filament-panels::page>
