<?php

/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 */

class QentaCheckoutSeamlessBackModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * return from payment
     * @throws Exception
     */
    public function display()
    {
        echo $this->module->back();
    }
    // public function postProcess()
    // {
    //     $this->module = Module::getInstanceByName('qentacheckoutseamless');
    //     echo $this->module->back();
    // }
}
