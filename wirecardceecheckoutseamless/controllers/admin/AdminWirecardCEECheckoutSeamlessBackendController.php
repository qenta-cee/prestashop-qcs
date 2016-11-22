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

/**
 * @property WirecardCEECheckoutSeamless module
 */
class AdminWirecardCEECheckoutSeamlessBackendController extends ModuleAdminController
{

    /** @var WirecardCheckoutSeamlessBackend */
    protected $backendClient = null;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'wirecard_checkout_seamless_tx';
        $this->className = 'WirecardCheckoutSeamlessTransaction';
        $this->lang = false;
        $this->addRowAction('view');
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->identifier = 'id_tx';

        $this->module = Module::getInstanceByName('wirecardceecheckoutseamless');

        $this->_orderBy = 'id_tx';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $this->backendClient = new WirecardCheckoutSeamlessBackend($this->module);

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = array(
            'id_tx' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'amount' => array(
                'title' => $this->l('Amount'),
                'align' => 'text-right',
                'class' => 'fixed-width-xs',
                'type' => 'price',
            ),
            'currency' => array(
                'title' => $this->l('Currency'),
                'class' => 'fixed-width-xs',
                'align' => 'text-right',
            ),

            'ordernumber' => array(
                'title' => $this->l('Order number'),
                'class' => 'fixed-width-lg',
            ),
            'gatewayreference' => array(
                'title' => $this->l('Gateway reference number'),
                'class' => 'fixed-width-xxl',
            ),
            'paymentmethod' => array(
                'title' => $this->l('Payment method'),
                'class' => 'fixed-width-lg',
            ),
            'paymentstate' => array(
                'title' => $this->l('State'),
                'class' => 'fixed-width-xs',
            ),

        );

