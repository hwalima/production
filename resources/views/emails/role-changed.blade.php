<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Access Level Has Changed</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6fb; color: #1e293b; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }
    .header { background: linear-gradient(135deg, #001a4d 0%, #000f2e 100%); padding: 32px 32px 24px; text-align: center; }
    .header .icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(252,185,19,.15); border: 2px solid rgba(252,185,19,.4); border-radius: 50%; font-size: 28px; margin-bottom: 14px; }
    .header h1 { color: #fcb913; font-size: 20px; font-weight: 800; letter-spacing: -.3px; margin-bottom: 4px; }
    .header p { color: rgba(255,255,255,.6); font-size: 13px; }
    .body { padding: 28px 32px; }
    .info-box { background: #eff6ff; border: 1px solid #93c5fd; border-left: 4px solid #3b82f6; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; color: #1e40af; }
    .section-label { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin-bottom: 10px; }
    .role-box { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px; }
    .role-row { display: flex; align-items: center; gap: 14px; }
    .role-chip { display: inline-block; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .role-old { background: #f3f4f6; color: #6b7280; text-decoration: line-through; }
    .role-new { background: #dcfce7; color: #166534; }
    .arrow { font-size: 18px; color: #9ca3af; }
    .notice { background: #fff8e1; border: 1px solid #fcd34d; border-left: 4px solid #fcb913; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px; font-size: 13px; color: #92400e; }
    .cta { text-align: center; margin: 8px 0 24px; }
    .cta a { display: inline-block; background: linear-gradient(135deg, #db9f01, #fcb913); color: #001a4d; text-decoration: none; font-weight: 800; font-size: 14px; padding: 13px 36px; border-radius: 10px; }
    .footer { background: #f8fafc; border-top: 1px solid #e5e7eb; padding: 18px 32px; text-align: center; font-size: 11px; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        @if(!empty($logoUrl))
        <div style="margin-bottom:16px;">
            <img src="{{ $logoUrl }}" alt="{{ $companyName }}" style="max-height:60px;max-width:200px;object-fit:contain;">
        </div>
        @else
        <div class="icon">🔐</div>
        @endif
        <h1>Access Level Updated</h1>
        <p>Your role in {{ $companyName }} has changed</p>
    </div>

    <div class="body">

        <div class="info-box">
            Hi <strong>{{ $userName }}</strong>, your access level in the {{ $companyName }} system has been updated by an administrator.
        </div>

        <div class="section-label">Role Change</div>
        <div class="role-box">
            <div class="role-row">
                <span class="role-chip role-old">{{ ucfirst(str_replace('_', ' ', $oldRole)) }}</span>
                <span class="arrow">→</span>
                <span class="role-chip role-new">{{ ucfirst(str_replace('_', ' ', $newRole)) }}</span>
            </div>
        </div>

        <div class="notice">
            ℹ️ Your new permissions are effective immediately. If you are currently logged in, please log out and back in to ensure the new role takes effect.
        </div>

        <div class="cta">
            <a href="{{ $appUrl }}/login">Go to Dashboard →</a>
        </div>

        <p style="font-size:13px;color:#6b7280;line-height:1.6;">
            If you believe this change was made in error, please contact your system administrator.
        </p>

    </div>

    <div class="footer">
        This email was sent by {{ $companyName }} production management system.<br>
        Please do not reply to this email.
    </div>

</div>
</body>
</html>
