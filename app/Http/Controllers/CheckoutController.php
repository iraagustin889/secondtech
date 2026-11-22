<?php

namespace App\Http\Controllers;

use App\Actions\CreateOrderAction;
use App\Exceptions\OrderUnavailableException;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Product;
use App\Models\ServiceFeeConfiguration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function create(Request $request, Product $product): View
    {
        abort_unless($product->isPurchasable(), 404);
        abort_if($product->user_id === $request->user()->id, 403, 'Anda tidak dapat membeli produk milik sendiri.');

        $user = $request->user();
        $quantity = max(1, min((int) old('quantity', 1), $product->stock));
        $activeServiceFee = ServiceFeeConfiguration::active();
        $qrisServiceFee = ServiceFeeConfiguration::calculate((int) $product->price * $quantity);

        return view('checkout.create', [
            'product' => $product,
            'quantity' => $quantity,
            'recipient' => [
                'recipient_name' => old('recipient_name', $user?->name),
                'recipient_phone' => old('recipient_phone', $user?->phone),
                'recipient_address' => old('recipient_address', $user?->address),
                'recipient_location' => old('recipient_location', $user?->location),
            ],
            'qrisServiceFee' => $qrisServiceFee,
            'serviceFeeType' => $activeServiceFee?->type,
            'serviceFeeValue' => $activeServiceFee ? (float) $activeServiceFee->value : 0,
            'serviceFeeLabel' => $activeServiceFee
                ? ($activeServiceFee->type === 'percentage'
                    ? rtrim(rtrim(number_format((float) $activeServiceFee->value, 2, ',', '.'), '0'), ',').'%'
                    : 'Nominal')
                : null,
        ]);
    }

    public function store(StoreCheckoutRequest $request, Product $product, CreateOrderAction $createOrder): RedirectResponse
    {
        $data = $request->validated();

        try {
            $order = $createOrder->handle($request->user(), $product, $data);
        } catch (OrderUnavailableException $exception) {
            return to_route('catalog.index')->with('error', $exception->getMessage());
        }

        return to_route('orders.show', $order)->with('success', 'Pesanan berhasil dibuat.');
    }
}
