<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome to {{ $companyName }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6fb; color: #1e293b; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }
    .header { background: linear-gradient(135deg, #001a4d 0%, #000f2e 100%); padding: 32px 32px 24px; text-align: center; }
    .header .icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(252,185,19,.15); border: 2px solid rgba(252,185,19,.4); border-radius: 50%; font-size: 28px; margin-bottom: 14px; }
    .header h1 { color: #fcb913; font-size: 20px; font-weight: 800; letter-spacing: -.3px; margin-bottom: 4px; }
    .header p { color: rgba(255,255,255,.6); font-size: 13px; }
    .body { padding: 28px 32px; }
    .welcome-box { background: #f0fdf4; border: 1px solid #86efac; border-left: 4px solid #22c55e; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; color: #166534; }
    .section-label { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin-bottom: 10px; }
    .credentials { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px; }
    .credentials table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .credentials td { padding: 7px 0; vertical-align: top; }
    .credentials td.label { color: #6b7280; width: 38%; font-size: 13px; }
    .credentials td.value { font-weight: 600; color: #1e293b; word-break: break-all; }
    .credentials td.value.password { font-family: 'Courier New', Courier, monospace; font-size: 15px; background: #fffbeb; border: 1px dashed #fcd34d; border-radius: 6px; padding: 4px 10px; letter-spacing: .05em; }
    .notice { background: #fff8e1; border: 1px solid #fcd34d; border-left: 4px solid #fcb913; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px; font-size: 13px; color: #92400e; }
    .cta { text-align: center; margin: 8px 0 24px; }
    .cta a { display: inline-block; background: linear-gradient(135deg, #db9f01, #fcb913); color: #001a4d; text-decoration: none; font-weight: 800; font-size: 14px; padding: 13px 36px; border-radius: 10px; }
    .footer { background: #f8fafc; border-top: 1px solid #e5e7eb; padding: 18px 32px; text-align: center; font-size: 11px; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        @if(!empty($logoUrl))
        <div style="margin-bottom:16px;">
            <img src="{{ $logoUrl }}" alt="{{ $companyName }}" style="max-height:60px;max-width:200px;object-fit:contain;">
        </div>
        @else
        <div class="icon">👋</div>
        @endif
        <h1>Welcome to {{ $companyName }}</h1>
        <p>Your account has been created</p>
    </div>

    {{-- Body --}}
    <div class="body">

        <div class="welcome-box">
            ✅ Hi <strong>{{ $userName }}</strong>, your account is ready. Use the credentials below to sign in.
        </div>

        <div class="section-label">Your Login Credentials</div>
        <div class="credentials">
            <table>
                <tr>
                    <td class="label">Login URL</td>
                    <td class="value"><a href="{{ $appUrl }}/login" style="color:#001a4d;">{{ $appUrl }}/login</a></td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="value">{{ $userEmail }}</td>
                </tr>
                <tr>
                    <td class="label">Password</td>
                    <td class="value password">{{ $plainPassword }}</td>
                </tr>
            </table>
        </div>

        <div class="notice">
            🔒 <strong>Important:</strong> This is your temporary password. Please log in and change it immediately via your profile settings.
        </div>

        <div class="cta">
            <a href="{{ $appUrl }}/login">Sign In Now →</a>
        </div>

        <p style="font-size:13px;color:#6b7280;line-height:1.6;">
            If you have any questions or need help, please contact your system administrator.
        </p>

    </div>

    <div class="footer">
        This email was sent by {{ $companyName }} production management system.<br>
        Please do not reply to this email.
    </div>

</div>
</body>
</html>
