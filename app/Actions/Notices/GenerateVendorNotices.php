<?php

namespace App\Actions\Notices;

use App\Enums\PaymentStatus;
use App\Mail\VendorComplianceNotice;
use App\Models\Collection;
use App\Models\Stall;
use App\Models\VendorNotice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateVendorNotices
{
    public function execute(?int $marketId = null, ?int $vendorId = null, bool $sendEmails = true): array
    {
        $today = Carbon::now()->startOfDay();

        $stats = [
            'created' => 0,
            'reactivated' => 0,
            'resolved' => 0,
            'emails_sent' => 0,
            'emails_skipped' => 0,
            'vendors_notified' => 0,
        ];

        $paymentNoticeIds = $this->upsertPaymentNotices($today, $marketId, $vendorId, $stats);
        $stallNoticeIds = $this->upsertStallRentNotices($today, $marketId, $vendorId, $stats);

        if (! $sendEmails) {
            $stats['eligible_notices'] = count($paymentNoticeIds) + count($stallNoticeIds);

            return $stats;
        }

        $this->sendNotices($marketId, $vendorId, $stats);

        return $stats;
    }

    private function upsertPaymentNotices(Carbon $today, ?int $marketId, ?int $vendorId, array &$stats): array
    {
        $collections = Collection::query()
            ->with(['vendor.user'])
            ->when($marketId, fn ($q) => $q->where('market_id', $marketId))
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Overdue])
            ->get();

        $ids = [];

        foreach ($collections as $collection) {
            if (! $collection->vendor) {
                continue;
            }

            $issueKey = 'payment:'.$collection->id;

            $notice = VendorNotice::firstOrNew(['issue_key' => $issueKey]);
            $wasExisting = $notice->exists;
            $wasResolved = $notice->resolved_at !== null;

            $notice->fill([
                'market_id' => $collection->market_id,
                'vendor_id' => $collection->vendor_id,
                'collection_id' => $collection->id,
                'notice_type' => 'payment_overdue',
                'issue_date' => $collection->payment_date,
                'details' => [
                    'payment_status' => $collection->status->value,
                    'amount' => (float) $collection->amount,
                    'receipt_number' => $collection->receipt_number,
                    'pending_days' => $collection->payment_date ? Carbon::parse($collection->payment_date)->diffInDays($today) : null,
                ],
                'resolved_at' => null,
            ]);
            $notice->save();

            if (! $wasExisting) {
                $stats['created']++;
            } elseif ($wasResolved) {
                $stats['reactivated']++;
            }

            $ids[] = $notice->id;
        }

        return $ids;
    }

    /**
     * Notices for stall rentals whose term has lapsed. Business permits are handled by a
     * separate office, so the market only chases the stall lease it actually owns.
     */
    private function upsertStallRentNotices(Carbon $today, ?int $marketId, ?int $vendorId, array &$stats): array
    {
        $stalls = Stall::query()
            ->with('vendor')
            ->whereNotNull('vendor_id')
            ->when($marketId, fn ($q) => $q->where('market_id', $marketId))
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->whereNotNull('rent_expiry')
            ->whereDate('rent_expiry', '<', $today->toDateString())
            ->get();

        $ids = [];

        foreach ($stalls as $stall) {
            if (! $stall->vendor) {
                continue;
            }

            $expiryKey = $stall->rent_expiry->format('Ymd');
            $issueKey = 'stall:'.$stall->id.':'.$expiryKey;

            $notice = VendorNotice::firstOrNew(['issue_key' => $issueKey]);
            $wasExisting = $notice->exists;
            $wasResolved = $notice->resolved_at !== null;

            $notice->fill([
                'market_id' => $stall->market_id,
                'vendor_id' => $stall->vendor_id,
                'collection_id' => null,
                'notice_type' => 'stall_expired',
                'issue_date' => $stall->rent_expiry,
                'details' => [
                    'stall_number' => $stall->stall_number,
                    'section' => $stall->section,
                    'rent_expiry' => $stall->rent_expiry->toDateString(),
                    'expired_days' => (int) $stall->rent_expiry->diffInDays($today),
                    'monthly_rate' => (float) $stall->monthly_rate,
                ],
                'resolved_at' => null,
            ]);
            $notice->save();

            if (! $wasExisting) {
                $stats['created']++;
            } elseif ($wasResolved) {
                $stats['reactivated']++;
            }

            $ids[] = $notice->id;
        }

        return $ids;
    }

    private function sendNotices(?int $marketId, ?int $vendorId, array &$stats): void
    {
        $noticesByVendor = VendorNotice::query()
            ->with(['vendor.user', 'collection'])
            ->whereNull('resolved_at')
            ->when($marketId, fn ($q) => $q->where('market_id', $marketId))
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->orderBy('vendor_id')
            ->get()
            ->groupBy('vendor_id');

        foreach ($noticesByVendor as $vendorNotices) {
            $vendor = $vendorNotices->first()?->vendor;
            $user = $vendor?->user;

            if (! $vendor || ! $user || ! $user->email) {
                $stats['emails_skipped'] += $vendorNotices->count();

                continue;
            }

            try {
                // Send synchronously so notice delivery does not depend on queue workers in production.
                Mail::to($user->email)->send(new VendorComplianceNotice($user, $vendor, $vendorNotices));
                $stats['emails_sent'] += $vendorNotices->count();
                $stats['vendors_notified']++;
            } catch (\Throwable $e) {
                $stats['emails_skipped'] += $vendorNotices->count();

                Log::error('Failed to send vendor compliance notice email.', [
                    'vendor_id' => $vendor->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            VendorNotice::whereIn('id', $vendorNotices->pluck('id')->all())->get()->each(function (VendorNotice $notice) {
                $notice->update([
                    'last_sent_at' => now(),
                    'sent_count' => $notice->sent_count + 1,
                ]);
            });
        }
    }
}
