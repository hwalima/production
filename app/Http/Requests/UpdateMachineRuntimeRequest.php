<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMachineRuntimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'machine_code' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'service_after_hours' => 'required|numeric|min:0',
        ];
    }
}
