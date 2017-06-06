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

class WirecardCheckoutSeamlessOrderManagement
{
    /** @var  WirecardCEECheckoutSeamless */
    protected $module;

    /**
     * @var WirecardCheckoutSeamlessTransaction
     */
    protected $transaction;

    public function __construct($module)
    {
        $this->module = $module;
        $this->transaction = new WirecardCheckoutSeamlessTransaction();
    }

    /**
     * invoked during server2server request (confirm)
     *
     * @param WirecardCheckoutSeamlessPayment $paymentType
     * @param array $transactionData
     * @param WirecardCEE_Stdlib_Return_ReturnAbstract $return
     *
     * @return null
     * @throws Exception
     * @throws PrestaShopDatabaseException
     */
    public function processOrder($paymentType, $transactionData, $return)
    {
        $transactionIdField = $this->module->getConfigValue('options', 'transaction_id');
        $psOrderRef = $return->$transactionIdField;

        $txData = array(
            'paymentstate' => $return->getPaymentState(),
            'response' => Tools::jsonEncode($return->getReturned()),
        );

        $fraudDetected = false;
        // order creation after payment
        if (!$transactionData['id_order'] && (
                $return->getPaymentState() == WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS ||
                $return->getPaymentState() == WirecardCEE_QMore_ReturnFactory::STATE_PENDING
            )
        ) {
            $cart = new Cart((int)($transactionData['id_cart']));

            $id_order = $this->createOder($cart, $this->module->getAwaitingState());
            $transactionData['id_order'] = $txData['id_order'] = $id_order;

            $this->updatePaymentInformation($id_order, $paymentType);

            // detect cart modifications during checkout
            $cartHash = $this->module->computeCartHash($cart);
            $fraudDetected = $cartHash != $transactionData['carthash'];
        }

        $order = null;
        if ($transactionData['id_order']) {
            $order = new Order($transactionData['id_order']);
        }

        $this->module->log(
            __METHOD__ . ':using:' . $transactionIdField . ' as transactionId:' . $return->$transactionIdField
        );

        switch ($return->getPaymentState()) {
            case WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS:
                /** @var WirecardCEE_QMore_Return_Success $return */
                $orderState = _PS_OS_PAYMENT_;
                //create message with returned Parameters.
                if ($order !== null) {
                    $this->saveReturnedFields($order->id, $return);
                    $this->updatePaymentInformation($order->id, $paymentType, $psOrderRef);
                }
                $txData['ordernumber'] = $return->getOrderNumber();
                $txData['gatewayreference'] = $return->getGatewayReferenceNumber();
                break;

            case WirecardCEE_QMore_ReturnFactory::STATE_CANCEL:
                /** @var WirecardCEE_QMore_Return_Cancel $return */
                $orderState = _PS_OS_CANCELED_;
                $txData['message'] = $this->module->l('You have canceled the payment process!');
                break;

            case WirecardCEE_QMore_ReturnFactory::STATE_FAILURE:
                /** @var WirecardCEE_QMore_Return_Failure $return */

                if ($return->getNumberOfErrors() > 0) {
                    $m = $this->module;
                    $msg = implode(
                        ',',
                        array_map(function ($e) use ($m) {
                            /** @var \WirecardCEE_QMore_Error $e */
                            $m->log(__METHOD__ . ':msg:' . $e->getConsumerMessage());
                            return $e->getConsumerMessage();
                        }, $return->getErrors())
                    );

                    if (Tools::strlen($msg)) {
                        $txData['message'] = $msg;
                    }

                    $this->module->log(__METHOD__ . ':msg:' . $msg);
                }

                if ($order !== null) {
                    $this->saveReturnedFields($order->id, $return);
                }
                $orderState = _PS_OS_ERROR_;
                break;

            case WirecardCEE_QMore_ReturnFactory::STATE_PENDING:
                /** @var WirecardCEE_QMore_Return_Pending $return */
                if (Tools::strlen($return->getOrderNumber())) {
                    $txData['ordernumber'] = $return->getOrderNumber();
                }

                if ($order !== null) {
                    $this->saveReturnedFields($order->id, $return);
                }
                $orderState = $this->module->getAwaitingState();
                break;

            default:
                throw new Exception('Invalid uncaught paymentState. Should not happen.');
        }

        $this->transaction->updateTransaction($transactionData['id_tx'], $txData);

        if ($fraudDetected) {
            $orderState = $this->module->getFraudState();
        }

        if ($order !== null) {
            $this->setOrderState($order->id, $orderState);
        }
    }

