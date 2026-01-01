<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\Campaign\CampaignService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendCampaignJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Campaign $campaign
    ) {}

    /**
     * Execute the job.
     */
    public function handle(CampaignService $campaignService): void
    {
        try {
            Log::info('Starting campaign send', ['campaign_id' => $this->campaign->id]);

            $result = $campaignService->sendCampaign($this->campaign);

            Log::info('Campaign send completed', [
                'campaign_id' => $this->campaign->id,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Campaign send failed', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);

            $this->campaign->update(['status' => 'failed']);

            throw $e;
        }
    }
}
