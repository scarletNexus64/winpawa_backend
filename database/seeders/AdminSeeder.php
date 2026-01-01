<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\AffiliateStats;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les rôles
        $adminRole = Role::create(['name' => 'admin']);
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $userRole = Role::create(['name' => 'user']);

        // Créer les permissions
        $permissions = [
            // Users
            'view_users', 'create_users', 'edit_users', 'delete_users',
            // Games
            'view_games', 'create_games', 'edit_games', 'delete_games',
            // Transactions
            'view_transactions', 'approve_withdrawals', 'process_deposits',
            // Bets
            'view_bets', 'cancel_bets',
            // Settings
            'view_settings', 'edit_settings',
            // Reports
            'view_reports', 'export_reports',
            // Virtual Match
            'manage_virtual_matches',
            // Affiliate
            'view_affiliates', 'manage_affiliates',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Attribuer toutes les permissions au super admin
        $superAdminRole->givePermissionTo(Permission::all());

        // Attribuer les permissions de base à l'admin
        $adminRole->givePermissionTo([
            'view_users', 'edit_users',
            'view_games', 'edit_games',
            'view_transactions', 'approve_withdrawals',
            'view_bets',
            'view_reports',
            'view_affiliates',
        ]);

        // Créer le super admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@winpawa.com',
            'phone' => '+237600000000',
            'password' => Hash::make('WinPawa@2024!'),
            'referral_code' => 'ADMIN001',
            'is_active' => true,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $superAdmin->assignRole('super_admin');

        // Créer le wallet pour l'admin
        Wallet::create([
            'user_id' => $superAdmin->id,
            'main_balance' => 0,
            'bonus_balance' => 0,
            'affiliate_balance' => 0,
        ]);

        // Créer les stats d'affiliation
        AffiliateStats::create([
            'user_id' => $superAdmin->id,
        ]);

        // Créer un utilisateur de test
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'user@winpawa.com',
            'phone' => '+237699999999',
            'password' => Hash::make('Test@2024!'),
            'referral_code' => User::generateReferralCode(),
            'is_active' => true,
            'is_verified' => true,
            'email_verified_at' => now(),
            'date_of_birth' => '1995-01-15',
        ]);

        $testUser->assignRole('user');

        Wallet::create([
            'user_id' => $testUser->id,
            'main_balance' => 10000, // 10,000 FCFA pour tests
            'bonus_balance' => 0,
            'affiliate_balance' => 0,
        ]);

        AffiliateStats::create([
            'user_id' => $testUser->id,
        ]);
    }
}
