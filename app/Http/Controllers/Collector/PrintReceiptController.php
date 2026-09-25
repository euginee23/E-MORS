<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A bare page holding only the receipt, so the browser's print dialog never
 * picks up the app shell around it.
 */
class PrintReceiptController extends Controller
{
    public function __invoke(Request $request, Collection $collection): View
    {
        abort_unless($collection->market_id === $request->user()->market_id, 404);

        $collection->load(['vendor', 'stall', 'collector', 'market']);

        return view('collector.receipt-print', ['receipt' => $collection]);
    }
}
