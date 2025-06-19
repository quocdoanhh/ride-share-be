<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => 'required|numeric',
            'make' => 'required|string',
            'model' => 'required|string',
            'color' => 'required|alpha',
            'license_plate' => 'required|string',
            'name' => 'required|string',
        ];
    }
}
