<?php

declare(strict_types=1);

namespace App\Http\Requests\Books;

use App\Models\Book;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FinishBookRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|Closure|string>
     */
    public function rules(): array
    {
        return [
            'finished_at' => ['required', 'date', $this->afterStartedAt(...)],
        ];
    }

    /**
     * Ensure the finish date falls on or after the book's start date, and that the book has
     * actually been started.
     */
    protected function afterStartedAt(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var Book $book */
        $book = $this->route('book');

        if ($book->started_at === null) {
            $fail('This book has not been started yet.');

            return;
        }

        if ($value < $book->started_at->toDateString()) {
            $fail('The finished date must be on or after the date you started reading.');
        }
    }
}
