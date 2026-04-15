<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLabourEnergyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'zesa_cost' => 'required|numeric|min:0',
            'diesel_cost' => 'required|numeric|min:0',
            'labour_cost' => 'required|numeric|min:0',
            'date' => 'required|date',
        ];
    }
}
