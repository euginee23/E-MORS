<?php

namespace App\Http\Middleware;

use App\Enums\PermitStatus;
use App\Enums\UserRole;
use App\Models\Stall;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorIsApproved
{
    /**
     * Redirect pending/unassigned vendors to the waiting screen.
     *
     * A vendor is considered "approved and ready" when their permit status is
     * active AND they have been assigned a stall. Any other state sends them
     * to the vendor.pending waiting page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === UserRole::Vendor) {
            $vendor = $user->vendor;

            if (! $vendor) {
                return redirect()->route('vendor.pending');
            }

            $hasAssignedStall = Stall::query()
                ->where('vendor_id', $vendor->id)
                ->exists();

            // Keep vendor status aligned once a stall is assigned.
            if ($hasAssignedStall && $vendor->permit_status !== PermitStatus::Active) {
                $vendor->update(['permit_status' => PermitStatus::Active]);
                $vendor->refresh();
            }

            $isApproved = $vendor
                && $hasAssignedStall
                && $vendor->permit_status === PermitStatus::Active;

            if (! $isApproved) {
                return redirect()->route('vendor.pending');
            }
        }

        return $next($request);
    }
}
