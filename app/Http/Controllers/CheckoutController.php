<?php

namespace App\Http\Controllers;

use App\Actions\CreateOrderAction;
use App\Enums\ShippingMethod;
use App\Exceptions\OrderUnavailableException;
use App\Exceptions\ShippingQuoteUnavailableException;
use App\Http\Requests\QuoteShippingRequest;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Product;
use App\Services\ShippingQuoteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CheckoutController extends Controller
{
    public function create(Request $request, Product $product): View
    {
        abort_unless($product->isPurchasable(), 404);

        /** @var array{recipient_name?: string, recipient_phone?: string, recipient_address?: string, recipient_location?: string, destination_id?: int, destination_name?: string, quotes?: array<int, array<string, mixed>>} $savedCheckout */
        $savedCheckout = $request->session()->get($this->sessionKey($product), []);
        $user = $request->user();

        return view('checkout.create', [
            'product' => $product,
            'checkout' => $savedCheckout,
            'recipient' => [
                'recipient_name' => old('recipient_name', $savedCheckout['recipient_name'] ?? $user?->name),
                'recipient_phone' => old('recipient_phone', $savedCheckout['recipient_phone'] ?? $user?->phone),
                'recipient_address' => old('recipient_address', $savedCheckout['recipient_address'] ?? $user?->address),
                'recipient_location' => old('recipient_location', $savedCheckout['recipient_location'] ?? $user?->location),
            ],
        ]);
    }

    public function destinations(Request $request, ShippingQuoteService $shippingQuotes): JsonResponse
    {
        $data = $request->validate(['q' => ['required', 'string', 'min:3', 'max:100']]);

        try {
            return response()->json(['data' => $shippingQuotes->destinations($data['q'])]);
        } catch (ShippingQuoteUnavailableException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function quote(QuoteShippingRequest $request, Product $product, ShippingQuoteService $shippingQuotes): RedirectResponse
    {
        abort_unless($product->isPurchasable(), 404);

        $data = $request->validated();

        try {
            $quotes = $shippingQuotes->quotes(
                $product,
                (int) $data['destination_id'],
                $data['destination_name'],
            );
        } catch (ShippingQuoteUnavailableException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        if ($quotes === []) {
            return back()->withInput()->with('error', 'Tidak ada layanan ekspedisi yang tersedia untuk tujuan tersebut.');
        }

        $request->session()->put($this->sessionKey($product), [
            ...$data,
            'quotes' => $quotes,
        ]);

        return to_route('checkout.create', $product)
            ->with('success', 'Ongkir tersedia. Pilih layanan ekspedisi untuk melanjutkan.');
    }

    public function store(StoreCheckoutRequest $request, Product $product, CreateOrderAction $createOrder): RedirectResponse
    {
        $data = $request->validated();
        $method = ShippingMethod::from($data['shipping_method']);
        $shippingQuote = [];

        if ($method === ShippingMethod::Expedition) {
            /** @var array{quotes?: array<int, array<string, mixed>>} $savedCheckout */
            $savedCheckout = $request->session()->get($this->sessionKey($product), []);
            $shippingQuote = collect($savedCheckout['quotes'] ?? [])
                ->first(fn (array $quote): bool => hash_equals($quote['key'] ?? '', $data['quote_key'] ?? ''));

            if (! is_array($shippingQuote)) {
                return back()->withInput()->with('error', 'Pilihan ongkir sudah kedaluwarsa. Silakan cek ongkir kembali.');
            }
        }

        try {
            $order = $createOrder->handle($request->user(), $product, $data, $shippingQuote);
        } catch (OrderUnavailableException $exception) {
            return to_route('catalog.index')->with('error', $exception->getMessage());
        }

        $request->session()->forget($this->sessionKey($product));

        return to_route('orders.show', $order)->with('success', 'Pesanan berhasil dibuat.');
    }

    private function sessionKey(Product $product): string
    {
        return 'checkout.product.'.$product->id;
    }
}
