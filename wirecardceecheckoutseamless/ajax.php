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

require_once(dirname(__FILE__) . '../../../config/config.inc.php');
require_once(dirname(__FILE__) . '../../../init.php');

switch (Tools::getValue('method')) {
    case 'getOrdersSelect2':
        $term = Tools::getValue('q');
        if (!Tools::strlen($term)) {
            $term = '';
        } else {
            $term = 'AND ordernumber LIKE "' . $term . '%"';
        }

        $page = Tools::getValue('page');
        $resultCount = 25;

        $offset = ($page - 1) * $resultCount;

        $limit = " LIMIT " . $offset . ", " . $resultCount;

        $sql = 'SELECT ordernumber,currency FROM ' . _DB_PREFIX_ .
            'wirecard_checkout_seamless_tx WHERE ordernumber IS NOT NULL AND paymentstate != "CREDIT"';
        $sql .= $term . $limit;

        $db = Db::getInstance();
        $data = $db->ExecuteS($sql);

        $result_data = array();

        foreach ($data as $row) {
            $result_data[] = array(
                "id" => $row["ordernumber"],
                "text" => $row["ordernumber"],
                "currency" => $row["currency"]
            );
        }

        $result = array(
            "results" => $result_data,
            "pagination" => array(
                "more" => false
            )
        );
        echo Tools::jsonEncode($result);
        break;
    default:
        exit;
}
exit;
