<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Low Stock Alert</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6fb; color: #1e293b; }
    .wrapper { max-width: 620px; margin: 32px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }
    .header { background: linear-gradient(135deg, #001a4d 0%, #000f2e 100%); padding: 32px 32px 24px; text-align: center; }
    .header .icon { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(252,185,19,.15); border: 2px solid rgba(252,185,19,.4); border-radius: 50%; font-size: 28px; margin-bottom: 14px; }
    .header h1 { color: #fcb913; font-size: 20px; font-weight: 800; letter-spacing: -.3px; margin-bottom: 4px; }
    .header p { color: rgba(255,255,255,.6); font-size: 13px; }
    .body { padding: 28px 32px; }
    .alert-box { background: #fff8e1; border: 1px solid #fcd34d; border-left: 4px solid #fcb913; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; color: #92400e; line-height: 1.5; }
    .section-label { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 24px; }
    thead tr { background: #001a4d; }
    thead th { color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    thead th.r { text-align: right; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; color: #1e293b; vertical-align: middle; }
    tbody td.r { text-align: right; font-variant-numeric: tabular-nums; }
    tbody td.name { font-weight: 700; }
    .badge-cat {
        display: inline-block; border-radius: 20px; padding: 2px 9px;
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
    }
    .badge-out   { background: #fee2e2; color: #991b1b; }
    .badge-low   { background: #fff3cd; color: #854d0e; }
    .stock-out   { color: #dc2626; font-weight: 800; }
    .stock-low   { color: #d97706; font-weight: 700; }
    .deficit-val { color: #dc2626; font-weight: 700; }
    .cta { text-align: center; margin: 8px 0 24px; }
    .cta a { display: inline-block; background: linear-gradient(135deg, #db9f01, #fcb913); color: #001a4d; text-decoration: none; font-weight: 800; font-size: 14px; padding: 12px 32px; border-radius: 10px; }
    .footer { background: #f8fafc; border-top: 1px solid #e5e7eb; padding: 18px 32px; text-align: center; font-size: 11px; color: #9ca3af; line-height: 1.6; }
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
        <div class="icon">📦</div>
        @endif
        <h1>Low Stock Alert</h1>
        <p>{{ $companyName }}</p>
    </div>

    {{-- Body --}}
    <div class="body">

        <div class="alert-box">
            ⚠️ <strong>{{ count($lowItems) }} consumable item{{ count($lowItems) > 1 ? 's are' : ' is' }} at or below reorder level</strong>
            as of {{ now()->format('d M Y') }}. Please arrange replenishment to avoid disruption to operations.
        </div>

        <div class="section-label">Items Requiring Attention</div>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th class="r">Current Stock</th>
                    <th class="r">Reorder Level</th>
                    <th class="r">Deficit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowItems as $item)
                @php
                    $isOut = $item['current_stock'] <= 0;
                    $cat   = ucfirst(str_replace('_', ' ', $item['category']));
                @endphp
                <tr>
                    <td class="name">{{ $item['name'] }}</td>
                    <td>{{ $cat }}</td>
                    <td class="r {{ $isOut ? 'stock-out' : 'stock-low' }}">
                        {{ number_format($item['current_stock'], 2) }} {{ $item['use_unit'] }}
                    </td>
                    <td class="r" style="color:#6b7280;">
                        {{ number_format($item['reorder_level'], 2) }} {{ $item['use_unit'] }}
                    </td>
                    <td class="r deficit-val">
                        {{ number_format($item['deficit'], 2) }} {{ $item['use_unit'] }}
                    </td>
                    <td>
                        @if($isOut)
                            <span class="badge-cat badge-out">OUT OF STOCK</span>
                        @else
                            <span class="badge-cat badge-low">LOW STOCK</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cta">
            <a href="{{ $appUrl }}/consumables">Manage Consumables →</a>
        </div>

        <p style="font-size:12px;color:#9ca3af;text-align:center;line-height:1.5;">
            This alert is sent daily when items are below their reorder level.<br>
            Update stock by recording a purchase in the Consumables module.
        </p>

    </div>

    {{-- Footer --}}
    <div class="footer">
        {{ $companyName }} &mdash; Automated Daily Alert &mdash; {{ now()->format('d M Y H:i') }}
    </div>

</div>
</body>
</html>
