<?php

namespace App\Http\Requests;

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
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            // SecondTech mempertemukan pembeli dan penjual secara langsung.
            // Lokasi serta waktu handover akan disepakati melalui pesanan dan chat.
            'payment_method' => ['required', Rule::in(['cod', 'qris'])],
        ];
    }
}
