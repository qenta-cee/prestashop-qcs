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

class WirecardCheckoutSeamlessPaymentCcard extends WirecardCheckoutSeamlessPayment
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::CCARD;

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
