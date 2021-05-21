<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessPaymentInvoiceB2B extends QentaCheckoutSeamlessPaymentInvoice
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::INVOICE;

    protected $forceSendAdditionalData = true;

    protected $isB2B = true;

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    protected function isAvailableInvoice($cart)
    {
        // prestashops B2B mode must be enabled, otherwise company specific input fields are not available
        if (!Configuration::get('PS_B2B_ENABLE')) {
            $this->module->log(__METHOD__ . ':please enable B2B mode in the customers preferences');
            return false;
        }

        /** @var AddressCore $billingAddress */
        $billingAddress = new Address($cart->id_address_invoice);

        /** @var AddressCore $shippingAddress */
        $shippingAddress = new Address($cart->id_address_delivery);

        if ($this->getBillingShippingAddressSame() && $billingAddress->id != $shippingAddress->id) {
            $fields = array(
                'country',
                'company',
                'firstname',
                'lastname',
                'address1',
                'address2',
                'postcode',
                'city'
            );
            foreach ($fields as $f) {
                if ($billingAddress->$f != $shippingAddress->$f) {
                    return false;
                }
            }
        }

        /** @var CurrencyCore $currency */
        $currency = new Currency($cart->id_currency);
        if (!in_array($currency->iso_code, $this->getAllowedCurrencies())) {
            return false;
        }

        if (count($this->getAllowedShippingCountries())) {
            $c = new Country($shippingAddress->id_country);
            if (!in_array($c->iso_code, $this->getAllowedShippingCountries())) {
                return false;
            }
        }

        if (!Tools::strlen($billingAddress->company)) {
            return false;
        }

        return true;
    }
}
