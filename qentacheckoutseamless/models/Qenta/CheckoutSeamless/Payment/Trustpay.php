<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class QentaCheckoutSeamlessPaymentTrustpay extends QentaCheckoutSeamlessPayment
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::TRUSTPAY;

    /**
     * @var QentaCheckoutSeamlessBackend
     */
    protected $backend;

    public function __construct($module, $config, $transaction)
    {
        parent::__construct($module, $config, $transaction);
        $this->backend = new QentaCheckoutSeamlessBackend($module);
    }

    /**
     * return financial institutions from wirecard (backend operation)
     *
     * @return array
     */
    public function getFinancialInstitutions()
    {
        if (!$this->backend->isAvailable()) {
            return array();
        }

        $backendClient = $this->backend->getClient();

        try {
            $response = $backendClient->getFinancialInstitutions($this->paymentMethod);
        } catch (\Exception $e) {
            $this->module->log(__METHOD__ . ':' . $e->getMessage());

            return array();
        }

        if (!$response->hasFailed()) {
            $ret = $response->getFinancialInstitutions();

            uasort($ret, function ($a, $b) {
                return strcmp($a['id'], $b['id']);
            });

            $fis   = array();
            $fis[] = array('value' => '', 'label' => $this->module->l('Choose your bank...'));
            foreach ($ret as $fi) {
                $fis[] = array('value' => $fi['id'], 'label' => $fi['name']);
            }

            return $fis;
        } else {
            $this->module->log(__METHOD__ . ':' . print_r($response->getErrors(), true));

            return array();
        }
    }
}
