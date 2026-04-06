<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('settings.billing.update');
    }

    public function rules(): array
    {
        return [
            'invoice_prefix' => 'required|string|max:20',
            'receipt_prefix' => 'required|string|max:20',
            'estimate_prefix' => 'required|string|max:20',
            'refund_prefix' => 'required|string|max:20',
            'tax_name' => 'required|string|max:50',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'invoice_footer' => 'nullable|string',
            'auto_generate_invoice_number' => 'required|boolean',
            'allow_manual_discount' => 'required|boolean',
            'require_discount_approval' => 'required|boolean',
        ];
    }
}
