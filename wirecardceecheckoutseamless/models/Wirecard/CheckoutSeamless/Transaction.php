<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author    WirecardCEE
 * @copyright WirecardCEE
 * @license   GPLv2
 */

class WirecardCheckoutSeamlessTransaction extends ObjectModel
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
        'table' => 'wirecard_checkout_seamless_tx',
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

        $db->insert('wirecard_checkout_seamless_tx', array(
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

        $query = 'UPDATE `' . _DB_PREFIX_ . 'wirecard_checkout_seamless_tx` SET ' . $values . ' WHERE  `id_tx`=' .
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
        $query->from('wirecard_checkout_seamless_tx')->where('id_tx = ' . (int)$id_tx);

        return Db::getInstance()->getRow($query);
    }
}
