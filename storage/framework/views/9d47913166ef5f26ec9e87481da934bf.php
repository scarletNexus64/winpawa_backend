<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('heading', null, []); ?> 
            <div class="flex items-center gap-2">
                <span class="text-lg font-bold">🏆 Top 5 Jeux les Plus Joués</span>
            </div>
         <?php $__env->endSlot(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->getGames()->count() > 0): ?>
            <div x-data="{
                currentIndex: 0,
                games: <?php echo e($this->getGames()->count()); ?>,
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->getGames(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="w-full flex-shrink-0">
                                <div class="relative group overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($game->banner): ?>
                                        <img src="<?php echo e(Storage::url($game->banner)); ?>" alt="<?php echo e($game->name); ?>" class="w-full h-64 object-cover">
                                    <?php elseif($game->image): ?>
                                        <img src="<?php echo e(asset('images/' . $game->image)); ?>" alt="<?php echo e($game->name); ?>" class="w-full h-64 object-cover">
                                    <?php elseif($game->thumbnail): ?>
                                        <img src="<?php echo e(Storage::url($game->thumbnail)); ?>" alt="<?php echo e($game->name); ?>" class="w-full h-64 object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-64 bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                                            <span class="text-8xl"><?php echo e($game->type?->icon() ?? '🎮'); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                        <h3 class="font-bold text-2xl mb-2"><?php echo e($game->name); ?></h3>
                                        <p class="text-sm opacity-90 mb-3">
                                            <?php echo e($game->type?->icon() ?? '🎮'); ?> <?php echo e($game->type?->label() ?? 'N/A'); ?>

                                        </p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($game->bets_count > 0): ?>
                                            <div class="flex items-center gap-2">
                                                <span class="bg-primary-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                                    <?php echo e(number_format($game->bets_count)); ?> paris
                                                </span>
                                                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                                                    RTP: <?php echo e($game->rtp); ?>%
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="bg-primary-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($game->is_featured): ?> ⭐ Mis en avant <?php else: ?> 🆕 Nouveau <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->getGames(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button @click="currentIndex = <?php echo e($index); ?>"
                                :class="currentIndex === <?php echo e($index); ?> ? 'bg-primary-500 w-8' : 'bg-gray-300 dark:bg-gray-600 w-2'"
                                class="h-2 rounded-full transition-all duration-300">
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <div class="text-6xl mb-4">🎮</div>
                <p class="text-lg">Aucun jeu disponible</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH /Users/macbookpro/Desktop/Developments/Personnals/winpawa/winpawa_backend/resources/views/filament/widgets/top-games-widget.blade.php ENDPATH**/ ?>