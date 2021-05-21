<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

abstract class Controller extends ControllerCore
{
    public function __construct()
    {
        require_once _PS_MODULE_DIR_ . "qentacheckoutseamless" . DIRECTORY_SEPARATOR . "vendor"
            . DIRECTORY_SEPARATOR . "React" . DIRECTORY_SEPARATOR . "Promise" . DIRECTORY_SEPARATOR
            . "functions_include.php";
        require_once 'React/Promise/functions_include.php';

        parent::__construct();
    }
}
