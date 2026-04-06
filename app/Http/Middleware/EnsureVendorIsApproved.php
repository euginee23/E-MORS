<?php

namespace App\Http\Middleware;

use App\Enums\PermitStatus;
use App\Enums\UserRole;
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

            $isApproved = $vendor
                && $vendor->permit_status === PermitStatus::Active
                && $vendor->stall !== null;

            if (! $isApproved) {
                return redirect()->route('vendor.pending');
            }
        }

        return $next($request);
    }
}
