<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelOrderRequest extends FormRequest
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
            'reason' => [
                'required',
                Rule::in([
                    'Berubah pikiran',
                    'Alamat atau data penerima salah',
                    'Ingin mengganti metode pembayaran',
                    'Penjual tidak merespons',
                    'Lainnya',
                ]),
            ],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
