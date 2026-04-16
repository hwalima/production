@extends('pdf.layout')

@section('report-title', 'Stores Inventory Report')
@section('report-subtitle', 'Generated ' . now()->format('d F Y'))

@section('content')

{{-- ── Summary tiles ── --}}
<table class="summary-grid" style="width:75%;">
    <tr>
        <td style="width:33.33%;">
            <div class="tile-label">Total Items</div>
            <div class="tile-value">{{ $consumables->count() }}</div>
        </td>
        <td style="width:33.33%;">
            <div class="tile-label">Total Stock Value</div>
            <div class="tile-value gold">{{ $currencySymbol }}{{ number_format($totalValue, 2) }}</div>
        </td>
        <td style="width:33.33%;">
            <div class="tile-label">Low / Out of Stock</div>
            <div class="tile-value {{ $lowStockCount ? '' : 'green' }}" style="{{ $lowStockCount ? 'color:#b45309;' : '' }}">
                {{ $lowStockCount }}
            </div>
        </td>
    </tr>
</table>

{{-- ── Blasting items ── --}}
@php $blasting = $consumables->where('category','blasting'); @endphp
@if($blasting->count())
<div class="section-heading">Blasting</div>
@include('pdf._stores_table', ['items' => $blasting])
@endif

{{-- ── Chemicals ── --}}
@php $chemicals = $consumables->where('category','chemicals'); @endphp
@if($chemicals->count())
<div class="section-heading">Chemicals</div>
@include('pdf._stores_table', ['items' => $chemicals])
@endif

{{-- ── Mechanical ── --}}
@php $mechanical = $consumables->where('category','mechanical'); @endphp
@if($mechanical->count())
<div class="section-heading">Mechanical</div>
@include('pdf._stores_table', ['items' => $mechanical])
@endif

{{-- ── PPE ── --}}
@php $ppe = $consumables->where('category','ppe'); @endphp
@if($ppe->count())
<div class="section-heading">PPE</div>
@include('pdf._stores_table', ['items' => $ppe])
@endif

{{-- ── General / other categories ── --}}
@php $other = $consumables->whereNotIn('category',['blasting','chemicals','mechanical','ppe']); @endphp
@if($other->count())
<div class="section-heading">General / Other</div>
@include('pdf._stores_table', ['items' => $other])
@endif

{{-- ── Grand Total ── --}}
<table class="data-table" style="margin-top:14px;">
    <tfoot>
        <tr>
            <td colspan="5" style="font-weight:700;font-size:9px;">GRAND TOTAL — All Categories ({{ $consumables->count() }} items)</td>
            <td class="td-r" style="font-weight:700;font-size:9px;">{{ $currencySymbol }}{{ number_format($totalValue, 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

@endsection
