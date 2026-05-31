<?php

namespace Molitor\Purchase\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Molitor\Purchase\Enums\PurchaseState;

class UpdatePurchaseStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('acl', 'purchase_status');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'state' => ['required', new Enum(PurchaseState::class)],
            'description' => 'nullable|string',
        ];
    }
}
