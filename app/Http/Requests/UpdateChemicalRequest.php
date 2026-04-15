<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChemicalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'sodium_cyanide' => 'required|numeric|min:0',
            'lime' => 'required|numeric|min:0',
            'caustic_soda' => 'required|numeric|min:0',
            'iodised_salt' => 'required|numeric|min:0',
            'mercury' => 'required|numeric|min:0',
            'steel_balls' => 'required|numeric|min:0',
            'hydrogen_peroxide' => 'required|numeric|min:0',
            'borax' => 'required|numeric|min:0',
            'nitric_acid' => 'required|numeric|min:0',
            'sulphuric_acid' => 'required|numeric|min:0',
            'date' => 'required|date',
        ];
    }
}
