<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location'    => ['required', 'string', 'max:255'],
            'starts_at'   => ['required', 'date', 'after:now'],
            'capacity'    => ['required', 'integer', 'min:1'],
        ];
    }
}
