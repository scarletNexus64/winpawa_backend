<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'name', 'channel', 'subject', 'message', 'whatsapp_template', 'attachments',
        'recipient_type', 'specific_users', 'schedule_type', 'scheduled_at',
        'recurrence_pattern', 'recurrence_config', 'recurrence_start', 'recurrence_end',
        'status', 'total_recipients', 'sent_count', 'failed_count', 'sent_at', 'created_by'
    ];

    protected $casts = [
        'attachments' => 'array',
        'specific_users' => 'array',
        'recurrence_config' => 'array',
        'scheduled_at' => 'datetime',
        'recurrence_start' => 'datetime',
        'recurrence_end' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function getChannelLabelAttribute(): string
    {
        return match($this->channel) {
            'whatsapp' => 'WhatsApp',
            'sms' => 'SMS',
            'email' => 'Email',
            default => $this->channel,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Brouillon',
            'scheduled' => 'Programmée',
            'sending' => 'Envoi en cours',
            'sent' => 'Envoyée',
            'paused' => 'En pause',
            'cancelled' => 'Annulée',
            'failed' => 'Échouée',
            default => $this->status,
        };
    }
}
