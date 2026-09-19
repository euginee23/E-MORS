<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Receipt — E-MORS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f5; color: #18181b; -webkit-font-smoothing: antialiased; }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 16px 40px; }
        .card { background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #16a34a 0%, #10b981 100%); padding: 36px 40px 30px; text-align: center; }
        .header-title { font-size: 26px; font-weight: 800; color: #ffffff; line-height: 1.2; margin-bottom: 6px; }
        .header-sub { font-size: 14px; color: rgba(255,255,255,0.85); }
        .body { padding: 32px 40px; }
        .greeting { font-size: 16px; color: #3f3f46; margin-bottom: 12px; overflow-wrap: break-word; word-break: break-word; }
        .message { font-size: 15px; color: #52525b; line-height: 1.7; margin-bottom: 26px; }
        .amount-card { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 2px solid #86efac; border-radius: 16px; padding: 22px 26px; margin-bottom: 26px; text-align: center; }
        .amount { font-size: 40px; font-weight: 900; color: #16a34a; line-height: 1; margin-bottom: 6px; }
        .amount-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #15803d; }
        .receipt-no { margin-top: 12px; font-family: 'SFMono-Regular', Menlo, Consolas, monospace; font-size: 14px; font-weight: 700; color: #166534; overflow-wrap: break-word; word-break: break-all; }
        table.details { width: 100%; border-collapse: collapse; margin-bottom: 26px; }
        table.details td { padding: 10px 0; border-bottom: 1px solid #f4f4f5; font-size: 14px; vertical-align: top; }
        table.details td.lbl { color: #71717a; width: 42%; }
        table.details td.val { color: #18181b; font-weight: 600; text-align: right; overflow-wrap: break-word; word-break: break-word; }
        .note { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 16px; font-size: 13px; color: #92400e; line-height: 1.6; overflow-wrap: break-word; word-break: break-word; }
        .foot { border-top: 1px solid #f4f4f5; margin-top: 8px; padding: 18px 40px 28px; font-size: 12px; color: #71717a; line-height: 1.6; text-align: center; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="header-title">Payment Received</div>
                <div class="header-sub">{{ $collection->market?->name ?? 'E-MORS' }}</div>
            </div>

            <div class="body">
                <p class="greeting">Hello {{ $collection->vendor?->contact_name ?? 'Vendor' }},</p>
                <p class="message">
                    We have received your stall payment. This email serves as your official confirmation.
                    Please keep it for your records.
                </p>

                <div class="amount-card">
                    <div class="amount">₱ {{ number_format((float) $collection->amount, 2) }}</div>
                    <div class="amount-label">Amount Paid</div>
                    <div class="receipt-no">{{ $collection->receipt_number }}</div>
                </div>

                <table class="details">
                    <tr>
                        <td class="lbl">Payment Date</td>
                        <td class="val">{{ $collection->payment_date?->format('F j, Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Business Name</td>
                        <td class="val">{{ $collection->vendor?->business_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Stall</td>
                        <td class="val">
                            {{ $collection->stall?->stall_number ?? '—' }}
                            @if($collection->stall?->section)
                                (Section {{ $collection->stall->section }})
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">Payment Method</td>
                        <td class="val">{{ ucfirst(str_replace('_', ' ', (string) $collection->payment_method)) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Received By</td>
                        <td class="val">{{ $collection->collector?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Status</td>
                        <td class="val">{{ $collection->status->label() }}</td>
                    </tr>
                </table>

                @if($collection->notes)
                <div class="note"><strong>Note:</strong> {{ $collection->notes }}</div>
                @endif
            </div>

            <div class="foot">
                This is an automated receipt from E-MORS.<br>
                For questions about this payment, please visit the market office.
            </div>
        </div>
    </div>
</body>
</html>
