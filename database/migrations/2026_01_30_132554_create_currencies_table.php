<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // XAF, USD, EUR, etc.
            $table->string('name'); // Franc CFA, Dollar US, Euro, etc.
            $table->string('symbol'); // FCFA, $, €, etc.
            $table->decimal('rate_to_xaf', 15, 6)->default(1); // Taux de conversion vers XAF
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });

        // Insérer les devises par défaut
        DB::table('currencies')->insert([
            [
                'code' => 'XAF',
                'name' => 'Franc CFA',
                'symbol' => 'FCFA',
                'rate_to_xaf' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'USD',
                'name' => 'Dollar US',
                'symbol' => '$',
                'rate_to_xaf' => 600, // 1 USD = 600 XAF (approximatif)
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'rate_to_xaf' => 655.957, // 1 EUR = 655.957 XAF (taux fixe)
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'GBP',
                'name' => 'Livre Sterling',
                'symbol' => '£',
                'rate_to_xaf' => 750, // 1 GBP = 750 XAF (approximatif)
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CAD',
                'name' => 'Dollar Canadien',
                'symbol' => 'C$',
                'rate_to_xaf' => 440, // 1 CAD = 440 XAF (approximatif)
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'NGN',
                'name' => 'Naira Nigérian',
                'symbol' => '₦',
                'rate_to_xaf' => 0.4, // 1 NGN = 0.4 XAF (approximatif)
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'GHS',
                'name' => 'Cedi Ghanéen',
                'symbol' => 'GH₵',
                'rate_to_xaf' => 40, // 1 GHS = 40 XAF (approximatif)
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ZAR',
                'name' => 'Rand Sud-Africain',
                'symbol' => 'R',
                'rate_to_xaf' => 32, // 1 ZAR = 32 XAF (approximatif)
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
