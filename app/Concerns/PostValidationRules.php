<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\PostCategory;
use App\Enums\PostType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait PostValidationRules
{
    /**
     * Get the validation rules used to validate a post.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function postRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PostType::class)],
            'category' => ['nullable', 'prohibited_unless:type,post', Rule::enum(PostCategory::class)],
            'featured_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'project_id' => ['nullable', 'required_if:type,project_update', 'integer', 'exists:projects,id'],
            'body' => ['required', 'string'],
            'starts_at' => ['required_if:type,recap', 'nullable', 'date'],
            'ends_at' => ['required_if:type,recap', 'nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
