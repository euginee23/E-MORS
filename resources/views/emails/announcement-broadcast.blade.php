<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Market Announcement - E-MORS</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f4f4f5; color: #18181b; }
        .wrap { max-width: 560px; margin: 36px auto; padding: 0 14px; }
        .card { background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .head { background: linear-gradient(135deg, #ea580c, #f59e0b); color: #fff; padding: 26px; }
        .head h1 { margin: 0; font-size: 22px; }
        .head p { margin: 8px 0 0; font-size: 13px; opacity: 0.9; }
        .body { padding: 24px; }
        .category { display: inline-block; margin-bottom: 12px; background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; font-size: 12px; font-weight: 700; border-radius: 999px; padding: 4px 10px; text-transform: uppercase; letter-spacing: .4px; }
        .title { margin: 0 0 10px; font-size: 20px; line-height: 1.25; }
        .content { margin: 0; font-size: 14px; line-height: 1.7; color: #3f3f46; white-space: pre-line; }
        .foot { border-top: 1px solid #f4f4f5; margin-top: 22px; padding-top: 14px; font-size: 12px; color: #71717a; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="head">
                <h1>Market Announcement</h1>
                <p>E-MORS notification for {{ $vendor->business_name }}</p>
            </div>
            <div class="body">
                <span class="category">{{ $announcement->category->label() }}</span>
                <h2 class="title">{{ $announcement->title }}</h2>
                <p class="content">{{ $announcement->body }}</p>

                <div class="foot">
                    Sent to: {{ $user->email }}<br>
                    Published: {{ optional($announcement->published_at)->format('M j, Y g:i A') ?? 'Draft' }}<br>
                    This is an automated message from E-MORS.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
