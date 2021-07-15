<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessPaymentExecutionModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {

        $this->ssl                 = true;
        $this->display_column_left = false;
        parent::initContent();

        $cart = $this->context->cart;

        $this->context->smarty->assign(array(
            'nbProducts'           => $cart->nbProducts(),
            'total'                => Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH)),
            'paymentName'          => Tools::getValue('paymentName'),
            'paymentType'          => Tools::getValue('paymentType'),
            'financialinstitution' => Tools::getValue('financialinstitution', ''),
            'birthdate'            => Tools::getValue('birthdate', '')
        ));

        $this->setTemplate('module:qentacheckoutseamless/views/templates/front/payment_execution.tpl');
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
