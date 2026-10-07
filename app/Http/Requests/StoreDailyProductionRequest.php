<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'date'              => 'required|date',
            'shift'             => 'required|string|max:50',
            'mining_site'       => 'required|string|max:100',
            'ore_crushed'        => 'required|numeric|min:0',
            'ore_milled'         => 'required|numeric|min:0',
            'ro_mine_milled'     => 'required|numeric|min:0|lte:ore_milled',
            'sanda_milled'       => 'nullable|numeric|min:0|required_if:sanda_milled_manual,1',
            'sanda_milled_manual'=> 'nullable|boolean',
            'ore_milled_target'  => 'nullable|numeric|min:0',
            'gold_smelted'      => 'required|numeric|min:0',
            'purity_percentage' => 'required|numeric|min:0|max:100',
            'fidelity_price'    => 'required|numeric|min:0',
        ];
    }
}
