<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Stall Assigned — E-MORS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f5; color: #18181b; -webkit-font-smoothing: antialiased; }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 16px 40px; }
        .card { background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #16a34a 0%, #10b981 100%); padding: 40px 40px 32px; text-align: center; }
        .logo-row { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 24px; }
        .logo-icon { width: 44px; height: 44px; background: rgba(255,255,255,0.2); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; }
        .logo-text { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; }
        .header-icon { width: 64px; height: 64px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .header-title { font-size: 26px; font-weight: 800; color: #ffffff; line-height: 1.2; margin-bottom: 6px; }
        .header-sub { font-size: 14px; color: rgba(255,255,255,0.85); }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; color: #3f3f46; margin-bottom: 12px; }
        .message { font-size: 15px; color: #52525b; line-height: 1.7; margin-bottom: 28px; }
        /* Stall highlight card */
        .stall-card { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 2px solid #86efac; border-radius: 16px; padding: 24px 28px; margin-bottom: 28px; text-align: center; }
        .stall-number { font-size: 48px; font-weight: 900; color: #16a34a; line-height: 1; margin-bottom: 6px; }
        .stall-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #15803d; margin-bottom: 16px; }
        .stall-meta { display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; }
        .stall-meta-item { text-align: center; }
        .stall-meta-item .val { font-size: 15px; font-weight: 700; color: #166534; }
        .stall-meta-item .lbl { font-size: 11px; color: #86efac; margin-top: 2px; }
        /* Details */
        .section-label { font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #a1a1aa; margin-bottom: 12px; }
        .details-card { background: #fafafa; border-radius: 14px; border: 1px solid #e4e4e7; overflow: hidden; margin-bottom: 28px; }
        .detail-row { display: flex; align-items: flex-start; padding: 12px 16px; border-bottom: 1px solid #f4f4f5; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-size: 12px; font-weight: 600; color: #a1a1aa; width: 140px; flex-shrink: 0; }
        .detail-value { font-size: 13px; color: #18181b; font-weight: 500; flex: 1; }
        .cta-button { display: block; margin: 0 auto 28px; padding: 14px 32px; background: linear-gradient(135deg, #ea580c, #f59e0b); color: white; font-size: 15px; font-weight: 700; border-radius: 12px; text-decoration: none; text-align: center; width: fit-content; }
        .divider { height: 1px; background: #f4f4f5; margin: 0 0 28px; }
        .security { background: #f9fafb; border-radius: 12px; padding: 16px 20px; font-size: 13px; color: #71717a; line-height: 1.6; border: 1px solid #e4e4e7; }
        .security strong { color: #3f3f46; }
        .footer { padding: 24px 40px; background: #fafafa; border-top: 1px solid #f4f4f5; text-align: center; }
        .footer-brand { font-size: 13px; font-weight: 700; color: #ea580c; margin-bottom: 6px; }
        .footer-text { font-size: 12px; color: #a1a1aa; line-height: 1.6; }
        @media only screen and (max-width: 480px) {
            .wrapper { margin: 0 auto; padding: 0 8px 24px; }
            .card { border-radius: 16px; }
            .header { padding: 28px 20px 24px; }
            .header-title { font-size: 22px; }
            .header-sub { font-size: 13px; }
            .logo-text { font-size: 19px; }
            .logo-icon { width: 36px; height: 36px; }
            .header-icon { width: 52px; height: 52px; }
            .body { padding: 24px 20px; }
            .greeting { font-size: 15px; }
            .message { font-size: 14px; margin-bottom: 22px; }
            .stall-card { padding: 18px 16px; margin-bottom: 22px; }
            .stall-number { font-size: 40px; }
            .stall-meta { gap: 12px; }
            .stall-meta-item .val { font-size: 14px; }
            .detail-row { flex-direction: column; gap: 2px; padding: 10px 14px; }
            .detail-label { width: auto; font-size: 11px; }
            .detail-value { font-size: 13px; }
            .details-card { margin-bottom: 22px; }
            .cta-button { font-size: 14px; padding: 12px 24px; margin-bottom: 22px; }
            .security { padding: 14px 16px; font-size: 12px; }
            .footer { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="logo-row">
                    <span class="logo-icon">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </span>
                    <span class="logo-text">E-MORS</span>
                </div>
                <div class="header-icon">
                    <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="header-title">Stall Assigned! 🎉</div>
                <div class="header-sub">You're officially a vendor at {{ $stall->market->name }}</div>
            </div>

            <div class="body">
                <p class="greeting">Congratulations, <strong>{{ $user->name }}</strong>!</p>
                <p class="message">
                    Your vendor application has been approved and a stall has been assigned to you at
                    <strong>{{ $stall->market->name }}</strong>. You can now log in to the vendor portal
                    to view your stall details, track payments, and receive market announcements.
                </p>

                <!-- Stall Highlight -->
                <div class="stall-card">
                    <div class="stall-number">{{ $stall->stall_number }}</div>
                    <div class="stall-label">Your Assigned Stall</div>
                    <div class="stall-meta">
                        <div class="stall-meta-item">
                            <div class="val">{{ $stall->section }}</div>
                            <div class="lbl">Section</div>
                        </div>
                        <div class="stall-meta-item">
                            <div class="val">{{ $stall->size }}</div>
                            <div class="lbl">Size</div>
                        </div>
                        <div class="stall-meta-item">
                            <div class="val">₱{{ number_format($stall->monthly_rate, 2) }}</div>
                            <div class="lbl">Monthly Rate</div>
                        </div>
                    </div>
                </div>

                <div class="section-label">Vendor Details</div>
                <div class="details-card">
                    <div class="detail-row">
                        <span class="detail-label">Vendor Name</span>
                        <span class="detail-value">{{ $user->name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Business Name</span>
                        <span class="detail-value">{{ $vendor->business_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Product Type</span>
                        <span class="detail-value">{{ $vendor->product_type ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Market</span>
                        <span class="detail-value">{{ $stall->market->name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Market Address</span>
                        <span class="detail-value">{{ $stall->market->address }}</span>
                    </div>
                </div>

                <a href="{{ config('app.url') }}/login" class="cta-button">
                    Log In to Vendor Portal →
                </a>

                <div class="divider"></div>

                <div class="security">
                    <strong>Important:</strong> Please review your stall assignment in the vendor portal. If you have any concerns about your stall or permit, contact the market administrator at {{ $stall->market->name }}.
                </div>
            </div>

            <div class="footer">
                <div class="footer-brand">E-MORS</div>
                <div class="footer-text">
                    Electronic Market Operations &amp; Revenue System<br />
                    This is an automated message — please do not reply to this email.
                </div>
            </div>
        </div>

        <p style="text-align:center; font-size:12px; color:#a1a1aa; margin-top:20px;">
            © {{ date('Y') }} E-MORS. All rights reserved.
        </p>
    </div>
</body>
</html>
