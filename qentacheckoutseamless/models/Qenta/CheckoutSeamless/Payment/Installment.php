<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessPaymentInstallment extends QentaCheckoutSeamlessPayment
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::INSTALLMENT;

    protected $forceSendAdditionalData = true;

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

        if ($this->getProvider() == 'payolution') {
            return $this->isAvailablePayolution($cart);
        } elseif ($this->getProvider() == 'ratepay') {
            return $this->isAvailableRatePay($cart);
        } elseif ($this->getProvider() == 'qenta') {
            return $this->isAvailableQenta($cart);
        }

        return false;
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
        $val = $this->getConfigValue('installment_currencies');
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
        $val = $this->getConfigValue('installment_billing_countries');
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
        $val = $this->getConfigValue('installment_shipping_countries');
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
     * whether sending of basket is forced
     * @return bool
     */
    public function forceSendingBasket()
    {
        return $this->getProvider() == 'ratepay';
    }

    /**
     * allowed billing shipping countries for this payment method
     * @return bool
     */
    protected function getBillingShippingAddressSame()
    {
        return (bool)$this->getConfigValue('installment_billingshipping_same');
    }

    /**
     * min amount limit for this payment method
     * @return int
     */
    protected function getMinAmount()
    {
        return (int)$this->getConfigValue('installment_amount_min');
    }

    /**
     * max amount limit for this payment method
     * @return int
     */
    protected function getMaxAmount()
    {
        return (int)$this->getConfigValue('installment_amount_max');
    }

    /**
     * min basket size limit for this payment method
     * @return int
     */
    protected function getMinBasketSize()
    {
        return (int)$this->getConfigValue('installment_basketsize_min');
    }

    /**
     * max basket size limit for this payment method
     * @return int
     */
    protected function getMaxBasketSize()
    {
        return (int)$this->getConfigValue('installment_basketsize_max');
    }

    /**
     * return provider
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->getConfigValue('installment_provider');
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
        return $this->getProvider() == 'payolution' && Configuration::get('QCS_OPTIONS_PAYOLUTION_TERMS') == true;
    }

    /**
     * return min age
     *
     * @return int
     */
    public function getMinAge()
    {
        if ($this->getProvider() == 'payolution') {
            return 18;
        }

        return (int)$this->getConfigValue('installment_min_age');
    }

    /**
     * Invoice is B2B
     *
     * @return bool
     */
    public function isB2B()
    {
        return false;
    }
}
