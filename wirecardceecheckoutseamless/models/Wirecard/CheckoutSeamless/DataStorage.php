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

class WirecardCheckoutSeamlessDataStorage
{
    /** @var  WirecardCEECheckoutSeamless */
    protected $module;

    protected $link;

    public function __construct($module)
    {
        $this->module = $module;
        $this->link = new WirecardCheckoutSeamlessLink;
    }

    /**
     * @param Cart $cart
     *
     * @return WirecardCEEQMoreDataStorageResponseInitiation
     */
    public function init(Cart $cart)
    {
        $dataStorageInit = new \WirecardCEE_QMore_DataStorageClient($this->module->getConfigArray());

        $returnUrl = $this->module->getDataStorageReturnUrl();

        $dataStorageInit->setReturnUrl($returnUrl);
        $dataStorageInit->setOrderIdent($cart->id);

        if ($this->module->getConfigValue('creditcardoptions', 'pci3_dss_saq_a_enable')) {
            $dataStorageInit->setJavascriptScriptVersion('pci3');
            if (Tools::strlen(trim($this->module->getConfigValue('creditcardoptions', 'iframe_css_url')))) {
                $dataStorageInit->setIframeCssUrl(
                    $this->link->getIframeCssUrl($this->module->getConfigValue('creditcardoptions', 'iframe_css_url'))
                );
            }

            $dataStorageInit->setCreditCardPanPlaceholder(
                $this->module->l($this->module->getConfigValue('creditcardoptions', 'pan_placeholder'))
            );
            $dataStorageInit->setCreditCardShowExpirationDatePlaceholder(
                $this->module->getConfigValue('creditcardoptions', 'displayexpirationdate_placeholder')
            );
            $dataStorageInit->setCreditCardCardholderNamePlaceholder(
                $this->module->l($this->module->getConfigValue('creditcardoptions', 'cardholder_placeholder'))
            );
            $dataStorageInit->setCreditCardCvcPlaceholder(
                $this->module->l($this->module->getConfigValue('creditcardoptions', 'cvc_placeholder'))
            );
            $dataStorageInit->setCreditCardShowIssueDatePlaceholder(
                $this->module->getConfigValue('creditcardoptions', 'displayissuedate_placeholder')
            );
            $dataStorageInit->setCreditCardCardIssueNumberPlaceholder(
                $this->module->l($this->module->getConfigValue('creditcardoptions', 'issuenumber_placeholder'))
            );

            $dataStorageInit->setCreditCardShowCardholderNameField(
                $this->module->getConfigValue('creditcardoptions', 'displaycardholder')
            );
            $dataStorageInit->setCreditCardShowCvcField(
                $this->module->getConfigValue('creditcardoptions', 'displaycvc')
            );
            $dataStorageInit->setCreditCardShowIssueDateField(
                $this->module->getConfigValue('creditcardoptions', 'displayissuedate')
            );
            $dataStorageInit->setCreditCardShowIssueNumberField(
                $this->module->getConfigValue('creditcardoptions', 'displayissuenumber')
            );
        }

        $this->module->log(__METHOD__ . ':' . print_r($dataStorageInit->getRequestData(), true));

        return $dataStorageInit->initiate();
    }
}
