<?php

namespace Webkul\Paypal\Payment;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Webkul\Payment\Payment\Payment;

abstract class Paypal extends Payment
{
    /**
     * PayPal web URL generic getter
     *
     * @param  array  $params
     * @return string
     */
    public function getPaypalUrl($params = [])
    {
        return sprintf('https://www.%spaypal.com/cgi-bin/webscr%s',
            $this->getConfigData('sandbox') ? 'sandbox.' : '',
            $params ? '?'.http_build_query($params) : ''
        );
    }

    /**
     * Add order item fields
     *
     * @param  array  $fields
     * @param  int  $i
     * @return void
     */
    protected function addLineItemsFields(&$fields, $i = 1)
    {
        $cartItems = $this->getCartItems();
        $currencyCode = $this->getCart()->cart_currency_code;

        foreach ($cartItems as $item) {
            foreach ($this->itemFieldsFormat as $modelField => $paypalField) {
                $fieldName = sprintf($paypalField, $i);
                $value = $item->{$modelField};

                // Convert price amounts for AOA→USD conversion
                if ($modelField === 'price') {
                    $value = $this->convertAmountToPayPalCurrency((float) $value, $currencyCode);
                }

                $fields[$fieldName] = $value;
            }

            $i++;
        }
    }

    /**
     * Add billing address fields
     *
     * @param  array  $fields
     * @return void
     */
    protected function addAddressFields(&$fields)
    {
        $cart = $this->getCart();

        $billingAddress = $cart->billing_address;

        $fields = array_merge($fields, [
            'city' => $billingAddress->city,
            'country' => $billingAddress->country,
            'email' => $billingAddress->email,
            'first_name' => $billingAddress->first_name,
            'last_name' => $billingAddress->last_name,
            'zip' => $billingAddress->postcode,
            'state' => $billingAddress->state,
            'address1' => $billingAddress->address,
            'address_override' => 1,
        ]);
    }

    /**
     * Checks if line items enabled or not
     *
     * @return bool
     */
    public function getIsLineItemsEnabled()
    {
        return true;
    }

    /**
     * Format a currency value according to paypal's api constraints
     *
     * @param  float|int  $long
     */
    public function formatCurrencyValue($number): float
    {
        return round((float) $number, 2);
    }

    /**
     * Format phone field according to paypal's api constraints
     *
     * Strips non-numbers characters like '+' or ' ' in
     * inputs like "+54 11 3323 2323"
     *
     * @param  mixed  $phone
     */
    public function formatPhone($phone): string
    {
        return preg_replace('/[^0-9]/', '', (string) $phone);
    }

    /**
     * Returns payment method image
     *
     * @return array
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/paypal.png', 'shop');
    }

    /**
     * Determine the currency code that should be sent to PayPal.
     */
    public function getPaypalCurrencyCode($currencyCode = null): string
    {
        $currencyCode = strtoupper((string) ($currencyCode ?: ($this->getCart()->cart_currency_code ?: core()->getCurrentCurrencyCode())));

        if ($this->shouldConvertToUsd($currencyCode)) {
            return 'USD';
        }

        return $currencyCode ?: 'USD';
    }

    /**
     * Check if the amount should be converted from AOA to USD before reaching PayPal.
     */
    public function shouldConvertToUsd($currencyCode = null): bool
    {
        $currencyCode = strtoupper((string) ($currencyCode ?: ($this->getCart()->cart_currency_code ?: core()->getCurrentCurrencyCode())));

        if ($currencyCode !== 'AOA') {
            return false;
        }

        $rate = (float) $this->getConfigData('kwanza_aoa_to_usd_rate');

        return $rate > 0;
    }

    /**
     * Convert the given amount to the currency that should be sent to PayPal.
     */
    public function convertAmountToPayPalCurrency($amount, $currencyCode = null): float
    {
        $currencyCode = strtoupper((string) ($currencyCode ?: ($this->getCart()->cart_currency_code ?: core()->getCurrentCurrencyCode())));

        if (! $this->shouldConvertToUsd($currencyCode)) {
            return $this->formatCurrencyValue((float) $amount);
        }

        $rate = (float) $this->getConfigData('kwanza_aoa_to_usd_rate');

        $converted = $this->formatCurrencyValue((float) $amount / ($rate > 0 ? $rate : 1));

        try {
            Log::info('PayPal AOA→USD conversion', [
                'original_amount' => (float) $amount,
                'original_currency' => $currencyCode,
                'converted_amount' => $converted,
                'rate' => $rate,
                'cart_id' => $this->getCart()->id ?? null,
                'payment_method' => $this->code ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            // do not break checkout if logging fails
        }

        return $converted;
    }
}
