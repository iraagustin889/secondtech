<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(['Barang tidak sesuai', 'Barang rusak', 'Barang tidak sampai', 'Lainnya'])],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
