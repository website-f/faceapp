<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'person_type' => ['nullable', 'integer', 'in:1,2,3'],
            'verify_style' => ['nullable', 'integer'],
            'ac_group_number' => ['nullable', 'integer', 'min:0'],
            'photo_quality' => ['nullable', 'integer', 'in:0,1'],
            'photo_data_url' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || ! preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $value)) {
                        $fail('The '.$attribute.' field must be a JPEG or PNG data URL.');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'employee_id' => trim((string) $this->input('employee_id')),
            'name' => trim((string) $this->input('name')),
        ]);
    }
}
