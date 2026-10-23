<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Enums\BookCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait BookValidationRules
{
    /**
     * Get the validation rules used to validate a book.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function bookRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(BookCategory::class)],
            'genre' => ['required', 'string', 'max:255'],
            'pages' => ['required', 'integer', 'min:1'],
            'series' => ['nullable', 'string', 'max:255'],
            'is_reread' => ['boolean'],
            'review' => ['nullable', 'string'],
            'started_at' => ['nullable', 'date', 'required_with:finished_at'],
            'finished_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ];
    }

    /**
     * Normalize the "is_reread" checkbox, which is omitted from the request entirely when
     * unchecked, into an explicit boolean so it is always present in the validated data.
     */
    protected function prepareBookForValidation(): void
    {
        $this->merge(['is_reread' => $this->boolean('is_reread')]);
    }
}
