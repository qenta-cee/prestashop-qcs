<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessBackend
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
