<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vendor Compliance Notice - E-MORS</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f4f4f5; color: #18181b; }
        .wrap { max-width: 620px; margin: 36px auto; padding: 0 14px; }
        .card { background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .head { background: linear-gradient(135deg, #b91c1c, #f97316); color: #fff; padding: 26px; }
        .head h1 { margin: 0; font-size: 22px; }
        .head p { margin: 8px 0 0; font-size: 13px; opacity: 0.9; }
        .body { padding: 24px; }
        .intro { margin: 0 0 16px; font-size: 14px; line-height: 1.7; color: #3f3f46; }
        .item { border: 1px solid #e4e4e7; border-radius: 12px; padding: 14px; margin-bottom: 10px; background: #fafafa; }
        .item h3 { margin: 0 0 8px; font-size: 14px; color: #991b1b; }
        .item p { margin: 0; font-size: 13px; color: #52525b; line-height: 1.6; }
        .foot { border-top: 1px solid #f4f4f5; margin-top: 20px; padding-top: 14px; font-size: 12px; color: #71717a; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="head">
                <h1>Action Required</h1>
                <p>Compliance reminder for {{ $vendor->business_name }}</p>
            </div>
            <div class="body">
                <p class="intro">
                    Hello {{ $user->name }}, we detected one or more unresolved account issues that need your attention.
                    Please settle these items as soon as possible to avoid service interruptions.
                </p>

                @foreach($notices as $notice)
                    <div class="item">
                        @if($notice->notice_type === 'payment_overdue')
                            <h3>Overdue/Pending Payment</h3>
                            <p>
                                Amount: ₱{{ number_format((float) ($notice->details['amount'] ?? 0), 2) }}<br>
                                Receipt: {{ $notice->details['receipt_number'] ?? 'N/A' }}<br>
                                Status: {{ ucfirst((string) ($notice->details['payment_status'] ?? 'pending')) }}<br>
                                Due Date: {{ optional($notice->issue_date)->format('M j, Y') ?? 'N/A' }}
                            </p>
                        @elseif($notice->notice_type === 'permit_expired')
                            <h3>Expired Permit</h3>
                            <p>
                                Permit Number: {{ $notice->details['permit_number'] ?? 'N/A' }}<br>
                                Status: {{ ucfirst((string) ($notice->details['permit_status'] ?? 'expired')) }}<br>
                                Expiry Date: {{ optional($notice->issue_date)->format('M j, Y') ?? 'N/A' }}
                            </p>
                        @endif
                    </div>
                @endforeach

                <div class="foot">
                    Vendor: {{ $vendor->contact_name }}<br>
                    Email: {{ $user->email }}<br>
                    This is an automated daily notice from E-MORS.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
