<?php

namespace App\Services\Marketing;

use App\Models\EmailCampaign;
use App\Models\EmailRecipient;
use App\Models\EmailCampaignAnalytics;
use App\Models\Customer;

class EmailCampaignService
{
    public function createCampaign(array $data): EmailCampaign
    {
        $data['created_by'] = auth()->id();
        $campaign = EmailCampaign::create($data);
        
        $this->addRecipients($campaign, $data);
        
        return $campaign;
    }

    public function addRecipients(EmailCampaign $campaign, array $data): void
    {
        $recipients = [];

        if ($data['recipient_type'] === 'all') {
            $customers = Customer::all();
            foreach ($customers as $customer) {
                $recipients[] = [
                    'campaign_id' => $campaign->id,
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                    'status' => 'pending',
                ];
            }
        }

        if (!empty($recipients)) {
            EmailRecipient::insert($recipients);
            $campaign->update(['total_recipients' => count($recipients)]);
        }
    }

    public function sendCampaign(EmailCampaign $campaign): bool
    {
        $campaign->update(['status' => 'sending']);
        \App\Jobs\SendEmailCampaign::dispatch($campaign);
        return true;
    }

    public function updateAnalytics(EmailCampaign $campaign): void
    {
        $totalSent = $campaign->recipients()->where('status', '!=', 'pending')->count();
        $totalOpened = $campaign->recipients()->where('status', 'opened')->count();
        $totalClicked = $campaign->recipients()->where('status', 'clicked')->count();

        $openRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 2) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 2) : 0;

        EmailCampaignAnalytics::updateOrCreate(
            ['campaign_id' => $campaign->id],
            [
                'total_sent' => $totalSent,
                'total_opened' => $totalOpened,
                'total_clicked' => $totalClicked,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
            ]
        );
    }
}
