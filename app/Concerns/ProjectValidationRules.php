<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\ProjectStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProjectValidationRules
{
    /**
     * Get the validation rules used to validate a project.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function projectRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'blurb' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'image_ids' => ['nullable', 'array', 'max:5'],
            'image_ids.*' => ['integer', 'exists:images,id'],
        ];
    }
}
