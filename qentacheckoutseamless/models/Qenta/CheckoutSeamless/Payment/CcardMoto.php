<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessPaymentCcardMoto extends QentaCheckoutSeamlessPaymentCcard
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::CCARD_MOTO;

    /**
     * whether payment method is available on checkoutpage
     * @param Cart $cart
     *
     * @return bool
     */
    public function isAvailable($cart)
    {
        $customer = new Customer($cart->id_customer);

        if ($customer->is_guest) {
            return false;
        }

        if (!in_array($this->getAllowedGroup(), $customer->getGroups())) {
            return false;
        }

        return parent::isEnabled();
    }

    /**
     * get allowed usergroup for ccard moto payments
     * @return string
     */
    public function getAllowedGroup()
    {
        return $this->module->getConfigValue('creditcardoptions', 'ccardmoto_usergroup');
    }
}
