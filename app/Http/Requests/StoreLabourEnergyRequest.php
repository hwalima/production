<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLabourEnergyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'zesa_cost'    => 'required|numeric|min:0',
            'diesel_cost'  => 'required|numeric|min:0',
            'mining_cost' => 'nullable|numeric|min:0',
            'milling_cost' => 'nullable|numeric|min:0',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'date'         => ['required', 'date', Rule::unique('labour_energy', 'date')],
            'dept_costs'   => 'nullable|array',
            'dept_costs.*' => 'nullable|numeric|min:0',
        ];
    }
    public function messages(): array
    {
        return [
            'date.unique' => 'A Labour & Energy record already exists for this date. Edit the existing record instead.',
        ];
    }
}
