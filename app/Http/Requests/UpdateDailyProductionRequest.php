<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'date'              => 'required|date',
            'shift'             => 'nullable|string|max:50',
            'mining_site'       => 'nullable|string|max:100',
            'ore_hoisted'        => 'required|numeric|min:0',
            'ore_hoisted_target' => 'nullable|numeric|min:0',
            'waste_hoisted'      => 'required|numeric|min:0',
            'ore_crushed'        => 'required|numeric|min:0',
            'ore_milled'         => 'required|numeric|min:0',
            'ore_milled_target'  => 'nullable|numeric|min:0',
            'gold_smelted'      => 'required|numeric|min:0',
            'purity_percentage' => 'required|numeric|min:0|max:100',
            'fidelity_price'    => 'required|numeric|min:0',
        ];
    }
}
