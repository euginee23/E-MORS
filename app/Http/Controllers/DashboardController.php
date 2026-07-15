<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()->isSuperAdmin()) {
            return redirect()->route('super-admin.admins.index');
        }

        return view('dashboard');
    }
}
