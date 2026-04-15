@extends('layouts.app')
@section('title', 'Consumables Report')
@section('page-title', 'Reports')
@section('content')
<div class="page-header">
    <h1 class="page-title">Consumables Usage Report</h1>
    <div class="fbar-ctrl" style="display:flex;gap:8px;align-items:center;">
        <form method="GET" style="display:contents;">
            <input type="month" name="month" value="{{ $month }}" class="fc-input" style="width:auto;">
            <button class="btn-add">Filter</button>
        </form>
        <a href="{{ route('reports.consumables.pdf', ['month' => $month]) }}"
           class="btn-add"
           style="background:#b45309;border-color:#b45309;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
            &#8675; Export PDF
        </a>
    </div>
</div>

<h2 class="page-title" style="font-size:1.1rem;margin-bottom:12px;">Blasting Consumables</h2>
<div class="data-card" style="margin-bottom:24px;">
    <div class="tbl-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Fractures</th>
                    <th>Fuse</th>
                    <th>Carmes IEDs</th>
                    <th>Power Cords</th>
                    <th>ANFO</th>
                    <th>Oil</th>
                    <th>Drill Bits</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blasting as $b)
                <tr>
                    <td>{{ $b->date->format('d M Y') }}</td>
                    <td class="text-center">{{ $b->fractures }}</td>
                    <td class="text-center">{{ $b->fuse }}</td>
                    <td class="text-center">{{ $b->carmes_ieds }}</td>
                    <td class="text-center">{{ $b->power_cords }}</td>
                    <td class="text-center">{{ $b->anfo }}</td>
                    <td class="text-center">{{ $b->oil }}</td>
                    <td class="text-center">{{ $b->drill_bits }}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:24px;color:#9ca3af;">No blasting records for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<h2 class="page-title" style="font-size:1.1rem;margin-bottom:12px;">Chemicals Usage</h2>
<div class="data-card">
    <div class="tbl-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>NaCN</th>
                    <th>Lime</th>
                    <th>Caustic Soda</th>
                    <th>Iodised Salt</th>
                    <th>Mercury</th>
                    <th>Steel Balls</th>
                    <th>H&#8322;O&#8322;</th>
                    <th>Borax</th>
                    <th>HNO&#8323;</th>
                    <th>H&#8322;SO&#8324;</th>
                </tr>
            </thead>
            <tbody>
                @forelse($chemicals as $c)
                <tr>
                    <td>{{ $c->date->format('d M Y') }}</td>
                    <td class="text-center">{{ $c->sodium_cyanide }}</td>
                    <td class="text-center">{{ $c->lime }}</td>
                    <td class="text-center">{{ $c->caustic_soda }}</td>
                    <td class="text-center">{{ $c->iodised_salt }}</td>
                    <td class="text-center">{{ $c->mercury }}</td>
                    <td class="text-center">{{ $c->steel_balls }}</td>
                    <td class="text-center">{{ $c->hydrogen_peroxide }}</td>
                    <td class="text-center">{{ $c->borax }}</td>
                    <td class="text-center">{{ $c->nitric_acid }}</td>
                    <td class="text-center">{{ $c->sulphuric_acid }}</td>
                </tr>
                @empty
                <tr><td colspan="11" style="text-align:center;padding:24px;color:#9ca3af;">No chemical records for this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
