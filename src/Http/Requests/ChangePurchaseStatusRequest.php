<?php

namespace Molitor\Purchase\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ChangePurchaseStatusRequest extends FormRequest
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
            'purchase_status_id' => 'required|exists:purchase_statuses,id',
            'comment' => 'nullable|string',
        ];
    }
}
