<?php

namespace Webkul\FleetShipping\Carriers;

use Webkul\Shipping\Carriers\AbstractShipping;
use Webkul\Checkout\Models\CartShippingRate;
use Webkul\Checkout\Facades\Cart;
use Webkul\FleetShipping\Services\FleetApiClient;
use Webkul\FleetShipping\Repositories\FleetQuoteRepository;

/**
 * Método de envio Fleet.ao.
 *
 * FASE 1 (atual): cotação por cidade — o formulário de endereço de entrega
 * ainda não captura lat/lng do cliente, então o `destination` é enviado
 * sem `coordinates`, e a Fleet resolve pelo centróide do município.
 * A origem (armazém) SEMPRE envia coordinates, vindas da config do admin.
 *
 * FASE 2 (futura, fora do escopo actual): capturar lat/lng do cliente via
 * mapa no checkout para melhorar a precisão do preço/ETA.
 */
class Fleet extends AbstractShipping
{
    protected $code = 'fleet';

    protected $method = 'fleet_fleet';

    public function calculate()
    {
        if (! $this->isAvailable()) {
            return false;
        }

        $cart    = Cart::getCart();
        $address = $cart->shipping_address;

        if (! $address || ! $address->city) {
            return false;
        }

        $origin = [
            'city'         => core()->getConfigData('sales.carriers.fleet.origin_city'),
            'address_line' => core()->getConfigData('sales.carriers.fleet.origin_address_line'),
            'coordinates'  => [
                'lat' => (float) core()->getConfigData('sales.carriers.fleet.origin_lat'),
                'lng' => (float) core()->getConfigData('sales.carriers.fleet.origin_lng'),
            ],
        ];

        $destination = [
            'city'         => $address->city,
            'address_line' => trim($address->address1[0] ?? ''),
            // sem 'coordinates' na Fase 1 — ver nota acima.
        ];

        $parcel = [
            'type'         => core()->getConfigData('sales.carriers.fleet.default_parcel_type') ?? 'small_package',
            'description'  => 'Encomenda Bern Okuleka',
            'weight_grams' => max(1, (int) ($cart->total_weight * 1000)),
        ];

        $quote = app(FleetApiClient::class)->quote($origin, $destination, $parcel);

        if (! $quote) {
            // Fora de zona, erro de validação, etc. — o método simplesmente
            // não aparece como opção no checkout, sem quebrar o fluxo.
            return false;
        }

        app(FleetQuoteRepository::class)->storeForCart($cart->id, $quote, $origin, $destination, $parcel);

        $rate = new CartShippingRate;
        $rate->carrier            = $this->getCode();
        $rate->carrier_title      = $this->getConfigData('title');
        $rate->method             = $this->getMethod();
        $rate->method_title       = $this->getConfigData('title');
        $rate->method_description = $this->getConfigData('description');
        $rate->price              = $quote['fee_aoa'];
        $rate->base_price         = $quote['fee_aoa'];

        return $rate;
    }
}
