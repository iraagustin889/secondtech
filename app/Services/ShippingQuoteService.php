<?php

namespace App\Services;

use App\Exceptions\ShippingQuoteUnavailableException;
use App\Models\Product;
use App\Models\Shipping;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ShippingQuoteService
{
    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function destinations(string $search): array
    {
        try {
            $response = $this->request()->get('destination/domestic-destination', [
                'search' => $search,
                'limit' => 10,
                'offset' => 0,
            ]);
        } catch (ConnectionException) {
            throw new ShippingQuoteUnavailableException('Layanan pencarian lokasi sedang tidak dapat dihubungi.');
        }

        $data = $this->responseData($response->json(), 'Lokasi tujuan tidak ditemukan.');

        return collect($data)
            ->filter(fn (mixed $destination): bool => is_array($destination) && isset($destination['id']))
            ->map(fn (array $destination): array => [
                'id' => (int) $destination['id'],
                'name' => (string) ($destination['label']
                    ?? $destination['subdistrict_name']
                    ?? $destination['district_name']
                    ?? $destination['city_name']
                    ?? $destination['name']
                    ?? $destination['id']),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{key: string, courier: string, service: string, cost: int, estimate: string, origin_id: int, destination_id: int, destination_name: string}>
     */
    public function quotes(Product $product, int $destinationId, string $destinationName): array
    {
        $originId = $product->shipping_origin_id ?: config('services.rajaongkir.default_origin_id');

        if (blank($originId)) {
            throw new ShippingQuoteUnavailableException('Penjual belum melengkapi lokasi asal pengiriman produk ini.');
        }

        try {
            $response = $this->request()
                ->asForm()
                ->post('calculate/domestic-cost', [
                    'origin' => $originId,
                    'destination' => $destinationId,
                    'weight' => $product->weight,
                    'courier' => config('services.rajaongkir.couriers'),
                    'price' => 'lowest',
                ]);
        } catch (ConnectionException) {
            throw new ShippingQuoteUnavailableException('Layanan ongkir sedang tidak dapat dihubungi.');
        }

        $data = $this->responseData($response->json(), 'Ongkos kirim tidak dapat diperoleh saat ini.');

        return collect($data)
            ->filter(fn (mixed $quote): bool => is_array($quote) && isset($quote['cost'], $quote['code'], $quote['service']))
            ->map(function (array $quote) use ($originId, $destinationId, $destinationName): array {
                $courier = (string) $quote['code'];
                $service = (string) $quote['service'];
                $cost = (int) $quote['cost'];

                return [
                    'key' => hash('sha256', implode('|', [$courier, $service, $cost, $originId, $destinationId])),
                    'courier' => $courier,
                    'service' => $service,
                    'cost' => $cost,
                    'estimate' => (string) ($quote['etd'] ?? '-'),
                    'origin_id' => (int) $originId,
                    'destination_id' => $destinationId,
                    'destination_name' => $destinationName,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{status: string, delivered: bool, events: array<int, mixed>}
     */
    public function tracking(Shipping $shipping, string $recipientPhone): array
    {
        if (blank($shipping->resi) || blank($shipping->courier)) {
            throw new ShippingQuoteUnavailableException('Nomor resi belum tersedia.');
        }

        try {
            $response = $this->request()
                ->asForm()
                ->post('track/waybill', [
                    'awb' => $shipping->resi,
                    'courier' => $shipping->courier,
                    'last_phone_number' => substr(preg_replace('/\D/', '', $recipientPhone) ?? '', -5),
                ]);
        } catch (ConnectionException) {
            throw new ShippingQuoteUnavailableException('Layanan pelacakan sedang tidak dapat dihubungi.');
        }

        $data = $this->responseData($response->json(), 'Status pengiriman belum dapat diperoleh.');

        return [
            'status' => (string) data_get($data, 'delivery_status.status', data_get($data, 'summary.status', 'Dalam perjalanan')),
            'delivered' => in_array(strtoupper((string) data_get($data, 'delivery_status.status', '')), ['DELIVERED', 'DELIVERED_TO_RECIPIENT'], true),
            'events' => Arr::wrap(data_get($data, 'manifest', data_get($data, 'history', []))),
        ];
    }

    private function request(): PendingRequest
    {
        $key = config('services.rajaongkir.key');

        if (blank($key)) {
            throw new ShippingQuoteUnavailableException('API ongkir belum dikonfigurasi.');
        }

        return Http::baseUrl((string) config('services.rajaongkir.base_url'))
            ->acceptJson()
            ->withHeaders(['key' => $key])
            ->connectTimeout(3)
            ->timeout(7)
            ->retry([100, 300], throw: false);
    }

    /**
     * @return array<int, mixed>|array<string, mixed>
     */
    private function responseData(mixed $response, string $fallbackMessage): array
    {
        if (! is_array($response) || data_get($response, 'meta.status') !== 'success' || ! is_array($response['data'] ?? null)) {
            throw new ShippingQuoteUnavailableException((string) data_get($response, 'meta.message', $fallbackMessage));
        }

        return $response['data'];
    }
}
