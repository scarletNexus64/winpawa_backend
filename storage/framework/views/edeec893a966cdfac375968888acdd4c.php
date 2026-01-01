
<script>
    // Add particle effect to background
    document.addEventListener('DOMContentLoaded', function() {
        // Force dark mode ONLY - Remove light mode completely
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');

        // Prevent light mode toggle - FIXED to avoid infinite loop
        let isPreventingLightMode = false; // Flag to prevent infinite loop

        const preventLightMode = () => {
            // Éviter la boucle infinie en vérifiant si on est déjà en train de modifier
            if (isPreventingLightMode) return;

            const hasDark = document.documentElement.classList.contains('dark');
            const hasLight = document.documentElement.classList.contains('light');

            // Ne modifier que si nécessaire
            if (!hasDark || hasLight) {
                isPreventingLightMode = true;

                if (!hasDark) {
                    document.documentElement.classList.add('dark');
                }
                if (hasLight) {
                    document.documentElement.classList.remove('light');
                }

                // Reset flag après un court délai
                setTimeout(() => {
                    isPreventingLightMode = false;
                }, 10);
            }
        };

        // Monitor and enforce dark mode - OPTIMIZED
        const observer = new MutationObserver((mutations) => {
            // Ignorer les mutations si on est déjà en train de prévenir
            if (!isPreventingLightMode) {
                preventLightMode();
            }
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });

        // Override any light mode settings in localStorage
        if (localStorage.getItem('theme') !== 'dark') {
            localStorage.setItem('theme', 'dark');
        }

        // Particules désactivées pour optimisation des performances
        // Si vous voulez les activer, décommentez le code ci-dessous

        /*
        let particleCount = 0;
        const MAX_PARTICLES = 10;

        const createParticle = () => {
            if (particleCount >= MAX_PARTICLES) return;
            particleCount++;
            const particle = document.createElement('div');
            const colors = ['rgba(251, 191, 36, 0.3)', 'rgba(217, 70, 239, 0.2)'];
            const randomColor = colors[Math.floor(Math.random() * colors.length)];

            particle.style.cssText = `
                position: fixed;
                width: 2px;
                height: 2px;
                background: ${randomColor};
                border-radius: 50%;
                pointer-events: none;
                z-index: 0;
                animation: float 25s linear forwards;
                left: ${Math.random() * 100}vw;
                top: 100vh;
            `;

            document.body.appendChild(particle);
            setTimeout(() => {
                particle?.remove();
                particleCount--;
            }, 25000);
        };

        setInterval(createParticle, 3000);
        */

        // Add floating animation keyframes
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0% {
                    transform: translateY(0) rotate(0deg);
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(-100vh) rotate(720deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Animate stat numbers on load
        const animateValue = (element, start, end, duration) => {
            const range = end - start;
            const increment = end > start ? 1 : -1;
            const stepTime = Math.abs(Math.floor(duration / range));
            let current = start;
            
            const timer = setInterval(() => {
                current += increment * Math.ceil(range / 50);
                if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                    current = end;
                    clearInterval(timer);
                }
                element.textContent = new Intl.NumberFormat('fr-FR').format(current);
            }, stepTime);
        };

        // Initialize tooltips with gaming style
        const initGamingTooltips = () => {
            document.querySelectorAll('[title]').forEach(el => {
                el.style.cursor = 'help';
            });
        };

        initGamingTooltips();

        // Add hover sound effect (optional)
        const addHoverEffects = () => {
            document.querySelectorAll('.fi-sidebar-item, .fi-btn').forEach(el => {
                el.addEventListener('mouseenter', () => {
                    // Subtle visual feedback
                    el.style.transition = 'all 0.2s ease';
                });
            });
        };

        addHoverEffects();

        // Refresh animations on Livewire updates
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('message.processed', () => {
                initGamingTooltips();
                addHoverEffects();
                preventLightMode(); // Ensure dark mode after Livewire updates
            });
        }

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Table row animations désactivées pour performance

        // Ripple effect désactivé pour performance

        // Performance monitoring
        if ('performance' in window) {
            window.addEventListener('load', () => {
                const perfData = performance.timing;
                const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
                console.log(`%c Page loaded in ${pageLoadTime}ms`, 'color: #10b981; font-weight: bold;');
            });
        }
    });

    // Console branding with enhanced styling
    console.log('%c WINPAWA Admin Panel ', 'background: linear-gradient(90deg, #fbbf24, #d946ef, #8b5cf6); color: white; font-size: 24px; font-weight: bold; padding: 15px 30px; border-radius: 12px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);');
    console.log('%c Gaming Platform Management System - Dark Mode Only ', 'color: #fbbf24; font-size: 14px; font-weight: bold;');
    console.log('%c Powered by Filament v3 ', 'color: #d946ef; font-size: 12px;');
</script>

<?php /**PATH /Users/macbookpro/Desktop/Developments/Personnals/winpawa/resources/views/filament/custom-scripts.blade.php ENDPATH**/ ?>