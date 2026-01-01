<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'reference',
        'type',
        'amount',
        'fee',
        'net_amount',
        'balance_before',
        'balance_after',
        'status',
        'payment_method',
        'payment_reference',
        'description',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = self::generateReference();
            }
        });
    }

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', TransactionStatus::PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TransactionStatus::COMPLETED);
    }

    public function scopeDeposits($query)
    {
        return $query->where('type', TransactionType::DEPOSIT);
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', TransactionType::WITHDRAWAL);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ==================== METHODS ====================

    public static function generateReference(): string
    {
        return 'TXN-' . strtoupper(Str::random(12));
    }

    public function markAsCompleted(): bool
    {
        $this->status = TransactionStatus::COMPLETED;
        $this->processed_at = now();
        return $this->save();
    }

    public function markAsFailed(string $reason = null): bool
    {
        $this->status = TransactionStatus::FAILED;
        if ($reason) {
            $this->metadata = array_merge($this->metadata ?? [], ['failure_reason' => $reason]);
        }
        return $this->save();
    }

    public function isPending(): bool
    {
        return $this->status === TransactionStatus::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::COMPLETED;
    }

    public function isDeposit(): bool
    {
        return $this->type === TransactionType::DEPOSIT;
    }

    public function isWithdrawal(): bool
    {
        return $this->type === TransactionType::WITHDRAWAL;
    }
}
