<?php

/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 */

class QentaCheckoutSeamlessDataStorage
{
    /** @var  QentaCheckoutSeamless */
    protected $module;

    protected $link;

    public function __construct($module)
    {
        $this->module = $module;
        $this->link = new QentaCheckoutSeamlessLink;
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

        return $dataStorageInit->initiate();
    }
}
