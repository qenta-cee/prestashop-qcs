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
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'qenta_checkout_seamless_tx` wcst
        ON (wcst.`id_order` = a.`id_order`)';
        $this->_select .= ', wcst.ordernumber as wcs_order_no, wcst.orderreference as wcs_order_ref';
        $this->fields_list['wcs_order_no'] = array(
            'title' => $this->l('WD Order No.'),
            'align' => 'text-center'
        );
        $this->fields_list['wcs_order_ref'] = array(
            'title' => $this->l('WD Order Ref.'),
            'align' => 'text-center'
        );
    }
}
