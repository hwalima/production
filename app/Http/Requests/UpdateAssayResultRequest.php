<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssayResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'type' => 'required|in:fire_assay,gold_on_carbon,bottle_roll',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'assay_value' => 'required|numeric|min:0',
            'detection_limit' => 'nullable|numeric|min:0',
            'daily_production_id' => 'nullable|exists:daily_productions,id',
        ];
    }
}
