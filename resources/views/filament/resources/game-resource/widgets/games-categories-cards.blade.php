<x-filament-widgets::widget>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($this->getCategories() as $category)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="text-3xl">{{ $category['icon'] }}</div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $category['name'] }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $category['count'] }} jeu{{ $category['count'] > 1 ? 'x' : '' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach($category['games'] as $game)
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <span>{{ $game['icon'] }}</span>
                            <span class="truncate">{{ $game['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