        parent::__construct();
        $this->tpl_folder = 'backend/';
    }

    /**
     * add js plugins
     */
    public function setMedia()
    {
        parent::setMedia();

        if ($this->display == 'view') {
            $this->addJS(_PS_JS_DIR_ . 'tools.js');
            $this->addJqueryPlugin('autocomplete');
        }
    }

    /**
     * render view
     */
    public function renderView()
    {
        if (!Validate::isLoadedObject($this->object)) {
            $this->errors[] = Tools::displayError('The transaction cannot be found within your database.');
        }

        /** @var WirecardCheckoutSeamlessTransaction $transaction */
        $transaction = $this->object;

        $order = $orderLink = null;
        if (Tools::strlen($transaction->id_order)) {
            $index = str_replace('AdminWirecardCEECheckoutSeamlessBackend', 'AdminOrders', self::$currentIndex);
            $order = new Order($transaction->id_order);
            $orderLink = $index . '&id_order=' . $order->id . '&vieworder&token='
                . Tools::getAdminTokenLite('AdminOrders');
        }

        $currency = new Currency(Currency::getIdByIsoCode($transaction->currency));
        $this->context->currency = $currency;

        $orderDetails = null;
        $operationsAllowed = array();
        if (Tools::strlen($transaction->ordernumber)) {
            $orderDetails = $this->backendClient->getOrderDetails($transaction->ordernumber);
            if (!$orderDetails->hasFailed()) {
                $operationsAllowed = $orderDetails->getOrder()->getOperationsAllowed();
            } else {
                $orderDetails = null;
            }
        }

        $response = array();
        if (Tools::strlen($transaction->response)) {
            $resp = Tools::jsonDecode($transaction->response, true);
            $blacklist = array(
                'amount',
                'currency',
                'orderNumber',
                'language',
                'paymentState',
                'psWcsTxId',
                'gatewayReferenceNumber',
                'paymentType'
            );

            foreach ($resp as $k => $v) {
                if (in_array($k, $blacklist)) {
                    continue;
                }

                $response[$k] = $v;
            }
        }


        $payments = array();
        if ($orderDetails !== null) {
            $payments = $orderDetails->getOrder()->getPayments()->getArray();
            usort(
                $payments,
                function ($a, $b) {
                    /**
                     * @var WirecardCEE_QMore_Response_Backend_Order_Payment $a
                     * @var WirecardCEE_QMore_Response_Backend_Order_Payment $b
                     */
                    return $a->getTimeCreated() > $b->getTimeCreated();
                }
            );
        }

        $credits = array();
        if ($orderDetails !== null) {
            $credits = $orderDetails->getOrder()->getCredits()->getArray();
            usort(
                $credits,
                function ($a, $b) {
                    /**
                     * @var WirecardCEE_QMore_Response_Backend_Order_Payment $a
                     * @var WirecardCEE_QMore_Response_Backend_Order_Payment $b
                     */
                    return $a->getTimeCreated() > $b->getTimeCreated();
                }
            );
        }

        // Smarty assign
        $this->tpl_view_vars = array(
            'current_index' => self::$currentIndex,
            'transaction' => $transaction,
            'order' => $order,
            'orderLink' => $orderLink,
            'operations' => $operationsAllowed,
            'response' => $response,
            'payments' => $payments,
            'cart' => new Cart($order->id_cart),
            'credits' => $credits
        );

        return parent::renderView();
    }

    /**
     * process post
     *
     * @return bool
     */
    public function postProcess()
    {
        $transaction = null;
        if (Tools::isSubmit('id_tx') && Tools::getValue('id_tx') > 0) {
            $transaction = new WirecardCheckoutSeamlessTransaction(Tools::getValue('id_tx'));
            if (!Validate::isLoadedObject($transaction)) {
                $this->errors[] = Tools::displayError('The transcation cannot be found within your database.');
            }
        }

        if ($transaction === null) {
            return parent::postProcess();
        }

        if (Tools::isSubmit('submitWcsBackendOp')) {
            $paymentnumber = null;
            if (Tools::isSubmit('paymentnumber') && Tools::getValue('paymentnumber') > 0) {
                if (!Validate::isInt(Tools::getValue('paymentnumber'))) {
                    $this->errors[] = Tools::displayError('Invalid paymentnumber given.');

                    return parent::postProcess();
                }

                $paymentnumber = (int)Tools::getValue('paymentnumber');
            }

            $creditnumber = null;
            if (Tools::isSubmit('creditnumber') && Tools::getValue('creditnumber') > 0) {
                if (!Validate::isInt(Tools::getValue('creditnumber'))) {
                    $this->errors[] = Tools::displayError('Invalid creditnumber given.');

                    return parent::postProcess();
                }

                $creditnumber = (int)Tools::getValue('creditnumber');
            }

            $amount = 0;
            if (Tools::isSubmit('amount') && Tools::strlen(Tools::getValue('amount'))) {
                $amount = strtr(Tools::getValue('amount'), ',', '.');

                if (!Validate::isFloat($amount)) {
                    $this->errors[] = Tools::displayError('Invalid amount given.');

                    return parent::postProcess();
                }

                $amount = (float)$amount;
            }

            $op = null;

            $id_order = Db::getInstance()->getValue(
                'SELECT id_order FROM ' . _DB_PREFIX_ . 'wirecard_checkout_seamless_tx 
            WHERE ordernumber = "' . pSQL($transaction->ordernumber) . '"'
            );

            switch (Tools::getValue('submitWcsBackendOp')) {
                case 'DEPOSIT':
                    $op = $this->backendClient->getClient()->deposit(
                        $transaction->ordernumber,
                        $amount,
                        $transaction->currency
                    );
                    break;

                case 'DEPOSITREVERSAL':
                    if (!$paymentnumber) {
                        $this->errors[] = Tools::displayError('Paymentnumber is mandatory.');

                        return parent::postProcess();
                    }
                    $op = $this->backendClient->getClient()->depositReversal($transaction->ordernumber, $paymentnumber);
                    break;

                case 'APPROVEREVERSAL':
                    $op = $this->backendClient->getClient()->approveReversal($transaction->ordernumber);
                    break;

                case 'REFUND':


                    $op = $this->backendClient->getClient()->refund(
                        $transaction->ordernumber,
                        $amount,
                        $transaction->currency
                    );


                    break;

                case 'REFUNDREVERSAL':
                    $op = $this->backendClient->getClient()->refundReversal($transaction->ordernumber, $creditnumber);
                    break;
            }

            $state = $this->backendClient->getOrderDetails($transaction->ordernumber)->getOrder()->getState();

            $msg = new Message();
            if (Tools::strlen($state) < 4) {
                $state = "NO ORDER";
            }
            $msg->message = "Wirecard CEE Order state: " . $state;
            $msg->id_order = $id_order;
            $msg->private = 1;
            $msg->add();

            if ($op !== null) {
                $this->module->log(
                    __METHOD__ . ':backend-op:' . Tools::getValue('submitWcsBackendOp') . ' 
                ordernumber:' . $transaction->ordernumber . ' amount:' . $amount
                );

                if ($op->hasFailed()) {
                    $errors = $op->getErrors();
                    $this->errors[] = $errors[0]->getConsumerMessage();
                    $this->module->log(__METHOD__ . ':backend-op: error: ' . print_r($op->getErrors(), true));
                } else {
                    $this->module->log(__METHOD__ . ':backend-op: response:' . print_r($op->getResponse(), true));
                }
            }
        }

        return parent::postProcess();
    }
}
