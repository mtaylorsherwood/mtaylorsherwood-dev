<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingRequest extends FormRequest
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
            'nickname' => [
                'required',
                'string',
            ],
            'timestamp' => [
                'required',
                'date_format:Y-m-d H:i:s',
            ],
            'readings.temperature' => [
                'required',
                'numeric:strict',
            ],
            'readings.humidity' => [
                'required',
                'numeric:strict',
            ],
            'readings.pressure' => [
                'required',
                'numeric:strict',
            ],
            'readings.noise' => [
                'required',
                'numeric:strict',
            ],
            'readings.pm1' => [
                'required',
                'numeric:strict',
            ],
            'readings.pm2_5' => [
                'required',
                'numeric:strict',
            ],
            'readings.pm10' => [
                'required',
                'numeric:strict',
            ],
        ];
    }
}
