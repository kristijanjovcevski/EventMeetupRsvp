<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'location'    => ['sometimes', 'string', 'max:255'],
            'starts_at'   => ['sometimes', 'date'],
            'capacity'    => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
