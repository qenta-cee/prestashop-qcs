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

class WirecardCheckoutSeamlessBackend
{
    /** @var  WirecardCEECheckoutSeamless */
    protected $module;

    protected $context;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
    }

    /**
     * return client for sending backend operations
     *
     * @return \WirecardCEEQMoreBackendClient
     */
    public function getClient()
    {
        $cfg = $this->module->getConfigArray();
        $cfg['PASSWORD'] = $this->module->getConfigValue('basicdata', 'backendpw');

        return new \WirecardCEE_QMore_BackendClient($cfg);
    }


    /**
     * check if toolkit is available for backend operations
     *
     * @return bool
     */
    public function isAvailable()
    {
        return Tools::strlen($this->module->getConfigValue('basicdata', 'backendpw')) > 0;
    }

    /**
     * return order details from backend
     *
     * @param $orderNumber
     *
     * @return \WirecardCEEQMoreResponseBackendGetOrderDetails
     */
    public function getOrderDetails($orderNumber)
    {
        $client = $this->getClient();

        $ret = $client->getOrderDetails($orderNumber);
        if ($ret->hasFailed()) {
            $msg = implode(',', array_map(function ($e) {
                /** @var \WirecardCEE_QMore_Error $e */
                return $e->getConsumerMessage();
            }, $ret->getErrors()));
            $this->module->log(__METHOD__ . ':' . $msg);
        }

        return $ret;
    }
}
