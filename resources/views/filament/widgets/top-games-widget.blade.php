<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="text-lg font-bold">🏆 Top 5 Jeux les Plus Joués</span>
            </div>
        </x-slot>

        @if($this->getGames()->count() > 0)
            <div x-data="{
                currentIndex: 0,
                games: {{ $this->getGames()->count() }},
                autoplay: null,
                init() {
                    this.startAutoplay();
                },
                startAutoplay() {
                    this.autoplay = setInterval(() => {
                        this.next();
                    }, 3000);
                },
                stopAutoplay() {
                    clearInterval(this.autoplay);
                },
                next() {
                    this.currentIndex = (this.currentIndex + 1) % this.games;
                },
                prev() {
                    this.currentIndex = (this.currentIndex - 1 + this.games) % this.games;
                }
            }"
            @mouseenter="stopAutoplay()"
            @mouseleave="startAutoplay()"
            class="relative">
                <!-- Carousel Container -->
                <div class="overflow-hidden rounded-lg">
                    <div class="flex transition-transform duration-500 ease-in-out"
                         :style="`transform: translateX(-${currentIndex * 100}%)`">
                        @foreach ($this->getGames() as $index => $game)
                            <div class="w-full flex-shrink-0">
                                <div class="relative group overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                    @if($game->banner)
                                        <img src="{{ Storage::url($game->banner) }}" alt="{{ $game->name }}" class="w-full h-64 object-cover">
                                    @elseif($game->image)
                                        <img src="{{ asset('images/' . $game->image) }}" alt="{{ $game->name }}" class="w-full h-64 object-cover">
                                    @elseif($game->thumbnail)
                                        <img src="{{ Storage::url($game->thumbnail) }}" alt="{{ $game->name }}" class="w-full h-64 object-cover">
                                    @else
                                        <div class="w-full h-64 bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                                            <span class="text-8xl">{{ $game->type?->icon() ?? '🎮' }}</span>
                                        </div>
                                    @endif

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                        <h3 class="font-bold text-2xl mb-2">{{ $game->name }}</h3>
                                        <p class="text-sm opacity-90 mb-3">
                                            {{ $game->type?->icon() ?? '🎮' }} {{ $game->type?->label() ?? 'N/A' }}
                                        </p>
                                        @if($game->bets_count > 0)
                                            <div class="flex items-center gap-2">
                                                <span class="bg-primary-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                                    {{ number_format($game->bets_count) }} paris
                                                </span>
                                                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                                                    RTP: {{ $game->rtp }}%
                                                </span>
                                            </div>
                                        @else
                                            <span class="bg-primary-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                                @if($game->is_featured) ⭐ Mis en avant @else 🆕 Nouveau @endif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <button @click="prev()"
                        class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 dark:bg-gray-800/90 hover:bg-white dark:hover:bg-gray-800 text-gray-800 dark:text-white rounded-full p-2 shadow-lg transition-all z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <button @click="next()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 dark:bg-gray-800/90 hover:bg-white dark:hover:bg-gray-800 text-gray-800 dark:text-white rounded-full p-2 shadow-lg transition-all z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Dots Indicators -->
                <div class="flex justify-center gap-2 mt-4">
                    @foreach ($this->getGames() as $index => $game)
                        <button @click="currentIndex = {{ $index }}"
                                :class="currentIndex === {{ $index }} ? 'bg-primary-500 w-8' : 'bg-gray-300 dark:bg-gray-600 w-2'"
                                class="h-2 rounded-full transition-all duration-300">
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <div class="text-6xl mb-4">🎮</div>
                <p class="text-lg">Aucun jeu disponible</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
