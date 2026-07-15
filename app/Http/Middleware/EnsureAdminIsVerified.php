<?php

namespace App\Http\Middleware;

use App\Enums\AdminStatus;
use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminIsVerified
{
    /**
     * Guard already-active sessions against verification changes made after login.
     *
     * A pending admin is sent to the waiting screen (still logged in, can sign out).
     * A rejected or deactivated admin is logged out entirely with an explanatory message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === UserRole::Admin) {
            if ($user->status === AdminStatus::Rejected) {
                return $this->logoutWithMessage($request, 'Your registration has been rejected. Please contact the Super Admin.');
            }

            if (! $user->is_active) {
                return $this->logoutWithMessage($request, 'Your account has been deactivated. Please contact the Super Admin.');
            }

            if ($user->status === AdminStatus::Pending && ! $request->routeIs('admin.pending') && ! $request->routeIs('logout')) {
                return redirect()->route('admin.pending');
            }
        }

        return $next($request);
    }

    private function logoutWithMessage(Request $request, string $message): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', $message);
    }
}
