<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessPaymentTrustly extends QentaCheckoutSeamlessPayment
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::TRUSTLY;
}
