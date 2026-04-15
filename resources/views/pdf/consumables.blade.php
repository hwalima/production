@extends('pdf.layout')

@section('report-title', 'Consumables Usage Report')
@section('report-subtitle', \Carbon\Carbon::parse($month . '-01')->format('F Y'))

@section('content')

{{-- ── Blasting Consumables ─────────────────────────────── --}}
<div class="section-heading">Blasting Consumables</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="th-c">Fractures</th>
            <th class="th-c">Fuse</th>
            <th class="th-c">Carmes IEDs</th>
            <th class="th-c">Power Cords</th>
            <th class="th-c">ANFO</th>
            <th class="th-c">Oil</th>
            <th class="th-c">Drill Bits</th>
        </tr>
    </thead>
    <tbody>
        @forelse($blasting as $b)
        <tr>
            <td>{{ $b->date->format('d M Y') }}</td>
            <td class="td-c">{{ $b->fractures }}</td>
            <td class="td-c">{{ $b->fuse }}</td>
            <td class="td-c">{{ $b->carmes_ieds }}</td>
            <td class="td-c">{{ $b->power_cords }}</td>
            <td class="td-c">{{ $b->anfo }}</td>
            <td class="td-c">{{ $b->oil }}</td>
            <td class="td-c">{{ $b->drill_bits }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:14px;color:#9ca3af;">No blasting records for this period.</td></tr>
        @endforelse
    </tbody>
    @if($blasting->count())
    <tfoot>
        <tr>
            <td>Totals</td>
            <td class="td-c">{{ $blasting->sum('fractures') }}</td>
            <td class="td-c">{{ $blasting->sum('fuse') }}</td>
            <td class="td-c">{{ $blasting->sum('carmes_ieds') }}</td>
            <td class="td-c">{{ $blasting->sum('power_cords') }}</td>
            <td class="td-c">{{ $blasting->sum('anfo') }}</td>
            <td class="td-c">{{ $blasting->sum('oil') }}</td>
            <td class="td-c">{{ $blasting->sum('drill_bits') }}</td>
        </tr>
    </tfoot>
    @endif
</table>

{{-- ── Chemicals Usage ──────────────────────────────────── --}}
<div class="section-heading" style="margin-top:18px;">Chemicals Usage</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="th-c">NaCN</th>
            <th class="th-c">Lime</th>
            <th class="th-c">Caustic Soda</th>
            <th class="th-c">Iodised Salt</th>
            <th class="th-c">Mercury</th>
            <th class="th-c">Steel Balls</th>
            <th class="th-c">H&#8322;O&#8322;</th>
            <th class="th-c">Borax</th>
            <th class="th-c">HNO&#8323;</th>
            <th class="th-c">H&#8322;SO&#8324;</th>
        </tr>
    </thead>
    <tbody>
        @forelse($chemicals as $c)
        <tr>
            <td>{{ $c->date->format('d M Y') }}</td>
            <td class="td-c">{{ $c->sodium_cyanide }}</td>
            <td class="td-c">{{ $c->lime }}</td>
            <td class="td-c">{{ $c->caustic_soda }}</td>
            <td class="td-c">{{ $c->iodised_salt }}</td>
            <td class="td-c">{{ $c->mercury }}</td>
            <td class="td-c">{{ $c->steel_balls }}</td>
            <td class="td-c">{{ $c->hydrogen_peroxide }}</td>
            <td class="td-c">{{ $c->borax }}</td>
            <td class="td-c">{{ $c->nitric_acid }}</td>
            <td class="td-c">{{ $c->sulphuric_acid }}</td>
        </tr>
        @empty
        <tr><td colspan="11" style="text-align:center;padding:14px;color:#9ca3af;">No chemical records for this period.</td></tr>
        @endforelse
    </tbody>
    @if($chemicals->count())
    <tfoot>
        <tr>
            <td>Totals</td>
            <td class="td-c">{{ $chemicals->sum('sodium_cyanide') }}</td>
            <td class="td-c">{{ $chemicals->sum('lime') }}</td>
            <td class="td-c">{{ $chemicals->sum('caustic_soda') }}</td>
            <td class="td-c">{{ $chemicals->sum('iodised_salt') }}</td>
            <td class="td-c">{{ $chemicals->sum('mercury') }}</td>
            <td class="td-c">{{ $chemicals->sum('steel_balls') }}</td>
            <td class="td-c">{{ $chemicals->sum('hydrogen_peroxide') }}</td>
            <td class="td-c">{{ $chemicals->sum('borax') }}</td>
            <td class="td-c">{{ $chemicals->sum('nitric_acid') }}</td>
            <td class="td-c">{{ $chemicals->sum('sulphuric_acid') }}</td>
        </tr>
    </tfoot>
    @endif
</table>

@endsection
