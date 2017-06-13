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

class WirecardCheckoutSeamlessPaymentInvoice extends WirecardCheckoutSeamlessPayment
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::INVOICE;

    protected $forceSendAdditionalData = true;

    protected $isB2B = false;

    /**
     * Invoice is B2B
     *
     * @return bool
     */
    public function isB2B()
    {
        return $this->isB2B;
    }

    /**
     * whether payment method is available on checkoutpage
     * @param Cart $cart
     *
     * @return bool
     */
    public function isAvailable($cart)
    {
        if (!parent::isAvailable($cart)) {
            return false;
        }

        return $this->isAvailableInvoice($cart);
    }

    /**
     * whether payment method is available on checkoutpage, invoice specific
     * @param $cart
     *
     * @return bool
     */
    protected function isAvailableInvoice($cart)
    {
        if ($this->getProvider() == 'payolution') {
            return $this->isAvailablePayolution($cart);
        } elseif ($this->getProvider() == 'ratepay') {
            return $this->isAvailableRatePay($cart);
        } elseif ($this->getProvider() == 'wirecard') {
            return $this->isAvailableWirecard($cart);
        }

        return false;
    }

    /**
     * return provider
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->getConfigValue('invoice_provider');
    }

    /**
     * whether sending of basket is forced
     * @return bool
     */
    public function forceSendingBasket()
    {
        return $this->getProvider() == 'ratepay' || $this->getProvider() == 'wirecard';
    }

    /**
     * return payolution merchant id
     *
     * @return string
     */
    public function getPayolutionMid()
    {
        return $this->getConfigValue('payolution_mid');
    }

    /**
     * whether consent must be acknowledged
     *
     * @return bool
     */
    public function hasConsent()
    {
        return $this->getProvider() == 'payolution' && Configuration::get('WCS_OPTIONS_PAYOLUTION_TERMS') == true;
    }

    /**
     * autodeposit must not be used for Invoice and Installment to prevent the sending of an invoice or a
     * first installment to the consumer before he got the ordered product.
     * @return bool
     */
    protected function getAutoDeposit()
    {
        return false;
    }

    /**
     * allowed currencies for this payment method
     * @return array
     */
    protected function getAllowedCurrencies()
    {
        $val = $this->getConfigValue('invoice_currencies');
        if (!Tools::strlen($val)) {
            return array();
        }

        $currencies = Tools::jsonDecode($val);
        if (!is_array($currencies)) {
            return array();
        }

        return $currencies;
    }

    /**
     * allowed shipping countries for this payment method
     * @return array
     */
    protected function getAllowedBillingCountries()
    {
        $val = $this->getConfigValue('invoice_billing_countries');
        if (!Tools::strlen($val)) {
            return array();
        }

        $currencies = Tools::jsonDecode($val);
        if (!is_array($currencies)) {
            return array();
        }

        return $currencies;
    }

    /**
     * allowed shipping countries for this payment method
     * @return array
     */
    protected function getAllowedShippingCountries()
    {
        $val = $this->getConfigValue('invoice_shipping_countries');
        if (!Tools::strlen($val)) {
            return array();
        }

        $currencies = Tools::jsonDecode($val);
        if (!is_array($currencies)) {
            return array();
        }

        return $currencies;
    }

    /**
     * allowed billing shipping countries for this payment method
     * @return bool
     */
    protected function getBillingShippingAddressSame()
    {
        return (bool)$this->getConfigValue('invoice_billingshipping_same');
    }

    /**
     * min amount limit for this payment method
     * @return int
     */
    protected function getMinAmount()
    {
        return (int)$this->getConfigValue('invoice_amount_min');
    }

    /**
     * max amount limit for this payment method
     * @return int
     */
    protected function getMaxAmount()
    {
        return (int)$this->getConfigValue('invoice_amount_max');
    }
}
