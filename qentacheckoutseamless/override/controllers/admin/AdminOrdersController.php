<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class AdminOrdersController extends AdminOrdersControllerCore
{
    public function __construct()
    {
        parent::__construct();
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'qenta_checkout_seamless_tx` qcst
        ON (qcst.`id_order` = a.`id_order`)';
        $this->_select .= ', qcst.ordernumber as qcs_order_no, qcst.orderreference as qcs_order_ref';
        $this->fields_list['qcs_order_no'] = array(
            'title' => $this->l('QNT Order No.'),
            'align' => 'text-center'
        );
        $this->fields_list['qcs_order_ref'] = array(
            'title' => $this->l('QNT Order Ref.'),
            'align' => 'text-center'
        );
    }
}
