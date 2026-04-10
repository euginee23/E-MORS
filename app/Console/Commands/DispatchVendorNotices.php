<?php

namespace App\Console\Commands;

use App\Actions\Notices\GenerateVendorNotices;
use Illuminate\Console\Command;

class DispatchVendorNotices extends Command
{
    protected $signature = 'notices:dispatch {--market_id=} {--vendor_id=} {--no-email}';

    protected $description = 'Generate vendor payment/permit notices and send reminder emails.';

    public function handle(GenerateVendorNotices $generator): int
    {
        $marketId = $this->option('market_id') ? (int) $this->option('market_id') : null;
        $vendorId = $this->option('vendor_id') ? (int) $this->option('vendor_id') : null;
        $sendEmails = ! $this->option('no-email');

        $summary = $generator->execute($marketId, $vendorId, $sendEmails);

        $this->info('Vendor notices run complete.');
        $this->line('Created: ' . $summary['created']);
        $this->line('Reactivated: ' . $summary['reactivated']);
        $this->line('Resolved: ' . $summary['resolved']);
        $this->line('Vendors notified: ' . $summary['vendors_notified']);
        $this->line('Notices emailed: ' . $summary['emails_sent']);
        $this->line('Email skips: ' . $summary['emails_skipped']);

        return self::SUCCESS;
    }
}
