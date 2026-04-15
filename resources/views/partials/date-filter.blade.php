{{--
    Date range filter bar.
    Required vars: $routeName (string), $filterFrom, $filterTo, $isDefaultRange
--}}
@php
    $now = \Carbon\Carbon::now();
    $presets = [
        'This Month'   => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
        'Last Month'   => [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()],
        'Last 7 Days'  => [$now->copy()->subDays(6)->toDateString(), $now->toDateString()],
        'Last 30 Days' => [$now->copy()->subDays(29)->toDateString(), $now->toDateString()],
        'This Year'    => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
    ];
@endphp
<form method="GET" action="{{ route($routeName) }}">
    <div class="fbar">
        <span class="fbar-label">&#128197; Filter</span>
        <div class="fbar-ctrl">
            <input type="date" name="from" value="{{ $filterFrom }}" max="{{ date('Y-m-d') }}">
            <span class="fbar-sep">&rarr;</span>
            <input type="date" name="to"   value="{{ $filterTo }}"   max="{{ date('Y-m-d') }}">
            <button type="submit" class="fbar-apply">Apply</button>
        </div>
        @if(!$isDefaultRange)
            <span class="fbar-active">
                &#128269; {{ \Carbon\Carbon::parse($filterFrom)->format('d M') }} &ndash; {{ \Carbon\Carbon::parse($filterTo)->format('d M Y') }}
                &nbsp;<a href="{{ route($routeName) }}" style="color:inherit;text-decoration:none;opacity:.6;font-size:.9em">&times;</a>
            </span>
        @endif
        <div class="fbar-presets">
            @foreach($presets as $label => [$pFrom, $pTo])
                <a href="{{ route($routeName, ['from' => $pFrom, 'to' => $pTo]) }}"
                   class="fbar-preset {{ $filterFrom === $pFrom && $filterTo === $pTo ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
</form>