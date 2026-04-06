<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VendorApplicationReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class VerifyEmailCodeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(config('fortify.home'));
        }

        $cached = Cache::get("email_verify_code_{$user->id}");

        if (! $cached || $cached !== $request->code) {
            return back()->withErrors(['code' => 'The code is invalid or has expired.']);
        }

        $user->markEmailAsVerified();
        Cache::forget("email_verify_code_{$user->id}");

        if ($user->role === 'vendor' && $user->vendor) {
            Mail::to($user->email)->send(new VendorApplicationReceived($user, $user->vendor, $user->market));
        }

        return redirect()->intended(config('fortify.home'));
    }
}
