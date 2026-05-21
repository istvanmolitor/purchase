<?php

namespace Molitor\Purchase\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purchase_status_id' => 'required|exists:purchase_statuses,id',
            'url' => 'nullable|url|max:255',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'comment' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'total_price' => 'nullable|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'purchase_items' => 'required|array|min:1',
            'purchase_items.*.product_id' => 'required|exists:products,id',
            'purchase_items.*.quantity' => 'required|numeric|min:0.0001',
            'purchase_items.*.price' => 'nullable|numeric|min:0',
            'purchase_items.*.comment' => 'nullable|string',
        ];
    }
}

