<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Machine Service Alert</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6fb; color: #1e293b; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }
    .header { background: linear-gradient(135deg, #001a4d 0%, #000f2e 100%); padding: 32px 32px 24px; text-align: center; }
    .header .icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(252,185,19,.15); border: 2px solid rgba(252,185,19,.4); border-radius: 50%; font-size: 28px; margin-bottom: 14px; }
    .header h1 { color: #fcb913; font-size: 20px; font-weight: 800; letter-spacing: -.3px; margin-bottom: 4px; }
    .header p { color: rgba(255,255,255,.6); font-size: 13px; }
    .body { padding: 28px 32px; }
    .alert-box { background: #fff8e1; border: 1px solid #fcd34d; border-left: 4px solid #fcb913; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; color: #92400e; }
    .section-label { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 24px; }
    thead tr { background: #001a4d; }
    thead th { color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    thead th.r { text-align: right; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; color: #1e293b; }
    tbody td.r { text-align: right; }
    tbody td.code { font-family: monospace; font-weight: 700; font-size: 13px; }
    .badge-overdue { display: inline-block; background: #fee2e2; color: #991b1b; border-radius: 20px; padding: 2px 10px; font-size: 11px; font-weight: 700; }
    .cta { text-align: center; margin: 8px 0 24px; }
    .cta a { display: inline-block; background: linear-gradient(135deg, #db9f01, #fcb913); color: #001a4d; text-decoration: none; font-weight: 800; font-size: 14px; padding: 12px 32px; border-radius: 10px; }
    .footer { background: #f8fafc; border-top: 1px solid #e5e7eb; padding: 18px 32px; text-align: center; font-size: 11px; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <div class="icon">⚙️</div>
        <h1>Machine Service Alert</h1>
        <p>{{ $companyName }}</p>
    </div>

    {{-- Body --}}
    <div class="body">
        <div class="alert-box">
            ⚠️ <strong>{{ count($overdueList) }} machine{{ count($overdueList) > 1 ? 's are' : ' is' }} overdue for service</strong>
            as of {{ now()->format('d M Y') }}. Immediate attention is required to prevent equipment failure.
        </div>

        <div class="section-label">Overdue Machines</div>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Service Due</th>
                    <th class="r">Days Overdue</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($overdueList as $machine)
                @php $daysOverdue = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($machine->next_service_date)); @endphp
                <tr>
                    <td class="code">{{ $machine->machine_code }}</td>
                    <td>{{ $machine->description }}</td>
                    <td>{{ \Carbon\Carbon::parse($machine->next_service_date)->format('d M Y') }}</td>
                    <td class="r" style="font-weight:700;color:#991b1b;">{{ $daysOverdue }}d</td>
                    <td><span class="badge-overdue">OVERDUE</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cta">
            <a href="{{ $appUrl }}/machines">View Machines →</a>
        </div>

        <p style="font-size:12px;color:#9ca3af;text-align:center;">
            This alert is sent once per overdue event. You will receive a new alert if additional machines become overdue.
        </p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        {{ $companyName }} &mdash; Automated Alert &mdash; {{ now()->format('d M Y H:i') }}
    </div>

</div>
</body>
</html>
