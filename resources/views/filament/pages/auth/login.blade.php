<x-filament-panels::page.simple>
    <style>
        /* Force Dark Mode */
        html, body {
            background: #0a0515 !important;
            color-scheme: dark !important;
        }

        body {
            background: linear-gradient(135deg, #020105 0%, #0f0a1f 50%, #1a0a2e 100%) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .simple-login-card {
            background: rgba(15, 10, 31, 0.9);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 20px;
            padding: 2rem;
            max-width: 450px;
            width: 100%;
            backdrop-filter: blur(10px);
        }

        .simple-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .simple-logo img {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            filter: drop-shadow(0 0 20px rgba(251, 191, 36, 0.6));
        }

        .simple-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            color: #fbbf24;
            margin: 1rem 0;
        }

        .simple-subtitle {
            text-align: center;
            color: rgba(251, 191, 36, 0.7);
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }
    </style>

    <div class="simple-login-card">
        <div class="simple-logo">
            <img src="{{ asset('images/logo.png') }}" alt="WINPAWA Logo">
            <h1 class="simple-title">WINPAWA</h1>
            <p class="simple-subtitle">Administration Panel</p>
        </div>

        {{ $this->form }}

        <div class="mt-6">
            {{ $this->loginAction }}
        </div>

        <div class="text-center mt-6 text-sm text-gray-500">
            © {{ date('Y') }} WINPAWA - Tous droits réservés
        </div>
    </div>
</x-filament-panels::page.simple>
