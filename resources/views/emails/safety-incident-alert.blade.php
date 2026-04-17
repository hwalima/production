<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Safety Incident Alert</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6fb; color: #1e293b; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }
    .header { background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%); padding: 32px 32px 24px; text-align: center; }
    .header .icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(252,185,19,.15); border: 2px solid rgba(252,185,19,.4); border-radius: 50%; font-size: 28px; margin-bottom: 14px; }
    .header h1 { color: #fca5a5; font-size: 20px; font-weight: 800; letter-spacing: -.3px; margin-bottom: 4px; }
    .header p { color: rgba(255,255,255,.6); font-size: 13px; }
    .body { padding: 28px 32px; }
    .alert-box { background: #fef2f2; border: 1px solid #fca5a5; border-left: 4px solid #ef4444; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; color: #991b1b; }
    .section-label { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin-bottom: 10px; }
    .detail-box { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px; }
    .detail-box table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .detail-box td { padding: 6px 0; vertical-align: top; border-bottom: 1px solid #f1f5f9; }
    .detail-box tr:last-child td { border-bottom: none; }
    .detail-box td.label { color: #6b7280; width: 55%; font-size: 13px; }
    .detail-box td.value { font-weight: 700; color: #dc2626; text-align: right; }
    .meta-box { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 20px; margin-bottom: 24px; font-size: 13px; color: #374151; }
    .meta-box table { width: 100%; border-collapse: collapse; }
    .meta-box td { padding: 4px 0; }
    .meta-box td.label { color: #9ca3af; width: 35%; }
    .cta { text-align: center; margin: 8px 0 24px; }
    .cta a { display: inline-block; background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; text-decoration: none; font-weight: 800; font-size: 14px; padding: 13px 36px; border-radius: 10px; }
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
        <div class="icon">⚠️</div>
        @endif
        <h1>Safety Incident Recorded</h1>
        <p>Immediate notification — {{ $companyName }}</p>
    </div>

    <div class="body">

        <div class="alert-box">
            🚨 A critical safety event has been logged and requires your immediate attention.
        </div>

        <div class="section-label">Incident Details</div>
        <div class="meta-box">
            <table>
                <tr>
                    <td class="label">Date</td>
                    <td><strong>{{ $incidentDate }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Department</td>
                    <td><strong>{{ $departmentName }}</strong></td>
                </tr>
            </table>
        </div>

        <div class="section-label">Recorded Indicators</div>
        <div class="detail-box">
            <table>
                @foreach($indicators as $name => $count)
                <tr>
                    <td class="label">{{ $name }}</td>
                    <td class="value">{{ $count }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="cta">
            <a href="{{ $appUrl }}/she">View SHE Records →</a>
        </div>

        <p style="font-size:13px;color:#6b7280;line-height:1.6;">
            Please review the full SHE report and take any required corrective action. This is an automated notification generated when a fatal incident or LTI is recorded in the system.
        </p>

    </div>

    <div class="footer">
        This email was sent by {{ $companyName }} production management system.<br>
        Please do not reply to this email.
    </div>

</div>
</body>
</html>
