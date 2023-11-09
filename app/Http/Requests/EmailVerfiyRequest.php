<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerfiyRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required',
            'verification_code' => 'required|max:4|min:4'
        ];
    }

    public function messages() : array
    {
        return [
            'token.required' => 'Token is required',
            'verification_code.required' => 'Verification code is required',
            'verification_code.max' => 'Invalid Verification code',
            'verification_code.min' => 'Invalid Verification code',
        ];
    }
}
