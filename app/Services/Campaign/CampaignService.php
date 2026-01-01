<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\User;
use App\Services\Notifications\WhatsAppService;
use App\Services\Notifications\NexahService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CampaignService
{
    public function __construct(
        protected WhatsAppService $whatsappService,
        protected NexahService $nexahService
    ) {}

    /**
     * Prepare campaign recipients
     */
    public function prepareRecipients(Campaign $campaign): void
    {
        $users = $this->getRecipients($campaign);

        foreach ($users as $user) {
            CampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Get campaign recipients
     */
    protected function getRecipients(Campaign $campaign)
    {
        if ($campaign->recipient_type === 'all') {
            return User::all();
        }

        return User::whereIn('id', $campaign->specific_users ?? [])->get();
    }

    /**
     * Send campaign to all recipients
     */
    public function sendCampaign(Campaign $campaign): array
    {
        if ($campaign->status !== 'draft' && $campaign->status !== 'scheduled') {
            return ['success' => false, 'message' => 'La campagne ne peut pas être envoyée'];
        }

        // Update status to sending
        $campaign->update(['status' => 'sending']);

        // Prepare recipients if not already done
        if ($campaign->recipients()->count() === 0) {
            $this->prepareRecipients($campaign);
        }

        $successCount = 0;
        $failedCount = 0;

        // Get pending recipients
        $recipients = $campaign->recipients()->where('status', 'pending')->get();

        foreach ($recipients as $recipient) {
            $result = $this->sendToRecipient($campaign, $recipient);

            if ($result['success']) {
                $successCount++;
                $recipient->update(['status' => 'sent', 'sent_at' => now()]);
            } else {
                $failedCount++;
                $recipient->update([
                    'status' => 'failed',
                    'error_message' => $result['message'],
                ]);
            }
        }

        // Update campaign statistics
        $campaign->update([
            'sent_count' => $successCount,
            'failed_count' => $failedCount,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => "Campagne envoyée: {$successCount} succès, {$failedCount} échecs",
            'sent' => $successCount,
            'failed' => $failedCount,
        ];
    }

    /**
     * Send message to a single recipient
     */
    protected function sendToRecipient(Campaign $campaign, CampaignRecipient $recipient): array
    {
        try {
            $user = $recipient->user;

            if (!$user) {
                return ['success' => false, 'message' => 'Utilisateur non trouvé'];
            }

            return match ($campaign->channel) {
                'whatsapp' => $this->sendWhatsApp($campaign, $user),
                'sms' => $this->sendSms($campaign, $user),
                'email' => $this->sendEmail($campaign, $user),
                default => ['success' => false, 'message' => 'Canal non supporté'],
            };
        } catch (\Exception $e) {
            Log::error('Campaign send error', [
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send via WhatsApp
     */
    protected function sendWhatsApp(Campaign $campaign, User $user): array
    {
        if (empty($user->phone)) {
            return ['success' => false, 'message' => 'Numéro de téléphone manquant'];
        }

        // Use template if specified, otherwise send as text (requires approved template)
        if ($campaign->whatsapp_template) {
            return $this->whatsappService->sendOtp($user->phone, '000000'); // Adapt this based on your template
        }

        // For text messages without template, you'd need to use a different WhatsApp endpoint
        return ['success' => false, 'message' => 'Template WhatsApp requis'];
    }

    /**
     * Send via SMS
     */
    protected function sendSms(Campaign $campaign, User $user): array
    {
        if (empty($user->phone)) {
            return ['success' => false, 'message' => 'Numéro de téléphone manquant'];
        }

        return $this->nexahService->sendSms($user->phone, $campaign->message);
    }

    /**
     * Send via Email
     */
    protected function sendEmail(Campaign $campaign, User $user): array
    {
        if (empty($user->email)) {
            return ['success' => false, 'message' => 'Email manquant'];
        }

        try {
            Mail::send([], [], function ($message) use ($campaign, $user) {
                $message->to($user->email, $user->name)
                    ->subject($campaign->subject)
                    ->html($campaign->message);

                // Add attachments if any
                if ($campaign->attachments) {
                    foreach ($campaign->attachments as $attachment) {
                        $path = storage_path('app/public/' . $attachment);
                        if (file_exists($path)) {
                            $message->attach($path);
                        }
                    }
                }
            });

            return ['success' => true, 'message' => 'Email envoyé'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check if campaign should be sent (for recurring campaigns)
     */
    public function shouldSendRecurring(Campaign $campaign): bool
    {
        if ($campaign->schedule_type !== 'recurring') {
            return false;
        }

        if ($campaign->status !== 'scheduled') {
            return false;
        }

        // Check if within recurrence window
        $now = now();
        if ($campaign->recurrence_start && $now->lt($campaign->recurrence_start)) {
            return false;
        }

        if ($campaign->recurrence_end && $now->gt($campaign->recurrence_end)) {
            return false;
        }

        // Check recurrence pattern
        $config = $campaign->recurrence_config ?? [];

        return match ($campaign->recurrence_pattern) {
            'daily' => $this->checkDailyRecurrence($config, $now),
            'weekly' => $this->checkWeeklyRecurrence($config, $now),
            'monthly' => $this->checkMonthlyRecurrence($config, $now),
            default => false,
        };
    }

    protected function checkDailyRecurrence(array $config, $now): bool
    {
        $targetTime = $config['time'] ?? '10:00';
        return $now->format('H:i') === $targetTime;
    }

    protected function checkWeeklyRecurrence(array $config, $now): bool
    {
        $targetDay = strtolower($config['day'] ?? 'monday');
        $targetTime = $config['time'] ?? '10:00';

        return strtolower($now->format('l')) === $targetDay && $now->format('H:i') === $targetTime;
    }

    protected function checkMonthlyRecurrence(array $config, $now): bool
    {
        $targetDay = $config['day'] ?? 1;
        $targetTime = $config['time'] ?? '10:00';

        return $now->day == $targetDay && $now->format('H:i') === $targetTime;
    }
}
