<!DOCTYPE html>
<html lang="fr" class="dark" style="color-scheme: dark;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>WINPAWA - Connexion Admin</title>

    <!-- Force Dark Mode Immediately -->
    <script>
        // Script exécuté AVANT tout autre chargement
        (function() {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
            document.documentElement.style.colorScheme = 'dark';
            localStorage.setItem('theme', 'dark');
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Filament Styles -->
    @filamentStyles
    @vite('resources/css/app.css')

<style>
    /* ============================================
       🎮 WINPAWA ULTRA GAMING LOGIN THEME
       ============================================ */

    /* Gaming Grid Background */
    .gaming-grid {
        width: 100%;
        height: 100%;
        background-image:
            linear-gradient(rgba(217, 70, 239, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(217, 70, 239, 0.03) 1px, transparent 1px);
        background-size: 50px 50px;
        animation: gridMove 20s linear infinite;
    }

    @keyframes gridMove {
        0% { transform: translateY(0); }
        100% { transform: translateY(50px); }
    }

    /* Animated Orbs */
    .gaming-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        animation: floatOrb 20s ease-in-out infinite;
    }

    .gaming-orb-1 {
        width: 500px;
        height: 500px;
        top: -15%;
        left: -10%;
        opacity: 0.25;
        animation-delay: 0s;
    }

    .gaming-orb-2 {
        width: 400px;
        height: 400px;
        top: 20%;
        right: -8%;
        opacity: 0.2;
        animation-delay: 2s;
    }

    .gaming-orb-3 {
        width: 450px;
        height: 450px;
        bottom: 15%;
        left: -12%;
        opacity: 0.18;
        animation-delay: 4s;
    }

    .gaming-orb-4 {
        width: 300px;
        height: 300px;
        bottom: -10%;
        right: 10%;
        opacity: 0.15;
        animation-delay: 6s;
    }

    @keyframes floatOrb {
        0%, 100% { transform: translate(0, 0) scale(1); }
        25% { transform: translate(30px, -30px) scale(1.1); }
        50% { transform: translate(-20px, 20px) scale(0.95); }
        75% { transform: translate(20px, 30px) scale(1.05); }
    }

    /* Floating Particles */
    .particles-container {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .particle {
        position: absolute;
        width: 4px;
        height: 4px;
        background: rgba(217, 70, 239, 0.6);
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(217, 70, 239, 0.8);
        animation: floatParticle 15s ease-in-out infinite;
    }

    .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
    .particle:nth-child(2) { left: 20%; animation-delay: 2s; background: rgba(139, 92, 246, 0.6); }
    .particle:nth-child(3) { left: 30%; animation-delay: 4s; background: rgba(6, 182, 212, 0.6); }
    .particle:nth-child(4) { left: 40%; animation-delay: 1s; }
    .particle:nth-child(5) { left: 60%; animation-delay: 3s; background: rgba(139, 92, 246, 0.6); }
    .particle:nth-child(6) { left: 70%; animation-delay: 5s; background: rgba(6, 182, 212, 0.6); }
    .particle:nth-child(7) { left: 80%; animation-delay: 2.5s; }
    .particle:nth-child(8) { left: 90%; animation-delay: 4.5s; background: rgba(251, 191, 36, 0.6); }

    @keyframes floatParticle {
        0% {
            transform: translateY(100vh) scale(0);
            opacity: 0;
        }
        10% {
            opacity: 1;
        }
        90% {
            opacity: 1;
        }
        100% {
            transform: translateY(-100vh) scale(1);
            opacity: 0;
        }
    }

    /* Scanline Effect */
    .scanline {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            to bottom,
            transparent 0%,
            rgba(217, 70, 239, 0.03) 50%,
            transparent 100%
        );
        animation: scan 8s linear infinite;
        pointer-events: none;
        z-index: 1;
    }

    @keyframes scan {
        0% { transform: translateY(-100%); }
        100% { transform: translateY(100%); }
    }

    /* Login Container Entrance */
    .gaming-login-container {
        animation: containerEntrance 0.8s ease-out;
    }

    @keyframes containerEntrance {
        0% {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Logo Entrance */
    .logo-entrance {
        animation: logoEntrance 1s ease-out;
    }

    @keyframes logoEntrance {
        0% {
            opacity: 0;
            transform: translateY(-50px) scale(0.8);
        }
        50% {
            transform: translateY(10px) scale(1.05);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Enhanced Gaming Logo Animations */
    .gaming-logo-container {
        position: relative;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }

    .gaming-logo-box-enhanced {
        position: relative;
        animation: logoHover 4s ease-in-out infinite;
    }

    @keyframes logoHover {
        0%, 100% {
            transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
        }
        25% {
            transform: perspective(1000px) rotateY(5deg) rotateX(-5deg);
        }
        50% {
            transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
        }
        75% {
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
        }
    }

    .gaming-logo-image {
        animation: logoBreath 3s ease-in-out infinite;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .gaming-logo-image:hover {
        transform: scale(1.15) rotate(5deg);
        filter: drop-shadow(0 0 50px rgba(251, 191, 36, 1)) drop-shadow(0 0 80px rgba(217, 70, 239, 0.8)) brightness(1.2) !important;
    }

    @keyframes logoBreath {
        0%, 100% {
            filter: drop-shadow(0 0 30px rgba(251, 191, 36, 0.8)) drop-shadow(0 0 60px rgba(217, 70, 239, 0.5)) brightness(1);
        }
        50% {
            filter: drop-shadow(0 0 50px rgba(251, 191, 36, 1)) drop-shadow(0 0 100px rgba(217, 70, 239, 0.7)) brightness(1.1);
        }
    }

    /* Rotating Ring Animation */
    @keyframes spin-slow {
        from { transform: translate(-50%, -50%) rotate(0deg); }
        to { transform: translate(-50%, -50%) rotate(360deg); }
    }

    .animate-spin-slow {
        animation: spin-slow 8s linear infinite;
    }

    /* Pulse Ring Effect */
    .pulse-ring {
        border: 3px solid rgba(251, 191, 36, 0.5);
        border-radius: 50%;
        animation: pulseRing 2s ease-out infinite;
    }

    @keyframes pulseRing {
        0% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -50%) scale(1.3);
            opacity: 0;
        }
    }

    /* Enhanced Corner Accents */
    @keyframes pulse-glow {
        0%, 100% {
            border-color: rgba(251, 191, 36, 0.5);
            box-shadow: 0 0 5px rgba(251, 191, 36, 0.5);
        }
        50% {
            border-color: rgba(251, 191, 36, 1);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.8), 0 0 30px rgba(217, 70, 239, 0.5);
        }
    }

    .animate-pulse-glow {
        animation: pulse-glow 2s ease-in-out infinite;
    }

    /* Corner Accents */
    .corner-accent {
        position: absolute;
        width: 20px;
        height: 20px;
        border: 2px solid rgba(251, 191, 36, 0.5);
        z-index: 10;
    }

    .corner-tl {
        top: 4px;
        left: 4px;
        border-right: none;
        border-bottom: none;
    }

    .corner-tr {
        top: 4px;
        right: 4px;
        border-left: none;
        border-bottom: none;
    }

    .corner-bl {
        bottom: 4px;
        left: 4px;
        border-right: none;
        border-top: none;
    }

    .corner-br {
        bottom: 4px;
        right: 4px;
        border-left: none;
        border-top: none;
    }

    /* Enhanced Gaming Title */
    .gaming-title-enhanced {
        animation: titleFloat 4s ease-in-out infinite;
    }

    @keyframes titleFloat {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
    }

    .gaming-highlight-gold {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #fbbf24 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: gradientShiftGold 3s ease infinite;
    }

    .gaming-highlight {
        background: linear-gradient(135deg, #d946ef 0%, #8b5cf6 50%, #d946ef 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: gradientShift 3s ease infinite;
    }

    @keyframes gradientShiftGold {
        0%, 100% { background-position: 0% center; }
        50% { background-position: 100% center; }
    }

    @keyframes gradientShift {
        0%, 100% { background-position: 0% center; }
        50% { background-position: 100% center; }
    }

    /* Live Pulse */
    .live-pulse {
        animation: livePulse 2s ease-in-out infinite;
    }

    @keyframes livePulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            opacity: 1;
        }
        50% {
            box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            opacity: 0.8;
        }
    }

    /* Gaming Card */
    .gaming-card {
        position: relative;
        background: rgba(15, 10, 31, 0.85);
        border: 1px solid rgba(217, 70, 239, 0.3);
        border-radius: 24px;
        backdrop-filter: blur(30px);
        box-shadow:
            0 8px 32px rgba(0, 0, 0, 0.4),
            0 0 0 1px rgba(217, 70, 239, 0.1) inset,
            0 20px 60px rgba(217, 70, 239, 0.15);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .gaming-card:hover {
        transform: translateY(-5px);
        box-shadow:
            0 16px 48px rgba(0, 0, 0, 0.5),
            0 0 0 1px rgba(217, 70, 239, 0.2) inset,
            0 30px 90px rgba(217, 70, 239, 0.25);
        border-color: rgba(217, 70, 239, 0.5);
    }

    /* Card Glow Effect */
    .card-glow {
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(217, 70, 239, 0.15) 0%, transparent 70%);
        animation: rotateGlow 15s linear infinite;
        pointer-events: none;
    }

    @keyframes rotateGlow {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Card Entrance */
    .card-entrance {
        animation: cardEntrance 1s ease-out 0.3s both;
    }

    @keyframes cardEntrance {
        0% {
            opacity: 0;
            transform: translateY(50px) scale(0.9) rotateX(20deg);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1) rotateX(0deg);
        }
    }

    /* Footer Entrance */
    .footer-entrance {
        animation: fadeInUp 1s ease-out 0.6s both;
    }

    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ============================================
       🎨 FILAMENT OVERRIDES - GAMING STYLE
       ============================================ */

    /* Input Fields */
    .fi-input, .fi-select-input {
        background: rgba(8, 5, 16, 0.8) !important;
        border: 1px solid rgba(217, 70, 239, 0.25) !important;
        border-radius: 14px !important;
        color: #fff !important;
        font-family: 'Outfit', sans-serif !important;
        padding: 0.875rem 1rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 0 0 0 rgba(217, 70, 239, 0) inset !important;
    }

    .fi-input::placeholder {
        color: rgba(255, 255, 255, 0.3) !important;
    }

    .fi-input:hover {
        border-color: rgba(217, 70, 239, 0.4) !important;
        background: rgba(15, 10, 31, 0.9) !important;
    }

    .fi-input:focus {
        border-color: #d946ef !important;
        background: rgba(8, 5, 16, 0.95) !important;
        box-shadow:
            0 0 0 4px rgba(217, 70, 239, 0.15) !important,
            0 0 20px rgba(217, 70, 239, 0.3) !important,
            0 0 0 1px rgba(217, 70, 239, 0.5) inset !important;
        transform: translateY(-2px);
    }

    /* Labels */
    .fi-fo-field-wrp label {
        color: rgba(255, 255, 255, 0.9) !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        letter-spacing: 0.025em !important;
        margin-bottom: 0.5rem !important;
        font-family: 'Outfit', sans-serif !important;
    }

    /* Primary Button */
    .fi-btn-primary {
        background: linear-gradient(135deg, #d946ef 0%, #8b5cf6 50%, #7c3aed 100%) !important;
        border: none !important;
        border-radius: 14px !important;
        padding: 0.95rem 2rem !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        font-family: 'Orbitron', sans-serif !important;
        box-shadow:
            0 4px 20px rgba(217, 70, 239, 0.4),
            0 0 0 1px rgba(255, 255, 255, 0.1) inset !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative;
        overflow: hidden;
    }

    .fi-btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .fi-btn-primary:hover::before {
        left: 100%;
    }

    .fi-btn-primary:hover {
        transform: translateY(-3px) scale(1.02) !important;
        box-shadow:
            0 8px 30px rgba(217, 70, 239, 0.6),
            0 0 0 1px rgba(255, 255, 255, 0.2) inset,
            0 0 40px rgba(139, 92, 246, 0.4) !important;
        background: linear-gradient(135deg, #e054f5 0%, #9466f7 50%, #8b48f3 100%) !important;
    }

    .fi-btn-primary:active {
        transform: translateY(-1px) scale(0.98) !important;
    }

    /* Checkbox */
    .fi-checkbox-input {
        border: 2px solid rgba(217, 70, 239, 0.4) !important;
        border-radius: 6px !important;
        background: rgba(8, 5, 16, 0.6) !important;
        transition: all 0.3s ease !important;
    }

    .fi-checkbox-input:checked {
        background: linear-gradient(135deg, #d946ef, #8b5cf6) !important;
        border-color: #d946ef !important;
        box-shadow: 0 0 15px rgba(217, 70, 239, 0.5) !important;
    }

    /* Links */
    a {
        color: #d946ef !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        text-decoration: none !important;
    }

    a:hover {
        color: #e054f5 !important;
        text-shadow: 0 0 10px rgba(217, 70, 239, 0.5) !important;
        text-decoration: none !important;
    }

    /* Remove Filament branding from login */
    .fi-simple-layout {
        background: transparent !important;
    }

    /* Custom scrollbar for form areas */
    .fi-fo-component-ctn::-webkit-scrollbar {
        width: 6px;
    }

    .fi-fo-component-ctn::-webkit-scrollbar-track {
        background: rgba(8, 5, 16, 0.5);
        border-radius: 3px;
    }

    .fi-fo-component-ctn::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #d946ef, #8b5cf6);
        border-radius: 3px;
    }

    /* Error messages styling */
    .fi-fo-field-wrp-error-message {
        color: #fca5a5 !important;
        font-size: 0.75rem !important;
        margin-top: 0.375rem !important;
    }

    /* Success messages */
    .fi-fo-field-wrp-hint {
        color: rgba(167, 243, 208, 0.8) !important;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .gaming-orb {
            filter: blur(60px);
        }

        .gaming-logo-box {
            width: 4rem !important;
            height: 4rem !important;
        }

        .gaming-logo-text {
            font-size: 1.875rem !important;
        }

        .gaming-title {
            font-size: 2rem !important;
        }

        .gaming-card {
            border-radius: 20px;
        }
    }
</style>
</head>
<body class="antialiased">

<x-filament-panels::page.simple>
<div class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden" style="background: linear-gradient(135deg, #020105 0%, #0a0515 25%, #0f0a1f 50%, #1a0a2e 75%, #1e0836 100%);">

    <!-- Animated Background Grid -->
    <div class="fixed inset-0 pointer-events-none opacity-10">
        <div class="gaming-grid"></div>
    </div>

    <!-- Enhanced Background Effects with Animation -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <!-- Animated orbs -->
        <div class="gaming-orb gaming-orb-1" style="background: radial-gradient(circle, #d946ef 0%, transparent 70%);"></div>
        <div class="gaming-orb gaming-orb-2" style="background: radial-gradient(circle, #8b5cf6 0%, transparent 70%);"></div>
        <div class="gaming-orb gaming-orb-3" style="background: radial-gradient(circle, #06b6d4 0%, transparent 70%);"></div>
        <div class="gaming-orb gaming-orb-4" style="background: radial-gradient(circle, #fbbf24 0%, transparent 70%);"></div>

        <!-- Floating particles -->
        <div class="particles-container">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
    </div>

    <!-- Scan line effect -->
    <div class="scanline"></div>

    <div class="relative z-10 w-full max-w-md gaming-login-container">
        <!-- Enhanced Logo with Animation -->
        <div class="text-center mb-10 logo-entrance">
            <div class="flex flex-col items-center gap-6 relative">
                <!-- Glow effect behind logo -->
                <div class="absolute inset-0 rounded-3xl blur-3xl opacity-60" style="background: radial-gradient(circle, #fbbf24 0%, #f59e0b 30%, #d946ef 70%, transparent 100%);"></div>

                <!-- Logo Container with Epic Animations -->
                <div class="relative gaming-logo-container">
                    <!-- Rotating Ring Effect -->
                    <div class="absolute inset-0 rounded-full border-4 border-transparent animate-spin-slow"
                         style="border-top-color: #fbbf24; border-right-color: #f59e0b; width: 140px; height: 140px; left: 50%; top: 50%; transform: translate(-50%, -50%);"></div>

                    <!-- Logo with 3D Effect -->
                    <div class="relative z-10 gaming-logo-box-enhanced" style="width: 120px; height: 120px;">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="WINPAWA Logo"
                             class="w-full h-full object-contain drop-shadow-2xl gaming-logo-image"
                             style="filter: drop-shadow(0 0 30px rgba(251, 191, 36, 0.8)) drop-shadow(0 0 60px rgba(217, 70, 239, 0.5));">
                    </div>

                    <!-- Pulse Rings -->
                    <div class="absolute inset-0 rounded-full pulse-ring" style="width: 140px; height: 140px; left: 50%; top: 50%; transform: translate(-50%, -50%);"></div>

                    <!-- Corner Accents with Animation -->
                    <div class="corner-accent corner-tl animate-pulse-glow"></div>
                    <div class="corner-accent corner-tr animate-pulse-glow" style="animation-delay: 0.2s;"></div>
                    <div class="corner-accent corner-bl animate-pulse-glow" style="animation-delay: 0.4s;"></div>
                    <div class="corner-accent corner-br animate-pulse-glow" style="animation-delay: 0.6s;"></div>
                </div>

                <!-- Title Section -->
                <div class="text-center relative mt-4">
                    <h1 class="text-5xl font-black text-white mb-3 gaming-title-enhanced" style="font-family: 'Orbitron', sans-serif; letter-spacing: 0.1em; text-shadow: 0 0 20px rgba(251, 191, 36, 0.5), 0 0 40px rgba(217, 70, 239, 0.3);">
                        <span class="gaming-highlight-gold">WIN</span><span class="gaming-highlight">PAWA</span>
                    </h1>
                    <div class="flex items-center justify-center gap-3 mb-2">
                        <div class="h-px w-12 bg-gradient-to-r from-transparent via-yellow-500 to-yellow-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full live-pulse" style="background: #10b981;"></div>
                        <p class="text-yellow-400 text-sm font-bold tracking-widest" style="font-family: 'Orbitron', sans-serif;">ADMINISTRATION</p>
                        <div class="w-2.5 h-2.5 rounded-full live-pulse" style="background: #10b981;"></div>
                        <div class="h-px w-12 bg-gradient-to-l from-transparent via-yellow-500 to-yellow-500"></div>
                    </div>
                    <p class="text-purple-300/70 text-xs font-medium tracking-wider">GAMING PLATFORM CONTROL CENTER</p>
                </div>
            </div>
        </div>

        <!-- Enhanced Login Card with 3D Effect -->
        <div class="gaming-card card-entrance">
            <!-- Card glow -->
            <div class="card-glow"></div>

            <!-- Top accent bar -->
            <div class="h-1 w-full rounded-t-2xl" style="background: linear-gradient(90deg, #d946ef 0%, #8b5cf6 50%, #06b6d4 100%);"></div>

            <div class="p-8 relative">
                <!-- Security badge -->
                <div class="absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 rounded-full"
                     style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3);">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-emerald-400 text-xs font-bold" style="font-family: 'Orbitron', sans-serif;">SÉCURISÉ</span>
                </div>

                <h2 class="text-2xl font-black text-white text-center mb-2 mt-4" style="font-family: 'Orbitron', sans-serif; letter-spacing: 0.05em;">
                    CONNEXION ADMIN
                </h2>
                <p class="text-purple-300/80 text-center mb-8 text-sm">Accédez au tableau de bord de gestion</p>

                {{ $this->form }}

                <div class="mt-6">
                    {{ $this->loginAction }}
                </div>

                <!-- Decorative elements -->
                <div class="mt-6 pt-6 border-t border-purple-500/20">
                    <div class="flex items-center justify-center gap-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span>Système actif</span>
                        </div>
                        <span>•</span>
                        <div class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Cryptage SSL</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Footer -->
        <div class="text-center mt-8 space-y-3 footer-entrance">
            <p class="text-gray-500 text-xs font-medium">
                © {{ date('Y') }} <span class="text-purple-400 font-bold">WINPAWA</span> - Plateforme de Casino Gaming
            </p>
            <p class="text-gray-600 text-xs">
                Tous droits réservés • Cameroun
            </p>
        </div>
    </div>
</div>
</x-filament-panels::page.simple>

@filamentScripts
@vite('resources/js/app.js')

<script>
// Force Dark Mode on Login Page
(function() {
    'use strict';

    // Immédiatement forcer le dark mode
    document.documentElement.classList.add('dark');
    document.documentElement.classList.remove('light');
    document.body.classList.add('dark');
    document.body.classList.remove('light');

    // Forcer le localStorage
    localStorage.setItem('theme', 'dark');
    localStorage.setItem('color-scheme', 'dark');

    // Observer pour empêcher tout changement
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (!target.classList.contains('dark')) {
                    target.classList.add('dark');
                }
                target.classList.remove('light');
            }
        });
    });

    // Observer le HTML et le BODY
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });

    // Forcer au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
        document.body.classList.add('dark');
        document.body.classList.remove('light');
    });

    // Intercepter tout changement de thème Filament
    if (window.filament) {
        Object.defineProperty(window.filament, 'theme', {
            get: function() { return 'dark'; },
            set: function() { return 'dark'; }
        });
    }

    console.log('%c🌙 Dark Mode Forcé', 'background: #1a0a2e; color: #fbbf24; font-size: 14px; padding: 5px 10px; border-radius: 5px;');
})();
</script>

</body>
</html>
