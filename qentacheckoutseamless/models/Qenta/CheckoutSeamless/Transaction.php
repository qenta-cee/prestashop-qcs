<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessTransaction extends ObjectModel
{
    public $id_tx;

    public $id_parent;

    public $id_order;

    public $id_cart;

    public $ordernumber;

    public $paymentname;

    public $paymentmethod;

    public $paymentnumber;

    public $creditnumber;

    public $paymentstate;

    public $gatewayreference;

    public $amount;

    public $currency;

    public $message;

    public $request;

    public $response;

    public $status;

    public $created;

    public $modified;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'qenta_checkout_seamless_tx',
        'primary' => 'id_tx',
        'fields' => array(
            'id_parent' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'ordernumber' => array('type' => self::TYPE_STRING),
            'paymentname' => array('type' => self::TYPE_STRING, 'required' => true),
            'paymentmethod' => array('type' => self::TYPE_STRING, 'required' => true),
            'paymentstate' => array('type' => self::TYPE_STRING, 'required' => true),
            'paymentnumber' => array('type' => self::TYPE_INT),
            'creditnumber' => array('type' => self::TYPE_INT),
            'gatewayreference' => array('type' => self::TYPE_STRING),
            'amount' => array('type' => self::TYPE_FLOAT, 'required' => true),
            'currency' => array('type' => self::TYPE_STRING, 'required' => true),
            'message' => array('type' => self::TYPE_STRING),
            'request' => array('type' => self::TYPE_STRING),
            'response' => array('type' => self::TYPE_STRING),
            'status' => array('type' => self::TYPE_STRING, 'required' => true),
            'created' => array('type' => self::TYPE_DATE, 'required' => true),
            'modified' => array('type' => self::TYPE_DATE),
        ),
    );

    /**
     * @param $id_order
     * @param $id_cart
     * @param $amount
     * @param $currency
     * @param $paymentname
     * @param $paymentmethod
     *
     * @return int
     * @throws PrestaShopDatabaseException
     */
    public function create($id_order, $id_cart, $amount, $currency, $paymentname, $paymentmethod)
    {
        $db = Db::getInstance();

        $db->insert('qenta_checkout_seamless_tx', array(
            'id_order' => $id_order === null ? 'NULL' : (int)$id_order,
            'id_cart' => (int)$id_cart,
            'paymentname' => pSQL($paymentname),
            'paymentmethod' => pSQL($paymentmethod),
            'paymentstate' => pSQL('CREATED'),
            'amount' => (float)$amount,
            'currency' => pSQL($currency),
            'created' => 'NOW()'
        ));

        if ($db->getNumberError() > 0) {
            throw new \PrestaShopDatabaseException($db->getMsgError());
        }

        return $db->Insert_ID();
    }

    /**
     * @param $id_tx
     * @param array $data
     *
     * @return int
     * @throws PrestaShopDatabaseException
     */
    public function updateTransaction($id_tx, $data)
    {
        $db = Db::getInstance();

        $values = '';

        foreach ($data as $f => $v) {
            if (Tools::strlen($values)) {
                $values .= ',';
            }
            $values .= sprintf("`%s`='%s'", $f, $db->_escape($v));
        }

        $query = 'UPDATE `' . _DB_PREFIX_ . 'qenta_checkout_seamless_tx` SET ' . $values . ' WHERE  `id_tx`=' .
            (int)$id_tx;

        if (!$db->execute($query)) {
            throw new \PrestaShopDatabaseException($db->getMsgError());
        }

        return $db->Affected_Rows();
    }

    /**
     * get transaction from database
     * @param $id_tx
     *
     * @return array|bool|null|object
     */
    public function get($id_tx)
    {
        $query = new DbQuery();
        $query->from('qenta_checkout_seamless_tx')->where('id_tx = ' . (int)$id_tx);

        return Db::getInstance()->getRow($query);
    }
}
