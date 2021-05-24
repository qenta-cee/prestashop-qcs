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
            // print_r('class: ' . $class . '</br>');
            // print_r('paymentNamespace: ' . $paymentNamespace. '</br>');
            $classWithUnderscore .= Tools::substr($class, Tools::strlen($modelNamespace));
            // print_r('classWithUnderscore: ' . $classWithUnderscore. '</br>');


            if($classWithUnderscore === 'Qenta_CheckoutSeamless_PaymentSofortbanking') {
                $classWithUnderscore = 'Qenta_CheckoutSeamless_Payment_Sofortbanking';
            }
            if($classWithUnderscore === 'Qenta_CheckoutSeamless_PaymentSepa') {
                $classWithUnderscore = 'Qenta_CheckoutSeamless_Payment_Sepa';
            }
            if($classWithUnderscore === 'Qenta_CheckoutSeamless_PaymentTatrapay') {
                $classWithUnderscore = 'Qenta_CheckoutSeamless_Payment_Tatrapay';
            }
            if($classWithUnderscore === 'Qenta_CheckoutSeamless_PaymentTrustpay') {
                $classWithUnderscore = 'Qenta_CheckoutSeamless_Payment_Trustpay';
            }
            if($classWithUnderscore === 'Qenta_CheckoutSeamless_PaymentTrustly') {
                $classWithUnderscore = 'Qenta_CheckoutSeamless_Payment_Trustly';
            }
            if($classWithUnderscore === 'Qenta_CheckoutSeamless_PaymentVoucher') {
                $classWithUnderscore = 'Qenta_CheckoutSeamless_Payment_Voucher';
            }
            if($classWithUnderscore === 'Qenta_CheckoutSeamless_PaymentSkrillwallet') {
                $classWithUnderscore = 'Qenta_CheckoutSeamless_Payment_Skrillwallet';
            }
        }
        $class = $classWithUnderscore;
    }

    $file = str_replace(array('\\', '_'), '/', $class) . '.php';

    require_once $file;
}