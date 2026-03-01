<?php

namespace App\Http\Requests\API\Matches;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatchRequest extends FormRequest
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
            'lost_item_id' => ['required', 'exists:items,id'],
            'found_item_id' => ['required', 'exists:items,id', 'different:lost_item_id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
