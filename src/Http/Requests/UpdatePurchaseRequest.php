<?php

namespace Molitor\Purchase\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePurchaseRequest extends FormRequest
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
            'purchase_items' => 'nullable|array',
            'purchase_items.*.id' => 'nullable|integer|exists:purchase_items,id',
            'purchase_items.*.product_id' => 'required_with:purchase_items.*|exists:products,id',
            'purchase_items.*.quantity' => 'required_with:purchase_items.*|numeric|min:0.0001',
            'purchase_items.*.price' => 'nullable|numeric|min:0',
            'purchase_items.*.comment' => 'nullable|string',
            'purchase_extra_items' => 'nullable|array',
            'purchase_extra_items.*.id' => 'nullable|integer|exists:purchase_extra_items,id',
            'purchase_extra_items.*.purchase_extra_item_type_id' => 'required_with:purchase_extra_items.*|exists:purchase_extra_item_types,id',
            'purchase_extra_items.*.price' => 'nullable|numeric|min:0',
            'purchase_extra_items.*.comment' => 'nullable|string',
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasItems = ! empty($this->input('purchase_items')) || ! empty($this->input('purchase_extra_items'));

            if (! $hasItems) {
                $validator->errors()->add('items', 'Legalabb egy item szukseges: beszerzesi tetel vagy extra item.');
            }
        });
    }
}
