<?php

namespace App\Actions\Notices;

use App\Enums\PaymentStatus;
use App\Mail\VendorComplianceNotice;
use App\Models\Collection;
use App\Models\Vendor;
use App\Models\VendorNotice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Mail;

class GenerateVendorNotices
{
    public function execute(?int $marketId = null, ?int $vendorId = null, bool $sendEmails = true, int $pendingDays = 7): array
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

        $paymentNoticeIds = $this->upsertPaymentNotices($today, $marketId, $vendorId, $pendingDays, $stats);
        $permitNoticeIds = $this->upsertPermitNotices($today, $marketId, $vendorId, $stats);

        $this->resolveClosedIssues($today, $marketId, $vendorId, $pendingDays, $stats);

        if (! $sendEmails) {
            $stats['eligible_notices'] = count($paymentNoticeIds) + count($permitNoticeIds);
            return $stats;
        }

        $this->sendDailyNotices($today, $marketId, $vendorId, $stats);

        return $stats;
    }

    private function upsertPaymentNotices(Carbon $today, ?int $marketId, ?int $vendorId, int $pendingDays, array &$stats): array
    {
        $cutoffDate = $today->copy()->subDays($pendingDays);

        $collections = Collection::query()
            ->with(['vendor.user'])
            ->when($marketId, fn ($q) => $q->where('market_id', $marketId))
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->where(function ($q) use ($cutoffDate) {
                $q->where('status', PaymentStatus::Overdue)
                    ->orWhere(function ($q2) use ($cutoffDate) {
                        $q2->where('status', PaymentStatus::Pending)
                            ->whereDate('payment_date', '<=', $cutoffDate->toDateString());
                    });
            })
            ->get();

        $ids = [];

        foreach ($collections as $collection) {
            if (! $collection->vendor) {
                continue;
            }

            $issueKey = 'payment:' . $collection->id;

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

    private function upsertPermitNotices(Carbon $today, ?int $marketId, ?int $vendorId, array &$stats): array
    {
        $vendors = Vendor::query()
            ->with('user')
            ->when($marketId, fn ($q) => $q->where('market_id', $marketId))
            ->when($vendorId, fn ($q) => $q->where('id', $vendorId))
            ->whereNotNull('permit_expiry')
            ->whereDate('permit_expiry', '<', $today->toDateString())
            ->get();

        $ids = [];

        foreach ($vendors as $vendor) {
            $expiryKey = optional($vendor->permit_expiry)->format('Ymd') ?? 'na';
            $issueKey = 'permit:' . $vendor->id . ':' . $expiryKey;

            $notice = VendorNotice::firstOrNew(['issue_key' => $issueKey]);
            $wasExisting = $notice->exists;
            $wasResolved = $notice->resolved_at !== null;

            $notice->fill([
                'market_id' => $vendor->market_id,
                'vendor_id' => $vendor->id,
                'collection_id' => null,
                'notice_type' => 'permit_expired',
                'issue_date' => $vendor->permit_expiry,
                'details' => [
                    'permit_status' => $vendor->permit_status->value,
                    'permit_number' => $vendor->permit_number,
                    'expired_days' => $vendor->permit_expiry ? Carbon::parse($vendor->permit_expiry)->diffInDays($today) : null,
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

    private function resolveClosedIssues(Carbon $today, ?int $marketId, ?int $vendorId, int $pendingDays, array &$stats): void
    {
        $openNotices = VendorNotice::query()
            ->with(['vendor', 'collection'])
            ->whereNull('resolved_at')
            ->when($marketId, fn ($q) => $q->where('market_id', $marketId))
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->get();

        $cutoffDate = $today->copy()->subDays($pendingDays);

        foreach ($openNotices as $notice) {
            $shouldResolve = false;

            if ($notice->notice_type === 'payment_overdue') {
                $collection = $notice->collection;
                if (! $collection) {
                    $shouldResolve = true;
                } else {
                    $isOverdue = $collection->status === PaymentStatus::Overdue;
                    $isOldPending = $collection->status === PaymentStatus::Pending
                        && $collection->payment_date
                        && Carbon::parse($collection->payment_date)->lte($cutoffDate);

                    $shouldResolve = ! $isOverdue && ! $isOldPending;
                }
            }

            if ($notice->notice_type === 'permit_expired') {
                $vendor = $notice->vendor;
                $permitExpired = $vendor
                    && $vendor->permit_expiry
                    && Carbon::parse($vendor->permit_expiry)->lt($today);

                $shouldResolve = ! $permitExpired;
            }

            if ($shouldResolve) {
                $notice->update(['resolved_at' => now()]);
                $stats['resolved']++;
            }
        }
    }

    private function sendDailyNotices(Carbon $today, ?int $marketId, ?int $vendorId, array &$stats): void
    {
        $noticesByVendor = VendorNotice::query()
            ->with(['vendor.user', 'collection'])
            ->whereNull('resolved_at')
            ->when($marketId, fn ($q) => $q->where('market_id', $marketId))
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->where(function ($q) use ($today) {
                $q->whereNull('last_sent_at')
                    ->orWhereDate('last_sent_at', '<', $today->toDateString());
            })
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

            Mail::to($user->email)->queue(new VendorComplianceNotice($user, $vendor, $vendorNotices));
            $stats['emails_sent'] += $vendorNotices->count();
            $stats['vendors_notified']++;

            VendorNotice::whereIn('id', $vendorNotices->pluck('id')->all())->get()->each(function (VendorNotice $notice) {
                $notice->update([
                    'last_sent_at' => now(),
                    'sent_count' => $notice->sent_count + 1,
                ]);
            });
        }
    }
}
