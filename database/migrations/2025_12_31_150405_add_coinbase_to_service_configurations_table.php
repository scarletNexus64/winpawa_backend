<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_configurations', function (Blueprint $table) {
            // Coinbase Commerce API
            $table->string('coinbase_api_key')->nullable()->after('freemopay_retry_delay');
            $table->string('coinbase_webhook_secret')->nullable();
            $table->string('coinbase_api_version')->default('2018-03-22');
        });
    }

    public function down(): void
    {
        Schema::table('service_configurations', function (Blueprint $table) {
            $table->dropColumn(['coinbase_api_key', 'coinbase_webhook_secret', 'coinbase_api_version']);
        });
    }
};
