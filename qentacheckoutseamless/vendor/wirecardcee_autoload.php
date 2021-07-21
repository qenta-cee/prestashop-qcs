<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

spl_autoload_register('wirecardcee_autoload');

function wirecardcee_autoload($class)
{
    $namespaces = array('WirecardCEE', 'Qenta','React');
    $namespace = null;
    $modelNamespace = 'QentaCheckoutSeamless';
    $paymentNamespace = 'QentaCheckoutSeamlessPayment';
    $targetNamespace = 'Qenta_CheckoutSeamless_Payment';

    foreach ($namespaces as $ns) {

        if (strncmp($ns, $class, Tools::strlen($ns)) !== 0) {
            continue;
        } else {
            $namespace = $ns;
            break;
        }
    }
    if ($namespace === null) {
        return;
    }

    if (strcmp($class, $modelNamespace) > 0 && $namespace === 'Qenta') {
        $classWithUnderscore = 'Qenta_CheckoutSeamless_';

        if (
            (strcmp($paymentNamespace, Tools::substr($class, Tools::strlen($paymentNamespace))) >= 0)
            && ((Tools::substr($class, Tools::strlen($paymentNamespace))) != '')
        ) {
            $classWithUnderscore .= 'Payment_' . Tools::substr($class, Tools::strlen($paymentNamespace));
        } else {

            $classWithUnderscore .= Tools::substr($class, Tools::strlen($modelNamespace));

            if(Tools::strlen($class) > Tools::strlen($targetNamespace) && strpos($class, 'Payment') !== false) {
                $classWithUnderscore = str_replace($targetNamespace, $targetNamespace . '_', $classWithUnderscore);
            }
        }
        $class = $classWithUnderscore;
    }

    $file = str_replace(array('\\', '_'), '/', $class) . '.php';

    require_once $file;
}