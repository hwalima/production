<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #1e293b;
        background: #fff;
    }

    /* ── Page chrome ── */
    @page {
        margin: 14mm 12mm 18mm 12mm;
    }

    /* ── Header ── */
    .pdf-header {
        width: 100%;
        border-bottom: 2px solid #001a4d;
        padding-bottom: 10px;
        margin-bottom: 14px;
    }
    .pdf-header table { width: 100%; border-collapse: collapse; }
    .pdf-header td { vertical-align: middle; }
    .pdf-header .logo-cell { width: 80px; }
    .pdf-header img.logo {
        max-width: 72px;
        max-height: 52px;
        object-fit: contain;
    }
    .pdf-header .logo-placeholder {
        width: 60px; height: 44px;
        background: #001a4d;
        border-radius: 4px;
        display: inline-block;
    }
    .pdf-header .company-cell { padding-left: 12px; }
    .pdf-header .company-name {
        font-size: 14px;
        font-weight: bold;
        color: #001a4d;
        margin-bottom: 2px;
    }
    .pdf-header .company-meta {
        font-size: 8px;
        color: #6b7280;
        line-height: 1.5;
    }
    .pdf-header .report-title-cell {
        text-align: right;
        vertical-align: bottom;
    }
    .pdf-header .report-title {
        font-size: 13px;
        font-weight: bold;
        color: #001a4d;
    }
    .pdf-header .report-subtitle {
        font-size: 8.5px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* ── Footer (fixed position) ── */
    .pdf-footer {
        position: fixed;
        bottom: -14mm;
        left: 0; right: 0;
        border-top: 1px solid #d1d5db;
        padding-top: 4px;
        font-size: 7.5px;
        color: #9ca3af;
    }
    .pdf-footer table { width: 100%; border-collapse: collapse; }
    .pdf-footer .footer-left  { text-align: left; }
    .pdf-footer .footer-right { text-align: right; }
    .pdf-footer .page-num::after { content: counter(page); }
    .pdf-footer .page-total::after { content: counter(pages); }

    /* ── Summary tiles ── */
    .summary-grid {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }
    .summary-grid td {
        width: 25%;
        padding: 8px 10px;
        border: 1px solid #e5e7eb;
        vertical-align: top;
    }
    .tile-label {
        font-size: 7.5px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 3px;
    }
    .tile-value {
        font-size: 14px;
        font-weight: bold;
        color: #001a4d;
    }
    .tile-value.green { color: #16a34a; }
    .tile-value.gold  { color: #b45309; }

    /* ── Data table ── */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5px;
        margin-bottom: 10px;
    }
    .data-table th {
        background: #001a4d;
        color: #fff;
        padding: 5px 6px;
        text-align: left;
        font-weight: bold;
        font-size: 8px;
        white-space: nowrap;
    }
    .data-table th.th-r,
    .data-table td.td-r { text-align: right; }
    .data-table th.th-c,
    .data-table td.td-c { text-align: center; }
    .data-table tbody tr:nth-child(even) { background: #f8fafc; }
    .data-table tbody tr:nth-child(odd)  { background: #fff; }
    .data-table td {
        padding: 4px 6px;
        border-bottom: 1px solid #e5e7eb;
        color: #1e293b;
    }
    .data-table tfoot td {
        background: #001a4d;
        color: #fff;
        font-weight: bold;
        padding: 5px 6px;
    }
    .data-table tfoot td.td-r { text-align: right; }
    .data-table tfoot td.td-c { text-align: center; }

    /* ── Section heading ── */
    .section-heading {
        font-size: 10px;
        font-weight: bold;
        color: #001a4d;
        border-left: 3px solid #fcb913;
        padding-left: 6px;
        margin-bottom: 8px;
        margin-top: 14px;
    }

    .gold { color: #b45309; font-weight: 600; }
    .green { color: #16a34a; }
    .muted { color: #6b7280; }
</style>
</head>
<body>

{{-- ── Header ──────────────────────────────────────────── --}}
<div class="pdf-header">
    <table>
        <tr>
            <td class="logo-cell">
                @if(!empty($logoBase64))
                    <img class="logo" src="{{ $logoBase64 }}" alt="Logo">
                @else
                    <span class="logo-placeholder"></span>
                @endif
            </td>
            <td class="company-cell">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-meta">
                    @if(!empty($companyLocation)){{ $companyLocation }}<br>@endif
                    @if(!empty($companyPhone))Tel: {{ $companyPhone }}&nbsp;&nbsp;@endif
                    @if(!empty($companyEmail)){{ $companyEmail }}@endif
                </div>
            </td>
            <td class="report-title-cell">
                <div class="report-title">@yield('report-title')</div>
                <div class="report-subtitle">@yield('report-subtitle')</div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Footer ───────────────────────────────────────────── --}}
<div class="pdf-footer">
    <table>
        <tr>
            <td class="footer-left">{{ $companyName }} &mdash; Confidential</td>
            <td class="footer-right">
                Generated {{ now()->format('d M Y H:i') }}
                &nbsp;&nbsp;
                Page <span class="page-num"></span> of <span class="page-total"></span>
            </td>
        </tr>
    </table>
</div>

{{-- ── Content ──────────────────────────────────────────── --}}
@yield('content')

</body>
</html>
