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
class WirecardCheckoutSeamlessPaymentMasterpass extends WirecardCheckoutSeamlessPayment
{
    protected $paymentMethod = \WirecardCEE_Stdlib_PaymentTypeAbstract::MASTERPASS;
    protected $merchant_id = null;
    protected $merchant_secret = null;
    protected $wallet = null;
    protected $cart = null;

    /**
     * get wallet
     *
     * @return null|object
     */
    public function get_wallet()
    {
        if (!isset($this->wallet->id)) {
            $this->module->log('Masterpass wallet object is invalid.', 2, null);
            $this->wallet->id = null;
        }
        /*
         * @property id - wallet id
         * @property created
         * @property originUrl
         * @property basket
         */
        return $this->wallet;
    }

    /**
     * set merchant id
     *
     * @param $merchant_id
     */
    public function set_merchant_id($merchant_id)
    {
        $this->merchant_id = $merchant_id;
    }

    /**
     * set merchant secret
     *
     * @param $merchant_secret
     */
    public function set_merchant_secret($merchant_secret)
    {
        $this->merchant_secret = $merchant_secret;
    }

    /**
     * create masterpass wallet id
     *
     * @return false|object;
     */
    function create_wallet()
    {
        $wallet_process = curl_init(sprintf('https://checkout.wirecard.com/masterpass/merchants/%s/wallets', $this->merchant_id));
        curl_setopt($wallet_process, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            "Authorization: Bearer {$this->get_oauth_token()}"));
        curl_setopt($wallet_process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($wallet_process, CURLOPT_POST, 1);

        $basket_data = array(
            'originUrl' => $this->module->getContext()->link->getPageLink('order'),
            'basket' => $this->cart
        );
        curl_setopt($wallet_process, CURLOPT_POSTFIELDS, json_encode($basket_data));

        if (($wallet_return = curl_exec($wallet_process)) === false) {
            return false;
        }

        $this->wallet = json_decode($wallet_return);
        $cookie = $this->module->getContext()->cookie->__set('wcsWalletId', $this->wallet->id);

        return $this->wallet;
    }

