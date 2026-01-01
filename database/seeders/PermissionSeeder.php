<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions
        $permissions = [
            // Gestion des jeux
            'view_games',
            'create_games',
            'edit_games',
            'delete_games',
            'manage_game_settings',

            // Gestion des utilisateurs
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_user_balances',

            // Gestion des transactions
            'view_transactions',
            'approve_deposits',
            'approve_withdrawals',
            'reject_transactions',

            // Gestion des bannières
            'view_banners',
            'create_banners',
            'edit_banners',
            'delete_banners',

            // Gestion des affiliations
            'view_affiliates',
            'manage_affiliate_commissions',

            // Configuration
            'view_settings',
            'edit_settings',

            // Rôles et permissions
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',

            // Maintenance
            'manage_maintenance',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Créer le rôle Super Admin (toutes les permissions)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Créer le rôle Game Manager (gestion des jeux uniquement)
        $gameManager = Role::firstOrCreate(['name' => 'game_manager']);
        $gameManager->syncPermissions([
            'view_games',
            'create_games',
            'edit_games',
            'delete_games',
            'manage_game_settings',
            'manage_maintenance',
        ]);

        // Créer le rôle Partner (gestion limitée)
        $partner = Role::firstOrCreate(['name' => 'partner']);
        $partner->syncPermissions([
            'view_games',
            'edit_games',
            'view_users',
            'view_transactions',
            'view_banners',
        ]);

        // Créer le rôle Finance Manager
        $financeManager = Role::firstOrCreate(['name' => 'finance_manager']);
        $financeManager->syncPermissions([
            'view_transactions',
            'approve_deposits',
            'approve_withdrawals',
            'reject_transactions',
            'view_users',
            'manage_user_balances',
            'view_affiliates',
            'manage_affiliate_commissions',
        ]);

        // Créer le rôle Marketing Manager
        $marketingManager = Role::firstOrCreate(['name' => 'marketing_manager']);
        $marketingManager->syncPermissions([
            'view_banners',
            'create_banners',
            'edit_banners',
            'delete_banners',
            'view_users',
            'view_affiliates',
        ]);

        // Créer le rôle Support
        $support = Role::firstOrCreate(['name' => 'support']);
        $support->syncPermissions([
            'view_users',
            'view_transactions',
            'view_games',
        ]);

        $this->command->info('Permissions et rôles créés avec succès!');
    }
}
