<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_configurations', function (Blueprint $table) {
            // Google OAuth Configuration
            $table->string('google_client_id')->nullable()->after('coinbase_api_version');
            $table->string('google_client_secret')->nullable();
            $table->string('google_redirect_url')->nullable();
            $table->json('google_scopes')->nullable(); // ['email', 'profile', etc.]

            // Firebase Push Notifications Configuration
            $table->text('firebase_credentials')->nullable(); // JSON credentials file content
            $table->string('firebase_project_id')->nullable();
            $table->string('firebase_server_key')->nullable(); // For legacy HTTP API
            $table->string('firebase_sender_id')->nullable();
            $table->string('firebase_api_key')->nullable();
            $table->string('firebase_database_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_configurations', function (Blueprint $table) {
            $table->dropColumn([
                'google_client_id',
                'google_client_secret',
                'google_redirect_url',
                'google_scopes',
                'firebase_credentials',
                'firebase_project_id',
                'firebase_server_key',
                'firebase_sender_id',
                'firebase_api_key',
                'firebase_database_url',
            ]);
        });
    }
};
