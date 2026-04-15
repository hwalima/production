<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDrillingRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'end_name' => 'required|string|max:255',
            'hole_count' => 'required|numeric|min:0',
            'drill_steel_length' => 'required|numeric|min:0',
            'advance' => 'required|numeric|min:0',
            'date' => 'required|date',
        ];
    }
}
