<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'required',
                Rule::in(['open', 'in_progress', 'closed']),
            ],
            'priority' => [
                'sometimes',
                'required',
                Rule::in(['low', 'medium', 'high']),
            ],
        ];
    }
}
