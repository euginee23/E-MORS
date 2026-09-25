<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Receipt') }} {{ $receipt->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f4f4f5; color: #18181b; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; font-size: 14px; }
        .sheet { max-width: 420px; margin: 24px auto; background: #fff; padding: 28px; border: 1px solid #e4e4e7; border-radius: 12px; }
        .brand { text-align: center; border-bottom: 1px dashed #a1a1aa; padding-bottom: 14px; }
        .brand h1 { margin: 0; font-size: 20px; letter-spacing: .04em; }
        .brand p { margin: 2px 0 0; font-size: 12px; color: #52525b; }
        .headline { display: flex; justify-content: space-between; align-items: flex-end; margin: 16px 0 12px; }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: #71717a; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
        .amount { font-size: 24px; font-weight: 800; }
        dl { margin: 0; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px solid #f4f4f5; }
        .row dt { color: #71717a; }
        .row dd { margin: 0; text-align: right; font-weight: 600; overflow-wrap: anywhere; }
        .total { border-top: 1px dashed #a1a1aa; border-bottom: 0; margin-top: 4px; padding-top: 10px; font-size: 16px; }
        .signature { margin-top: 44px; width: 60%; border-top: 1px solid #71717a; padding-top: 4px; font-size: 11px; color: #52525b; }
        .footer { margin-top: 18px; text-align: center; font-size: 11px; color: #71717a; }
        .actions { max-width: 420px; margin: 0 auto 24px; display: flex; gap: 8px; justify-content: center; }
        .actions button { font: inherit; padding: 8px 16px; border-radius: 8px; border: 1px solid #d4d4d8; background: #fff; cursor: pointer; }
        .actions .primary { background: #f97316; border-color: #f97316; color: #fff; font-weight: 600; }
        @media print {
            @page { margin: 12mm; }
            body { background: #fff; }
            .sheet { margin: 0 auto; border: 0; border-radius: 0; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <main class="sheet">
        <div class="brand">
            <h1>E-MORS</h1>
            <p>{{ $receipt->market?->name }}</p>
            @if($receipt->market?->address)
            <p>{{ $receipt->market->address }}</p>
            @endif
        </div>

        <div class="headline">
            <div>
                <div class="label">{{ __('Official Receipt') }}</div>
                <div class="mono" style="font-weight:700">{{ $receipt->receipt_number }}</div>
            </div>
            <div style="text-align:right">
                <div class="amount">₱ {{ number_format($receipt->amount, 2) }}</div>
                <div class="label">{{ $receipt->payment_date?->format('M j, Y') }}</div>
            </div>
        </div>

        <dl>
            <div class="row"><dt>{{ __('Vendor') }}</dt><dd>{{ $receipt->vendor?->contact_name ?? '—' }}</dd></div>
            <div class="row"><dt>{{ __('Business') }}</dt><dd>{{ $receipt->vendor?->business_name ?? '—' }}</dd></div>
            <div class="row">
                <dt>{{ __('Stall') }}</dt>
                <dd>{{ $receipt->stall?->stall_number ?? '—' }}@if($receipt->stall?->section) ({{ __('Sec') }} {{ $receipt->stall->section }})@endif</dd>
            </div>
            <div class="row"><dt>{{ __('Method') }}</dt><dd>{{ ucfirst(str_replace('_', ' ', $receipt->payment_method)) }}</dd></div>
            @if($receipt->reference_number)
            <div class="row"><dt>{{ __('Reference No.') }}</dt><dd class="mono">{{ $receipt->reference_number }}</dd></div>
            @endif
            <div class="row"><dt>{{ __('Received By') }}</dt><dd>{{ $receipt->collector?->name ?? '—' }}</dd></div>
            <div class="row total"><dt>{{ __('Amount Paid') }}</dt><dd>₱ {{ number_format($receipt->amount, 2) }}</dd></div>
        </dl>

        <div class="signature">{{ __('Vendor Signature') }}</div>
        <div class="footer">{{ __('Printed') }} {{ now()->format('M j, Y g:i A') }}</div>
    </main>

    <div class="actions">
        <button type="button" class="primary" onclick="window.print()">{{ __('Print') }}</button>
        <button type="button" onclick="window.close()">{{ __('Close') }}</button>
    </div>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
