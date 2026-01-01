<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('channel', ['whatsapp', 'sms', 'email']); // Canal d'envoi
            $table->text('subject')->nullable(); // Pour email
            $table->longText('message');
            $table->string('whatsapp_template')->nullable(); // Template WhatsApp
            $table->json('attachments')->nullable(); // Pièces jointes (images, PDF)

            // Destinataires
            $table->enum('recipient_type', ['all', 'specific'])->default('all');
            $table->json('specific_users')->nullable(); // IDs des utilisateurs spécifiques

            // Programmation
            $table->enum('schedule_type', ['immediate', 'scheduled', 'recurring'])->default('immediate');
            $table->timestamp('scheduled_at')->nullable(); // Date/heure unique
            $table->string('recurrence_pattern')->nullable(); // daily, weekly, monthly, custom
            $table->json('recurrence_config')->nullable(); // {day: 'thursday', time: '10:00'}
            $table->timestamp('recurrence_start')->nullable();
            $table->timestamp('recurrence_end')->nullable();

            // Statut
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled', 'failed'])->default('draft');
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->timestamp('sent_at')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('channel');
            $table->index('status');
            $table->index('schedule_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
