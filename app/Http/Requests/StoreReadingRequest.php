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
            'model' => [
                'required',
                'string',
            ],
            'uid' => [
                'required',
                'string',
            ],
            'timestamp' => [
                'required',
                'date_format:Y-m-d\TH:i:s\Z',
            ],
            'readings.temperature' => [
                'required',
                'decimal:2',
            ],
            'readings.humidity' => [
                'required',
                'decimal:2',
            ],
            'readings.pressure' => [
                'required',
                'decimal:2',
            ],
            'readings.noise' => [
                'required',
                'decimal:2',
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
            'readings.voltage' => [
                'required',
                'decimal:3',
            ],
        ];
    }
}
