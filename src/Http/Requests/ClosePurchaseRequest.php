<?php

namespace Molitor\Purchase\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ClosePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('acl', 'purchase');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'delivery_date' => 'nullable|date',
        ];
    }
}
