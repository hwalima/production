<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account Removed</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6fb; color: #1e293b; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }
    .header { background: linear-gradient(135deg, #001a4d 0%, #000f2e 100%); padding: 32px 32px 24px; text-align: center; }
    .header .icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(252,185,19,.15); border: 2px solid rgba(252,185,19,.4); border-radius: 50%; font-size: 28px; margin-bottom: 14px; }
    .header h1 { color: #fcb913; font-size: 20px; font-weight: 800; letter-spacing: -.3px; margin-bottom: 4px; }
    .header p { color: rgba(255,255,255,.6); font-size: 13px; }
    .body { padding: 28px 32px; }
    .alert-box { background: #fef2f2; border: 1px solid #fca5a5; border-left: 4px solid #ef4444; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; color: #991b1b; }
    .detail-box { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px; }
    .detail-box table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .detail-box td { padding: 6px 0; vertical-align: top; }
    .detail-box td.label { color: #6b7280; width: 35%; font-size: 13px; }
    .detail-box td.value { font-weight: 600; color: #1e293b; }
    .notice { background: #fff8e1; border: 1px solid #fcd34d; border-left: 4px solid #fcb913; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px; font-size: 13px; color: #92400e; }
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
        <div class="icon">🗑️</div>
        @endif
        <h1>Account Removed</h1>
        <p>{{ $companyName }} Production System</p>
    </div>

    <div class="body">

        <div class="alert-box">
            Hi <strong>{{ $userName }}</strong>, your account has been removed from the {{ $companyName }} production management system.
        </div>

        <div class="detail-box">
            <table>
                <tr>
                    <td class="label">Name</td>
                    <td class="value">{{ $userName }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="value">{{ $userEmail }}</td>
                </tr>
                <tr>
                    <td class="label">System</td>
                    <td class="value"><a href="{{ $appUrl }}" style="color:#001a4d;">{{ $appUrl }}</a></td>
                </tr>
            </table>
        </div>

        <div class="notice">
            ℹ️ You no longer have access to the system. If you believe this was done in error, please contact your administrator directly.
        </div>

        <p style="font-size:13px;color:#6b7280;line-height:1.6;">
            Any data you created in the system has been retained for record-keeping purposes.
        </p>

    </div>

    <div class="footer">
        This email was sent by {{ $companyName }} production management system.<br>
        Please do not reply to this email.
    </div>

</div>
</body>
</html>
