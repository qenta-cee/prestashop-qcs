<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessPaymentCcard extends QentaCheckoutSeamlessPayment
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::CCARD;

    /**
     * whether payment method is available on checkoutpage
     * @param Cart $cart
     *
     * @return bool
     */
    public function isAvailable($cart)
    {
        return $this->isEnabled();
    }

    /**
     * return display cardholder option value
     * @return string
     */
    public function getDisplaycardholder()
    {
        return $this->module->getConfigValue('creditcardoptions', 'displaycardholder');
    }

    /**
     * return placeholdertext for pan input field
     * @return string
     */
    public function getPanPlaceholderText()
    {
        return $this->module->l($this->module->getConfigValue('creditcardoptions', 'pan_placeholder'));
    }

    /**
     * return placeholdertext for cardholder input field
     * @return string
     */
    public function getCardholderPlaceholderText()
    {
        return $this->module->l($this->module->getConfigValue('creditcardoptions', 'cardholder_placeholder'));
    }

    /**
     * return placeholdertext for cvc input field
     * @return string
     */
    public function getCvcPlaceholderText()
    {
        return $this->module->l($this->module->getConfigValue('creditcardoptions', 'cvc_placeholder'));
    }

    /**
     * return display issue date option value
     * @return string
     */
    public function getDisplayIssueDateField()
    {
        return $this->module->getConfigValue('creditcardoptions', 'displayissuedate');
    }

    /**
     * return display issue number option value
     * @return string
     */
    public function getDisplayIssueNumberField()
    {
        return $this->module->getConfigValue('creditcardoptions', 'displayissuenumber');
    }

    /**
     * return pci3 dss saq option value
     * @return string
     */
    public function getPci3DssSaqAEnable()
    {
        return $this->module->getConfigValue('creditcardoptions', 'pci3_dss_saq_a_enable');
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getMonths()
    {
        $months = array();
        for ($index = 1; $index <= 12; $index++) {
            $monthNum = ($index < 10) ? '0' . $index : $index;
            $months[$index] = $monthNum;
        }

        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getYears()
    {
        $years = array();
        $first = date("Y");

        for ($index = 0; $index <= 10; $index++) {
            $year = $first + $index;
            $years[$year] = $year;
        }

        return $years;
    }

    /**
     * years for issue date
     *
     * @return array
     */
    public function getIssueYears()
    {
        $years = array();
        $first = date("Y");

        for ($index = 5; $index >= 0; $index--) {
            $year = $first - $index;
            $years[$year] = $year;
        }

        return $years;
    }
}
