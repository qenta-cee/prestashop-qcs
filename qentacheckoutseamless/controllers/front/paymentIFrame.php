<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessPaymentIFrameModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->ssl = true;
        $this->display_column_left = false;
        parent::initContent();

        $this->context->smarty->assign(
            array(
                'redirectUrl' => $this->context->cookie->qcsRedirectUrl,
                'windowName' => $this->module->getWindowName()
            )
        );
        unset($this->context->cookie->qcsRedirectUrl);

        $this->setTemplate('module:qentacheckoutseamless/views/templates/front/payment_iframe.tpl');
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->registerJavascript(
            'module-qentacheckoutseamless-scripts',
            'modules/qentacheckoutseamless/views/js/scripts.js',
            array(
                'priority' => 201,
                'attribute' => 'async',
            )
        );
    }
}
