<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Application Received — E-MORS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f5; color: #18181b; -webkit-font-smoothing: antialiased; }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 16px 40px; }
        .card { background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #ea580c 0%, #f59e0b 100%); padding: 40px 40px 32px; text-align: center; }
        .logo-row { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 24px; }
        .logo-icon { width: 44px; height: 44px; background: rgba(255,255,255,0.2); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; }
        .logo-text { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; }
        .header-icon { width: 64px; height: 64px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .header-title { font-size: 24px; font-weight: 800; color: #ffffff; line-height: 1.2; margin-bottom: 6px; }
        .header-sub { font-size: 14px; color: rgba(255,255,255,0.85); }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; color: #3f3f46; margin-bottom: 12px; }
        .message { font-size: 15px; color: #52525b; line-height: 1.7; margin-bottom: 28px; }
        .section-label { font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #a1a1aa; margin-bottom: 12px; }
        .details-card { background: #fafafa; border-radius: 14px; border: 1px solid #e4e4e7; overflow: hidden; margin-bottom: 28px; }
        .detail-row { display: flex; align-items: flex-start; padding: 12px 16px; border-bottom: 1px solid #f4f4f5; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-size: 12px; font-weight: 600; color: #a1a1aa; width: 130px; flex-shrink: 0; }
        .detail-value { font-size: 13px; color: #18181b; font-weight: 500; flex: 1; }
        .status-badge { display: inline-block; padding: 4px 12px; background: #fef9c3; color: #854d0e; font-size: 12px; font-weight: 700; border-radius: 999px; border: 1px solid #fde68a; }
        .steps { background: #fff7ed; border-radius: 14px; border: 1px solid #fed7aa; padding: 20px 24px; margin-bottom: 28px; }
        .step { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
        .step:last-child { margin-bottom: 0; }
        .step-num { width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(135deg, #ea580c, #f59e0b); color: white; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
        .step-text { font-size: 13px; color: #7c2d12; line-height: 1.5; }
        .step-text strong { color: #9a3412; }
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
            .detail-row { flex-direction: column; gap: 2px; padding: 10px 14px; }
            .detail-label { width: auto; font-size: 11px; }
            .detail-value { font-size: 13px; }
            .details-card { margin-bottom: 22px; }
            .steps { padding: 16px 18px; margin-bottom: 22px; }
            .step { gap: 10px; margin-bottom: 10px; }
            .step-num { width: 22px; height: 22px; font-size: 10px; }
            .step-text { font-size: 12px; }
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
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="header-title">Application Received!</div>
                <div class="header-sub">{{ $market->name }}</div>
            </div>

            <div class="body">
                <p class="greeting">Hi, <strong>{{ $user->name }}</strong>!</p>
                <p class="message">
                    Thank you for applying as a vendor at <strong>{{ $market->name }}</strong>.
                    We have received your application and it is currently under review.
                    The market administrator will assign you a stall and notify you by email.
                </p>

                <div class="section-label">Application Details</div>
                <div class="details-card">
                    <div class="detail-row">
                        <span class="detail-label">Applicant</span>
                        <span class="detail-value">{{ $user->name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">{{ $user->email }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value">{{ $vendor->contact_phone ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Home Address</span>
                        <span class="detail-value">{{ $vendor->address ?? '—' }}</span>
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
                        <span class="detail-value">{{ $market->name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value"><span class="status-badge">Pending Review</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date Applied</span>
                        <span class="detail-value">{{ now()->format('F j, Y') }}</span>
                    </div>
                </div>

                <div class="section-label">What Happens Next</div>
                <div class="steps">
                    <div class="step">
                        <span class="step-num">1</span>
                        <span class="step-text"><strong>Verify your email</strong> — Check your inbox for a 6-digit verification code and enter it to confirm your account.</span>
                    </div>
                    <div class="step">
                        <span class="step-num">2</span>
                        <span class="step-text"><strong>Wait for review</strong> — The market administrator will review your application and check available stalls.</span>
                    </div>
                    <div class="step">
                        <span class="step-num">3</span>
                        <span class="step-text"><strong>Receive stall assignment</strong> — Once approved, you'll get another email with your assigned stall details and you can start using the vendor portal.</span>
                    </div>
                </div>

                <div class="security">
                    <strong>Questions?</strong> Contact the market office at <strong>{{ $market->name }}</strong>, {{ $market->address }}.
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
