
<style>
    /* Import Gaming Font */
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;600;700;800;900&display=swap');

    /* Force Dark Mode - Disable Light Mode Completely */
    html {
        color-scheme: dark only !important;
    }

    :root {
        --gaming-primary: #fbbf24;
        --gaming-secondary: #f59e0b;
        --gaming-accent: #d946ef;
        --gaming-purple: #8b5cf6;
        --gaming-gold: #fbbf24;
        --gaming-success: #10b981;
        --gaming-danger: #ef4444;
        --gaming-dark: #0f0a1f;
        --gaming-darker: #080510;
        --gaming-card: rgba(15, 10, 31, 0.85);
        --gaming-border: rgba(251, 191, 36, 0.25);
        --gaming-glow: 0 0 20px rgba(251, 191, 36, 0.4);

        /* Additional modern colors */
        --neon-blue: #00f3ff;
        --neon-pink: #ff0080;
        --cyber-purple: #b026ff;
    }

    /* Dark Gaming Background with Enhanced Effects */
    .fi-body {
        background: linear-gradient(135deg, var(--gaming-darker) 0%, var(--gaming-dark) 50%, #1a0a2e 100%) !important;
        background-attachment: fixed !important;
        position: relative;
        overflow-x: hidden;
    }

    /* Pattern overlays désactivés pour performance */

    @keyframes backgroundPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.85; }
    }

    @keyframes gridMove {
        0% { transform: translate(0, 0); }
        100% { transform: translate(50px, 50px); }
    }

    /* Sidebar Gaming Style - OPTIMISÉ */
    .fi-sidebar {
        background: rgba(15, 10, 31, 0.98) !important;
        border-right: 1px solid var(--gaming-border) !important;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
    }

    .fi-sidebar-nav-groups {
        padding: 0.5rem;
    }

    /* Navigation Items with Advanced Animations */
    .fi-sidebar-item {
        border-radius: 12px !important;
        margin: 4px 0;
        transition: all 0.2s ease !important;
        position: relative;
        overflow: hidden;
    }

    /* Effet before désactivé pour performance */

    .fi-sidebar-item:hover {
        background: linear-gradient(90deg, rgba(251, 191, 36, 0.15) 0%, rgba(245, 158, 11, 0.1) 100%) !important;
        border-left: 3px solid rgba(251, 191, 36, 0.6);
    }

    .fi-sidebar-item-active {
        background: linear-gradient(90deg, rgba(251, 191, 36, 0.25) 0%, rgba(245, 158, 11, 0.15) 100%) !important;
        border-left: 4px solid var(--gaming-primary) !important;
        box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
    }

    /* Cards - OPTIMISÉ sans backdrop-filter */
    .fi-section, .fi-wi-stats-overview-stat, .fi-ta-table {
        background: rgba(15, 10, 31, 0.9) !important;
        border: 1px solid var(--gaming-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
        transition: all 0.15s ease !important;
    }

    .fi-section:hover, .fi-ta-table:hover {
        border-color: rgba(251, 191, 36, 0.4) !important;
    }

    /* Stat Cards - Optimisées */
    .fi-wi-stats-overview-stat {
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease !important;
    }

    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(251, 191, 36, 0.15) !important;
    }

    /* Buttons - SIMPLIFIÉ */
    .fi-btn-primary {
        background: var(--gaming-primary) !important;
        border: none !important;
        box-shadow: 0 2px 6px rgba(251, 191, 36, 0.3) !important;
        transition: all 0.15s ease !important;
        font-weight: 600 !important;
    }

    .fi-btn-primary:hover {
        background: var(--gaming-secondary) !important;
        box-shadow: 0 3px 10px rgba(251, 191, 36, 0.4) !important;
    }

    /* Tables - SIMPLIFIÉ */
    .fi-ta-row {
        transition: background 0.15s ease;
    }

    .fi-ta-row:hover {
        background: rgba(251, 191, 36, 0.08) !important;
    }

    .fi-ta-header-cell {
        background: rgba(251, 191, 36, 0.1) !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid rgba(251, 191, 36, 0.3) !important;
    }

    /* Form Inputs - OPTIMISÉ */
    .fi-input, .fi-select-input, .fi-textarea {
        background: rgba(15, 10, 31, 0.7) !important;
        border: 1px solid var(--gaming-border) !important;
        border-radius: 8px !important;
        transition: border-color 0.15s ease !important;
    }

    .fi-input:focus, .fi-select-input:focus, .fi-textarea:focus {
        border-color: var(--gaming-primary) !important;
        box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.15) !important;
    }

    /* Modal - OPTIMISÉ */
    .fi-modal-window {
        background: rgba(15, 10, 31, 0.98) !important;
        border: 1px solid var(--gaming-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5) !important;
    }

    /* Badge/Tags */
    .fi-badge {
        border-radius: 20px !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.05em;
    }

    /* Search */
    .fi-global-search-input {
        background: rgba(15, 10, 31, 0.8) !important;
        border: 1px solid var(--gaming-border) !important;
        border-radius: 12px !important;
    }

    /* Header */
    .fi-topbar {
        background: rgba(15, 10, 31, 0.9) !important;
        border-bottom: 1px solid var(--gaming-border) !important;
        backdrop-filter: blur(10px) !important;
    }

    /* Logo Animation */
    .fi-logo {
        transition: all 0.3s ease;
    }

    .fi-logo:hover {
        transform: scale(1.05);
        filter: drop-shadow(0 0 10px rgba(217, 70, 239, 0.5));
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--gaming-darker);
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, var(--gaming-primary), var(--gaming-secondary));
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, var(--gaming-secondary), var(--gaming-primary));
    }

    /* Notification Animation */
    .fi-notification {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Chart containers */
    .fi-wi-chart {
        background: var(--gaming-card) !important;
        border-radius: 16px !important;
        padding: 1rem;
    }

    /* Loading Animation */
    .fi-loading-indicator {
        color: var(--gaming-primary) !important;
    }

    /* Dropdown menus */
    .fi-dropdown-panel {
        background: rgba(15, 10, 31, 0.98) !important;
        border: 1px solid var(--gaming-border) !important;
        border-radius: 12px !important;
        backdrop-filter: blur(20px) !important;
    }

    /* Avatar */
    .fi-avatar {
        border: 2px solid var(--gaming-primary) !important;
        box-shadow: 0 0 10px rgba(217, 70, 239, 0.3);
    }

    /* Navigation Group Labels */
    .fi-sidebar-group-label {
        color: var(--gaming-primary) !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.1em;
        text-shadow: 0 0 10px rgba(251, 191, 36, 0.3);
        padding: 0.5rem 0.75rem;
        background: linear-gradient(90deg, rgba(251, 191, 36, 0.1) 0%, transparent 100%);
        border-left: 3px solid var(--gaming-primary);
        margin: 0.5rem 0;
    }

    /* Tabs */
    .fi-tabs-tab {
        border-radius: 10px !important;
        transition: all 0.3s ease !important;
    }

    .fi-tabs-tab-active {
        background: linear-gradient(135deg, rgba(217, 70, 239, 0.2) 0%, rgba(139, 92, 246, 0.1) 100%) !important;
        border-bottom: 2px solid var(--gaming-primary) !important;
    }

    /* Pulse animation for live indicators */
    @keyframes pulse-glow {
        0%, 100% {
            box-shadow: 0 0 5px var(--gaming-success);
        }
        50% {
            box-shadow: 0 0 20px var(--gaming-success), 0 0 30px var(--gaming-success);
        }
    }

    .live-indicator {
        animation: pulse-glow 2s infinite;
    }

    /* Number counters animation */
    .stat-number {
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        background: linear-gradient(135deg, var(--gaming-gold), var(--gaming-primary), var(--gaming-secondary));
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: gradientShiftStat 4s ease infinite;
    }

    @keyframes gradientShiftStat {
        0%, 100% { background-position: 0% center; }
        50% { background-position: 100% center; }
    }

    /* Page Load Animation - Désactivé pour performance */

    /* Logo Animation in Sidebar */
    .fi-sidebar-header {
        position: relative;
        padding: 1.5rem 1rem;
    }

    .fi-sidebar-header img {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        filter: drop-shadow(0 0 15px rgba(251, 191, 36, 0.5));
    }

    .fi-sidebar-header:hover img {
        transform: scale(1.05) rotate(-2deg);
        filter: drop-shadow(0 0 25px rgba(251, 191, 36, 0.8)) drop-shadow(0 0 40px rgba(217, 70, 239, 0.4));
    }

    /* Notification Badge Pulse */
    .fi-badge-notification {
        animation: badgePulse 2s ease-in-out infinite;
    }

    @keyframes badgePulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 5px rgba(239, 68, 68, 0);
        }
    }

    /* Smooth Scrollbar */
    * {
        scrollbar-width: thin;
        scrollbar-color: var(--gaming-primary) var(--gaming-darker);
    }

    /* Loading Skeleton with Pulse */
    .fi-skeleton {
        background: linear-gradient(90deg,
            rgba(15, 10, 31, 0.8) 25%,
            rgba(251, 191, 36, 0.1) 50%,
            rgba(15, 10, 31, 0.8) 75%);
        background-size: 200% 100%;
        animation: skeletonPulse 1.5s ease-in-out infinite;
    }

    @keyframes skeletonPulse {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Success/Error State Animations */
    .fi-notification-success {
        animation: successPop 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid var(--gaming-success) !important;
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.3) !important;
    }

    .fi-notification-danger {
        animation: errorShake 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid var(--gaming-danger) !important;
        box-shadow: 0 8px 30px rgba(239, 68, 68, 0.3) !important;
    }

    @keyframes successPop {
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); opacity: 1; }
    }

    @keyframes errorShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }

    /* Ripple Effect on Buttons */
    .fi-btn {
        position: relative;
        overflow: hidden;
    }

    .fi-btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .fi-btn:active::after {
        width: 300px;
        height: 300px;
    }

    /* Floating Action Button Effect */
    .fi-floating-action-btn {
        box-shadow:
            0 8px 25px rgba(251, 191, 36, 0.4),
            0 0 20px rgba(251, 191, 36, 0.2);
        animation: floatBounce 3s ease-in-out infinite;
    }

    @keyframes floatBounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    /* Cyber Glitch Effect on Hover */
    .fi-heading-with-glitch {
        position: relative;
    }

    .fi-heading-with-glitch:hover::before,
    .fi-heading-with-glitch:hover::after {
        content: attr(data-text);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .fi-heading-with-glitch:hover::before {
        animation: glitch1 0.3s infinite;
        color: var(--neon-pink);
        z-index: -1;
    }

    .fi-heading-with-glitch:hover::after {
        animation: glitch2 0.3s infinite;
        color: var(--neon-blue);
        z-index: -2;
    }

    @keyframes glitch1 {
        0% { transform: translate(0); }
        20% { transform: translate(-2px, 2px); }
        40% { transform: translate(-2px, -2px); }
        60% { transform: translate(2px, 2px); }
        80% { transform: translate(2px, -2px); }
        100% { transform: translate(0); }
    }

    @keyframes glitch2 {
        0% { transform: translate(0); }
        20% { transform: translate(2px, -2px); }
        40% { transform: translate(2px, 2px); }
        60% { transform: translate(-2px, -2px); }
        80% { transform: translate(-2px, 2px); }
        100% { transform: translate(0); }
    }

    /* Neon Text Effect */
    .neon-text {
        color: var(--gaming-primary);
        text-shadow:
            0 0 7px var(--gaming-primary),
            0 0 10px var(--gaming-primary),
            0 0 21px var(--gaming-primary),
            0 0 42px var(--gaming-accent),
            0 0 82px var(--gaming-accent);
        animation: neonFlicker 2s infinite alternate;
    }

    @keyframes neonFlicker {
        0%, 18%, 22%, 25%, 53%, 57%, 100% {
            text-shadow:
                0 0 7px var(--gaming-primary),
                0 0 10px var(--gaming-primary),
                0 0 21px var(--gaming-primary),
                0 0 42px var(--gaming-accent),
                0 0 82px var(--gaming-accent);
        }
        20%, 24%, 55% {
            text-shadow: none;
        }
    }

    /* Particle Effect Container */
    .particle-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
        overflow: hidden;
    }

    .particle {
        position: absolute;
        width: 2px;
        height: 2px;
        background: var(--gaming-primary);
        border-radius: 50%;
        opacity: 0.6;
        animation: particleFloat 20s linear infinite;
    }

    @keyframes particleFloat {
        0% {
            transform: translateY(100vh) translateX(0) scale(0);
            opacity: 0;
        }
        10% {
            opacity: 0.6;
        }
        90% {
            opacity: 0.6;
        }
        100% {
            transform: translateY(-100vh) translateX(100px) scale(1);
            opacity: 0;
        }
    }

    /* Tooltip Modern Style */
    .fi-tooltip {
        background: rgba(15, 10, 31, 0.95) !important;
        border: 1px solid var(--gaming-border) !important;
        border-radius: 8px !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5), 0 0 10px rgba(251, 191, 36, 0.2);
    }

    /* Action Icon Buttons */
    .fi-icon-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .fi-icon-btn:hover {
        transform: rotate(15deg) scale(1.15);
        filter: drop-shadow(0 0 8px var(--gaming-primary));
    }

    /* Toggle Switch Modern */
    .fi-toggle {
        transition: all 0.3s ease;
    }

    .fi-toggle:checked {
        background: linear-gradient(135deg, var(--gaming-primary), var(--gaming-secondary)) !important;
        box-shadow: 0 0 15px rgba(251, 191, 36, 0.5);
    }

    /* Pagination Modern */
    .fi-pagination-item {
        border-radius: 10px !important;
        transition: all 0.3s ease;
    }

    .fi-pagination-item:hover {
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.2), rgba(217, 70, 239, 0.1)) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
    }

    .fi-pagination-item-active {
        background: linear-gradient(135deg, var(--gaming-primary), var(--gaming-secondary)) !important;
        box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
    }

    /* Breadcrumb Modern */
    .fi-breadcrumbs-item {
        transition: all 0.2s ease;
    }

    .fi-breadcrumbs-item:hover {
        color: var(--gaming-primary) !important;
        transform: translateX(2px);
    }

    /* Card Header with Accent */
    .fi-section-header {
        position: relative;
        padding-bottom: 1rem;
    }

    .fi-section-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, var(--gaming-primary), var(--gaming-accent), transparent);
        border-radius: 2px;
    }

    /* Spinner/Loader Modern */
    @keyframes modernSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .fi-loading-spinner {
        border: 3px solid rgba(251, 191, 36, 0.1);
        border-top-color: var(--gaming-primary);
        border-radius: 50%;
        animation: modernSpin 0.8s linear infinite;
        box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
    }

    /* Page Transitions - Désactivé pour performance */

    /* Status Indicators with Pulse */
    .status-online {
        position: relative;
        color: var(--gaming-success);
    }

    .status-online::before {
        content: '';
        position: absolute;
        left: -15px;
        top: 50%;
        transform: translateY(-50%);
        width: 8px;
        height: 8px;
        background: var(--gaming-success);
        border-radius: 50%;
        animation: statusPulse 2s ease-in-out infinite;
    }

    @keyframes statusPulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
        }
    }

    /* Progress Bar Modern */
    .fi-progress-bar {
        background: rgba(15, 10, 31, 0.8);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .fi-progress-bar-fill {
        background: linear-gradient(90deg, var(--gaming-primary), var(--gaming-accent));
        border-radius: 10px;
        animation: progressShine 2s ease-in-out infinite;
        box-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
    }

    @keyframes progressShine {
        0% { background-position: -200% center; }
        100% { background-position: 200% center; }
    }
</style>

<?php /**PATH /Users/macbookpro/Desktop/Developments/Personnals/winpawa/winpawa_backend/resources/views/filament/custom-head.blade.php ENDPATH**/ ?>