    /**
     * get oauth token
     *
     * @return bool|mixed
     */
    public function get_oauth_token()
    {
        $cookie = $this->module->getContext()->cookie;
        $merchant_basic_auth = base64_encode($this->merchant_id . ':' . $this->merchant_secret);
        if ($cookie->wcs_oauth_token && $cookie->wcs_oauth_expires && $cookie->wcs_oauth_expires > time()) {
            return $cookie->wcs_oauth_token;
        } else if ($this->merchant_id === null) {
            $this->module->log($this->module->l('WirecardCeeCheckoutSeamless: No masterpasss merchant id provided. Cannot start oauth.'), 3, null);
            return false;
        }

        $auth_process = curl_init('https://checkout.wirecard.com/oauth/token');
        curl_setopt($auth_process, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', "Authorization: Basic $merchant_basic_auth"));
        curl_setopt($auth_process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($auth_process, CURLOPT_POST, 1);
        curl_setopt($auth_process, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        if (($auth_return = curl_exec($auth_process)) === false) {
            return false;
        }

        $return_code = curl_getinfo($auth_process, CURLINFO_RESPONSE_CODE);

        if ($return_code !== 200) {
            return false;
        }
        $auth_return = json_decode($auth_return);

        $cookie->wcs_oauth_token = $auth_return->access_token;
        $cookie->wcs_oauth_expires = time() + $auth_return->expires_in;

        return $cookie->wcs_oauth_token;
    }

    /**
     * @param Cart $cart
     */
    public function set_cart(Cart $cart)
    {

        $currency = (new Currency($cart->id_currency))->iso_code;

        $this->cart = array(
            "totalAmount" => array(
                "amount" => round($cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), 2),
                "currency" => $currency
            )
        );

        if ($this->getConfigValue('options', 'send_basketinformation')) {
            $this->cart['items'] = array();
            foreach ($cart->getProducts() as $product) {
                $this->cart['items'][] = array(
                    "articleNumber" => $product['reference'],
                    "name" => Tools::substr($product['name'], 0, 127),
                    "description" => Tools::substr(strip_tags($product['description_short']), 0, 127),
                    "quantity" => $product['cart_quantity'],
                    "unitGrossAmount" => array(
                        "amount" => number_format($product['price_wt'], 2, '.', ''),
                        "currency" => $currency
                    ),
                    "unitNetAmount" => array(
                        "amount" => number_format($product['price'], 2, '.', ''),
                        "currency" => $currency
                    ),
                    "unitTaxAmount" => array(
                        "amount" => number_format($product['price_wt'] - $product['price'], 2, '.', ''),
                        "currency" => $currency
                    ),
                    "unitTaxRate" => $product['rate'],
                    "imageUrl" => $this->module->getContext()->link->getImageLink($product['link_rewrite'], $product['id_image'])
                );
            }
        }
    }

    /**
     * @param $merchant_id
     * @param $wallet_id
     * @return false|object
     */
    public function read_wallet($merchant_id = null, $wallet_id = null, $oauth_token = null)
    {
        if ($merchant_id === null) {
            $merchant_id = $this->merchant_id;
        }
        if ($wallet_id === null) {
            $wallet_id = $this->wallet->id;
        }
        if ($oauth_token === null) {
            $oauth_token = $this->get_oauth_token();
        }

        if (!$oauth_token) {
            return false;
        }

        $read_wallet_process = curl_init(sprintf('https://checkout.wirecard.com/masterpass/merchants/%s/wallets/%s', $merchant_id, $wallet_id));
        curl_setopt($read_wallet_process, CURLOPT_HTTPHEADER, array("Authorization: Bearer $oauth_token"));
        curl_setopt($read_wallet_process, CURLOPT_RETURNTRANSFER, 1);

        if (($read_wallet_return = curl_exec($read_wallet_process)) === false) {
            return false;
        }

        return json_decode($read_wallet_return);
    }

    /**
     * destroy oauth session
     */
    public function destroy()
    {
        unset($this->module->getContext()->cookie->wcsWalletId);
        $this->wcs_oauth_token = null;
        $this->wcs_oauth_expires = null;
    }

    /**
     * initiate the payment
     *
     * @return false|object
     *
     */
    public function pay()
    {
        /**
         * unset the var in cookie sometimes here
         *
         * $this->module->getContext()->cookie->__unset('wcsWalletId');
         */
        if (!isset($this->module->getContext()->cookie->wcsWalletId)) {
            $this->module->log('Masterpass wallet id invalid.', 3, null);
            return false;
        }

        $cart = new Cart($this->module->getContext()->cart->id);
        $transaction = new WirecardCheckoutSeamlessTransaction();
        $order_management = new WirecardCheckoutSeamlessOrderManagement($this->module);

        $amount = round($cart->getOrderTotal(), 2);

        $current_currency = new Currency($cart->id_currency);

        if ($this->module->getConfigValue('options', 'order_creation') == 'before') {
            $id_order = $order_management->createOder($cart, $this->module->getAwaitingState());
            $order_management->updatePaymentInformation($id_order, $this);

        } else {
            $id_order = null;
        }

        $id_tx = $transaction->create(
            $id_order,
            $this->module->getContext()->cart->id,
            $amount,
            $current_currency->iso_code,
            $this->getName(),
            $this->getMethod()
        );

        $pay_process = curl_init(sprintf('https://checkout.wirecard.com/masterpass/merchants/%s/wallets/%s/payment', $this->merchant_id, $this->module->getContext()->cookie->wcsWalletId));
        curl_setopt($pay_process, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Authorization: Bearer {$this->get_oauth_token()}"));
        curl_setopt($pay_process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($pay_process, CURLOPT_POST, 1);

        $payment_data = array(
            'orderDescription' => 'CID: ' . $cart->id_customer . ' TID: ' . $id_tx,
            'totalAmount' => array(
                'amount' => $amount,
                'currency' => $current_currency->iso_code
            ),
            'customerStatement' => $this->module->getConfigValue('options', 'shopname'),
            'orderReference' => sprintf('%010d', $id_tx),
            'notificationUrl' => $this->module->getConfirmUrl(),
            'deposit' => $this->module->getConfigValue('options', 'autodeposit'),
            'useWalletConsumerData' => true
        );

        curl_setopt($pay_process, CURLOPT_POSTFIELDS, json_encode($payment_data));

        if (($pay_return = curl_exec($pay_process)) === false) {
            return false;
        }
        $pay_return = json_decode($pay_return);

        if (!isset($pay_return->orderNumber)) {
            if (!isset($pay_return->message))
                $pay_return->message = "Unknown error";
            $this->module->getContext()->cookie->wcsMessage = html_entity_decode($pay_return->message);

            $page = 'order';
            if (Configuration::get('PS_ORDER_PROCESS_TYPE')) {
                $page = 'order-opc';
            }

            $params = array();

            if ($id_order !== null) {
                $params = array(
                    'submitReorder' => true,
                    'id_order' => (int)$id_order
                );
            }

            $this->module->getContext()->smarty->assign(
                array(
                    'orderConfirmation' => $this->module->getContext()->link->getPageLink(
                        $page,
                        true,
                        $cart->id_lang,
                        $params
                    ),
                    'this_path' => _THEME_CSS_DIR_
                )
            );
            echo $this->module->getContext()->smarty->fetch('module:wirecardceecheckoutseamless/views/templates/hook/back.tpl');
            die();
        } else {
            if ($this->module->getConfigValue('options', 'order_creation') == 'after') {
                $id_order = $order_management->createOder($cart, $this->module->getAwaitingState());
            }
            $transaction->updateTransaction($id_tx, array(
                'paymentstate' => 'PENDING',
                'id_order' => $id_order
            ));
            $this->destroy();
            Tools::redirect($this->module->getReturnUrl($cart, $id_tx));
            die();
        }

    }

}
