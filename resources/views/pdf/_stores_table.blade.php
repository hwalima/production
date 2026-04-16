<table class="data-table">
    <thead>
        <tr>
            <th>Item</th>
            <th class="th-c">Pack / Use Unit</th>
            <th class="th-r">Pack Cost</th>
            <th class="th-r">Unit Cost</th>
            <th class="th-r">In Stock</th>
            <th class="th-r">Stock Value</th>
            <th class="th-c">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td style="{{ $item->out_of_stock ? 'color:#9ca3af;' : '' }}">{{ $item->name }}</td>
            <td class="td-c muted">
                {{ number_format($item->units_per_pack, $item->units_per_pack == intval($item->units_per_pack) ? 0 : 2) }}
                {{ $item->use_unit }} / {{ $item->purchase_unit }}
            </td>
            <td class="td-r">{{ $currencySymbol }}{{ number_format($item->pack_cost, 2) }}</td>
            <td class="td-r gold">
                {{ $currencySymbol }}{{ number_format($item->unit_cost, $item->unit_cost < 1 ? 4 : 2) }}
            </td>
            <td class="td-r" style="font-weight:bold;{{ $item->out_of_stock ? 'color:#b91c1c;' : ($item->low_stock ? 'color:#b45309;' : '') }}">
                {{ number_format($item->current_stock, $item->current_stock == intval($item->current_stock) ? 0 : 2) }}
                <span style="font-weight:normal;font-size:7.5px;color:#9ca3af;"> {{ $item->use_unit }}</span>
            </td>
            <td class="td-r" style="font-weight:bold;">{{ $currencySymbol }}{{ number_format($item->stock_value, 2) }}</td>
            <td class="td-c">
                @if($item->out_of_stock)
                    <span style="color:#b91c1c;font-weight:bold;">OUT</span>
                @elseif($item->low_stock)
                    <span style="color:#b45309;font-weight:bold;">LOW</span>
                @else
                    <span style="color:#16a34a;font-weight:bold;">OK</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">Subtotal</td>
            <td class="td-r">{{ $currencySymbol }}{{ number_format($items->sum('stock_value'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
