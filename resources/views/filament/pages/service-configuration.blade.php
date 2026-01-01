<x-filament-panels::page>
    <x-filament::tabs>
        <!-- WhatsApp Tab -->
        <x-filament::tabs.item
            :active="$activeTab === 'whatsapp'"
            wire:click="$set('activeTab', 'whatsapp')"
            icon="heroicon-m-chat-bubble-left-right"
        >
            WhatsApp Business
        </x-filament::tabs.item>

        <!-- Nexah SMS Tab -->
        <x-filament::tabs.item
            :active="$activeTab === 'nexah'"
            wire:click="$set('activeTab', 'nexah')"
            icon="heroicon-m-envelope"
        >
            Nexah SMS
        </x-filament::tabs.item>

        <!-- FreeMoPay Tab -->
        <x-filament::tabs.item
            :active="$activeTab === 'freemopay'"
            wire:click="$set('activeTab', 'freemopay')"
            icon="heroicon-m-credit-card"
        >
            FreeMoPay
        </x-filament::tabs.item>

        <!-- Coinbase Tab -->
        <x-filament::tabs.item
            :active="$activeTab === 'coinbase'"
            wire:click="$set('activeTab', 'coinbase')"
            icon="heroicon-m-currency-dollar"
        >
            Coinbase Commerce
        </x-filament::tabs.item>

        <!-- Google OAuth Tab -->
        <x-filament::tabs.item
            :active="$activeTab === 'google'"
            wire:click="$set('activeTab', 'google')"
            icon="heroicon-m-user-circle"
        >
            Google OAuth
        </x-filament::tabs.item>

        <!-- Firebase Tab -->
        <x-filament::tabs.item
            :active="$activeTab === 'firebase'"
            wire:click="$set('activeTab', 'firebase')"
            icon="heroicon-m-bell-alert"
        >
            Firebase Push
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div class="mt-6">
        <!-- WhatsApp Configuration -->
        <div x-show="$wire.activeTab === 'whatsapp'">
            <x-filament::section>
                <x-slot name="heading">
                    Configuration WhatsApp Business API
                </x-slot>
                <x-slot name="description">
                    Configurez votre connexion à l'API WhatsApp Business pour envoyer des notifications
                </x-slot>

                <form wire:submit="saveWhatsApp" class="space-y-4">
                    {{ $this->whatsappForm }}

                    <div class="flex gap-2">
                        <x-filament::button type="submit" icon="heroicon-m-check">
                            Sauvegarder
                        </x-filament::button>
                        <x-filament::button color="info" wire:click="testWhatsApp" type="button" icon="heroicon-m-wifi">
                            Tester la connexion
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        <!-- Nexah SMS Configuration -->
        <div x-show="$wire.activeTab === 'nexah'">
            <x-filament::section>
                <x-slot name="heading">
                    Configuration Nexah SMS API
                </x-slot>
                <x-slot name="description">
                    Configurez votre connexion à l'API Nexah SMS pour envoyer des SMS
                </x-slot>

                <form wire:submit="saveNexah" class="space-y-4">
                    {{ $this->nexahForm }}

                    <div class="flex gap-2">
                        <x-filament::button type="submit" icon="heroicon-m-check">
                            Sauvegarder
                        </x-filament::button>
                        <x-filament::button color="info" wire:click="testNexah" type="button" icon="heroicon-m-wifi">
                            Tester la connexion
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        <!-- FreeMoPay Configuration -->
        <div x-show="$wire.activeTab === 'freemopay'">
            <x-filament::section>
                <x-slot name="heading">
                    Configuration FreeMoPay API
                </x-slot>
                <x-slot name="description">
                    Configurez votre connexion à l'API FreeMoPay pour les paiements mobile money
                </x-slot>

                <form wire:submit="saveFreemopay" class="space-y-4">
                    {{ $this->freemopayForm }}

                    <div class="flex gap-2">
                        <x-filament::button type="submit" icon="heroicon-m-check">
                            Sauvegarder
                        </x-filament::button>
                        <x-filament::button color="info" wire:click="testFreemopay" type="button" icon="heroicon-m-wifi">
                            Tester la connexion
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        <!-- Coinbase Configuration -->
        <div x-show="$wire.activeTab === 'coinbase'">
            <x-filament::section>
                <x-slot name="heading">
                    Configuration Coinbase Commerce API
                </x-slot>
                <x-slot name="description">
                    Configurez votre connexion à l'API Coinbase Commerce pour les paiements en crypto-monnaie
                </x-slot>

                <form wire:submit="saveCoinbase" class="space-y-4">
                    {{ $this->coinbaseForm }}

                    <div class="flex gap-2">
                        <x-filament::button type="submit" icon="heroicon-m-check">
                            Sauvegarder
                        </x-filament::button>
                        <x-filament::button color="info" wire:click="testCoinbase" type="button" icon="heroicon-m-wifi">
                            Tester la connexion
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        <!-- Google OAuth Configuration -->
        <div x-show="$wire.activeTab === 'google'">
            <x-filament::section>
                <x-slot name="heading">
                    Configuration Google OAuth 2.0
                </x-slot>
                <x-slot name="description">
                    Configurez Google OAuth pour l'authentification des utilisateurs
                </x-slot>

                <form wire:submit="saveGoogleOauth" class="space-y-4">
                    {{ $this->googleOauthForm }}

                    <div class="flex gap-2">
                        <x-filament::button type="submit" icon="heroicon-m-check">
                            Sauvegarder
                        </x-filament::button>
                        <x-filament::button color="info" wire:click="testGoogleOauth" type="button" icon="heroicon-m-wifi">
                            Tester la configuration
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        <!-- Firebase Configuration -->
        <div x-show="$wire.activeTab === 'firebase'">
            <x-filament::section>
                <x-slot name="heading">
                    Configuration Firebase Push Notifications
                </x-slot>
                <x-slot name="description">
                    Configurez Firebase Cloud Messaging pour envoyer des notifications push
                </x-slot>

                <form wire:submit="saveFirebase" class="space-y-4">
                    {{ $this->firebaseForm }}

                    <div class="flex gap-2">
                        <x-filament::button type="submit" icon="heroicon-m-check">
                            Sauvegarder
                        </x-filament::button>
                        <x-filament::button color="info" wire:click="testFirebase" type="button" icon="heroicon-m-wifi">
                            Tester la configuration
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
