<?php

namespace App\Http\Requests;

use App\Enums\ShippingMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'recipient_address' => ['required', 'string', 'max:2000'],
            'recipient_location' => ['required', 'string', 'max:150'],
            'shipping_method' => ['required', Rule::enum(ShippingMethod::class)],
            'quote_key' => ['nullable', 'string', 'size:64'],
        ];
    }
}