    /**
     * invoked during rest-api server2server request (confirm)
     *
     * @param WirecardCheckoutSeamlessPayment $paymentType
     * @param array $transactionData
     * @param stdClass $paymentData
     *
     * @return null
     * @throws Exception
     */
    public function processMasterpassOrder($paymentType, $transactionData, $paymentData)
    {
        $transactionIdField = $this->module->getConfigValue('options', 'transaction_id');
        $returned_payment_state = $transactionData['paymentstate'];
        $fraudDetected = false;

        // order creation after payment
        if (!$transactionData['id_order'] && $returned_payment_state == WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS) {
            $cart = new Cart((int)($transactionData['id_cart']));

            $id_order = $this->createOder($cart, $this->module->getAwaitingState());
            $transactionData['id_order'] = $txData['id_order'] = $id_order;

            $this->updatePaymentInformation($id_order, $paymentType);

            // detect cart modifications during checkout
            $cartHash = $this->module->computeCartHash($cart);
            $fraudDetected = $cartHash != $transactionData['carthash'];
        }

        $order = null;
        if ($transactionData['id_order']) {
            $order = new Order($transactionData['id_order']);
        }

        $this->module->log(
            __METHOD__ . ':using:' . $transactionIdField . ' as transactionId:' . $paymentData->$transactionIdField
        );

        switch ($paymentData->processingState) {
            case WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS:
                $orderState = _PS_OS_PAYMENT_;
                break;

            case WirecardCEE_QMore_ReturnFactory::STATE_FAILURE:
                $orderState = _PS_OS_ERROR_;
                break;
            default:
                throw new Exception('Invalid uncaught paymentState. Should not happen.');
        }

        if ($order !== null) {
            $msg = new Message();
            $msg->message = trim(print_r($paymentData, true), ';');
            $msg->id_order = $transactionData['id_order'];
            $msg->private = 1;
            $msg->add();
        }

        if ($fraudDetected) {
            $orderState = $this->module->getFraudState();
        }

        if ($order !== null) {
            $this->setOrderState($order->id, $orderState);
        }
    }

    /**
     * save some return fields to an order message
     * @param $orderId
     * @param WirecardCEE_Stdlib_Return_ReturnAbstract $return
     *
     * @throws PrestaShopException
     */
    private function saveReturnedFields($orderId, \WirecardCEE_Stdlib_Return_ReturnAbstract $return)
    {
        $msg = new Message();
        $message = $this->printArray($return->getReturned(), array(
            'paymentState',
            'amount',
            'currency',
            'language',
            'responseFingerprint',
            'responseFingerprintOrder'
        ));

        if (!Validate::isCleanHtml($message)) {
            $message = $this->module->l(
                'Payment process results could not be saved reliably. Please check the payment in the Wirecard 
                Payment Center.'
            );
        }

        $msg->message = trim(print_r($message, true), ';');
        $msg->id_order = $orderId;
        $msg->private = 1;
        $msg->add();
    }

    /**
     * create message structure from given array without ignored fields
     * @param $array
     * @param $ignore
     * @return string
     */
    private function printArray($array, $ignore)
    {
        $ret = "";
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (in_array($key, $ignore)) {
                    continue;
                }
                if (is_array($value)) {
                    $ret .= $this->printArray($value);
                } else {
                    $ret .= '; ' . $key . ':' . $value;
                }
            }
        }
        return $ret;
    }

    /**
     * @param $orderId
     * @param WirecardCheckoutSeamlessPayment $paymentType
     * @param string $transactionId
     */
    public function updatePaymentInformation($orderId, $paymentType, $transactionId = '')
    {
        $order = new Order($orderId);
        $aOrderPayments = OrderPayment::getByOrderReference($order->reference);
        if (!empty($aOrderPayments)) {
            $aOrderPayments[0]->payment_method = $this->module->getDisplayName() . ' ' . $paymentType->getLabel();
            if ($transactionId != '') {
                $aOrderPayments[0]->transaction_id = $transactionId;
            }
            $aOrderPayments[0]->save();
        }
    }

    /**
     * set the order state
     * @param $id_order
     * @param $state
     */
    public function setOrderState($id_order, $state)
    {
        //Order::setCurrentState() does not save history. - it's not even used in presta itself.

        $order = new Order($id_order);
        // if a pending payment leads to an error, but the payment has been accepted manually via admin
        // dont set the order to an error state
        if ($order->current_state == _PS_OS_PAYMENT_ &&
            ($state == _PS_OS_ERROR_ || $state == $this->module->getAwaitingState())
        ) {
            return;
        }

        $history = new OrderHistory();
        $history->id_order = (int)$id_order;
        $history->changeIdOrderState((int)($state), $history->id_order, true);
        $history->addWithemail();

        if ($state == $this->module->getFraudState()) {
            $msg = new Message();
            $msg->message = $this->module->l('Fraud detected: Cart has been modified during checkout!');
            $msg->id_order = $id_order;
            $msg->private = 1;
            $msg->add();
        }
    }

    /**
     * @param Cart $cart
     * @param $awaitingState
     *
     * @return int
     * @throws PrestaShopException
     */
    public function createOder($cart, $awaitingState)
    {
        $this->module->validateOrder(
            $cart->id,
            $awaitingState,
            $cart->getOrderTotal(true),
            $this->module->getDisplayName(),
            null,
            array(),
            null,
            false,
            $cart->secure_key
        );

        return $this->module->currentOrder;
    }
}
