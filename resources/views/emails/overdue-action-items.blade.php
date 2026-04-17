<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Overdue Action Items</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6fb; color: #1e293b; }
    .wrapper { max-width: 620px; margin: 32px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }
    .header { background: linear-gradient(135deg, #001a4d 0%, #000f2e 100%); padding: 32px 32px 24px; text-align: center; }
    .header .icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(252,185,19,.15); border: 2px solid rgba(252,185,19,.4); border-radius: 50%; font-size: 28px; margin-bottom: 14px; }
    .header h1 { color: #fcb913; font-size: 20px; font-weight: 800; letter-spacing: -.3px; margin-bottom: 4px; }
    .header p { color: rgba(255,255,255,.6); font-size: 13px; }
    .body { padding: 28px 32px; }
    .summary-box { background: #fff8e1; border: 1px solid #fcd34d; border-left: 4px solid #fcb913; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; color: #92400e; }
    .section-label { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin-bottom: 10px; }
    .items-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 24px; }
    .items-table th { background: #f1f5f9; color: #64748b; font-weight: 700; font-size: 11px; letter-spacing: .05em; text-transform: uppercase; padding: 8px 10px; text-align: left; border-bottom: 2px solid #e2e8f0; }
    .items-table td { padding: 10px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #374151; }
    .items-table tr:last-child td { border-bottom: none; }
    .badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .badge-high   { background: #fee2e2; color: #991b1b; }
    .badge-medium { background: #fff8e1; color: #92400e; }
    .badge-low    { background: #f1f5f9; color: #475569; }
    .overdue-date { color: #dc2626; font-weight: 600; }
    .comment-text { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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
        <div class="icon">📋</div>
        @endif
        <h1>Overdue Action Items</h1>
        <p>Daily digest — {{ now()->format('d M Y') }}</p>
    </div>

    <div class="body">

        <div class="summary-box">
            ⚠️ There {{ $items->count() === 1 ? 'is' : 'are' }} <strong>{{ $items->count() }} overdue action {{ $items->count() === 1 ? 'item' : 'items' }}</strong> that require attention.
        </div>

        <div class="section-label">Overdue Items</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Priority</th>
                    <th>Due Date</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->department?->name ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $item->priority }}">
                            {{ ucfirst($item->priority) }}
                        </span>
                    </td>
                    <td class="overdue-date">{{ $item->due_date?->format('d M Y') ?? '—' }}</td>
                    <td><span class="comment-text" title="{{ $item->comment }}">{{ Str::limit($item->comment, 60) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cta">
            <a href="{{ $appUrl }}/action-items">View All Action Items →</a>
        </div>

        <p style="font-size:13px;color:#6b7280;line-height:1.6;">
            This is an automated daily digest sent when open action items have passed their due date. Please update or reassign overdue items in the system.
        </p>

    </div>

    <div class="footer">
        This email was sent by {{ $companyName }} production management system.<br>
        Please do not reply to this email.
    </div>

</div>
</body>
</html>
