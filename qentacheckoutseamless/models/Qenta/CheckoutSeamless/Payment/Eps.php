<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessPaymentEps extends QentaCheckoutSeamlessPayment
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::EPS;

    /**
     * list of financial institutions
     * @return array
     */
    public function getFinancialInstitutions()
    {
        $fis = array();
        $fis[] = array('value' => '', 'label' => $this->module->l('Choose your bank...'));
        foreach (WirecardCEE_QMore_PaymentType::getFinancialInstitutions($this->paymentMethod) as $k => $v) {
            $fis[] = array('value' => $k, 'label' => html_entity_decode($v));
        }

        return $fis;
    }
}
