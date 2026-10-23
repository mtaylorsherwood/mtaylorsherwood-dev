<?php

declare(strict_types=1);

namespace App\Http\Requests\Images;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreImageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:8192',
                'dimensions:max_width=6000,max_height=6000',
            ],
        ];
    }
}
