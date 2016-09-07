<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author    WirecardCEE
 * @copyright WirecardCEE
 * @license   GPLv2
 */

class WirecardCheckoutSeamlessPaymentInvoiceB2B extends WirecardCheckoutSeamlessPaymentInvoice
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
