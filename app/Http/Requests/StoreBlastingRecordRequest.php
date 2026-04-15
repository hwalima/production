<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlastingRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'fractures' => 'required|numeric|min:0',
            'fuse' => 'required|numeric|min:0',
            'carmes_ieds' => 'required|numeric|min:0',
            'power_cords' => 'required|numeric|min:0',
            'anfo' => 'required|numeric|min:0',
            'oil' => 'required|numeric|min:0',
            'drill_bits' => 'required|numeric|min:0',
            'date' => 'required|date',
        ];
    }
}
