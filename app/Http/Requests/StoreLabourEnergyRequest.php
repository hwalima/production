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
            'zesa_cost'   => 'required|numeric|min:0',
            'diesel_cost' => 'required|numeric|min:0',
            'labour_cost' => 'required|numeric|min:0',
            'date'        => ['required', 'date', Rule::unique('labour_energy', 'date')],
        ];
    }
    public function messages(): array
    {
        return [
            'date.unique' => 'A Labour & Energy record already exists for this date. Edit the existing record instead.',
        ];
    }
}
