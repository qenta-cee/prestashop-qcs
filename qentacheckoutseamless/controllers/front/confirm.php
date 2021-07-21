<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessConfirmModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * handle server2server request
     */
    public function display()
    {
        echo $this->module->confirmResponse();
    }
}
