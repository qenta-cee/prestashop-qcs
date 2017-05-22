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
class WirecardCEECheckoutSeamlessMasterpassPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @var WirecardCheckoutSeamlessPaymentMasterpass $masterpass
     */
    private $masterpass;
    private $wallet;

    public function initContent()
    {
        ini_set("display_errors", "on");
        error_reporting(E_ALL);
        parent::initContent();

        $this->masterpass = new WirecardCheckoutSeamlessPaymentMasterpass($this->module, null, null);
        $this->masterpass->set_merchant_id(sprintf(
            "%s-%s",
            $this->module->getConfigValue('basicdata', 'customer_id'),
            $this->module->getConfigValue('basicdata', 'shop_id')
        ));
        $this->masterpass->set_merchant_secret(md5($this->module->getConfigValue('basicdata', 'secret')));

        $this->wallet = $this->masterpass->read_wallet(null, $_GET['walletId']);

        $this->init_checks($this->wallet);

        $this->get_customer_and_redirect();
    }

    public function init_checks($wallet)
    {

        if (!isset($wallet->id) || empty($wallet->id)) {
            $this->masterpass->destroy();
            $this->errors[] = $this->module->l('Invalid wallet content. Your session probably expired. Please try again.');
            $this->redirectWithNotifications('index.php?controller=cart&action=show');
            die();
        } else if ($_GET["status"] == 'FAILURE') {
            $this->masterpass->destroy();
            $this->info[] = $this->module->l('You canceled the checkout process.');
            $this->redirectWithNotifications('index.php?controller=cart&action=show');
            die();
        }
    }

    /**
     * After the customer returned from the masterpass, this function tries to find the customer by his email,
     * if it fails to do so, it will create a user with corresponding details and hopefully redirect him to the
     * shipping step of the checkout process.
     */
    public function get_customer_and_redirect()
    {
        /* Try to find the user by email */
        if (Validate::isEmail($this->wallet->card->emailAddress)) {
            $customer = new Customer();
            $customer->getByEmail($this->wallet->card->emailAddress);
        }

        if (!Validate::isLoadedObject($customer)) {
            $customer = new Customer();
            $customer->email = $this->wallet->card->emailAddress;
            $customer->firstname = $this->wallet->card->firstName;
            $customer->lastname = $this->wallet->card->lastName;
            $customer->passwd = Tools::encrypt(Tools::passwdGen());
            $customer->add();
        }

        $addresses = $customer->getAddresses((int)Configuration::get('PS_LANG_DEFAULT'));
        foreach ($addresses as $address) {
            if ($address['alias'] == 'Masterpass') {
                $id_address = (int)$address['id_address'];
                break;
            }
        }

        /* create/update the wcs_masterpass address for the customer */
        $address = new Address(isset($id_address) ? (int)$id_address : 0);
        $address->id_customer = (int)$customer->id;
        $address->id_country = (int)Country::getByIso($this->wallet->shippingAddress->country);
        $address->alias = 'Masterpass';
        $address->lastname = $this->wallet->card->lastName;
        $address->firstname = $this->wallet->card->firstName;
        $address->address1 = $this->wallet->shippingAddress->addressLine1;
        if (Tools::strlen($this->wallet->shippingAddress->addressLine2))
            $address->address2 = $this->wallet->shippingAddress->addressLine2;
        $address->city = $this->wallet->shippingAddress->city;
        $address->postcode = $this->wallet->shippingAddress->postalCode;
        $address->save();

        /* update cart shipping and billing address */
        if($customer->isGuest()){
            $this->context->cart->id_guest = $this->context->cookie->id_guest;
        }

        foreach($this->context->cart->getProducts() as $product){
            $this->context->cart->setProductAddressDelivery($product['id_product'], $product['id_product_attribute'], $product['id_address_delivery'], $address->id);
        }

        //$this->context->cart->setProductAddressDelivery();
        $this->context->cart->id_customer = (int)$customer->id;
        $this->context->cart->id_address_delivery = (int)$address->id;
        $this->context->cart->id_address_invoice = (int)$address->id;
        $this->context->cart->update();

        /* simulate logged session */

        $this->context->cookie->id_customer = (int)$customer->id;
        $this->context->cookie->is_guest = $customer->isGuest();
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->passwd = $customer->passwd;
        $this->context->cookie->logged = 1;
        $this->context->cookie->email = $customer->email;
        $this->context->cookie->id_address_invoice = (int)$address->id;
        $this->context->cookie->id_address_delivery = (int)$address->id;

        if (Configuration::get('PS_ORDER_PROCESS_TYPE'))
            Tools::redirectLink($this->context->link->getPageLink('order-opc.php'));
        else
            Tools::redirectLink($this->context->link->getPageLink('order.php') . '?step=2');
        exit;
    }

    public function get_shipping_methods()
    {
        $cart = new Cart($this->context->cookie->id_cart);
        $country = new Country(Country::getByIso($this->wallet->shippingAddress->country, true));

        $carrier_list = $cart->getDeliveryOptionList($this->country);

        if (empty($carrier_list)) {
            $this->masterpass->destroy();
            $this->errors[] = $this->module->l('No shipping method available for country: ' . $country->name);
            $this->redirectWithNotifications('index.php?controller=cart&action=show');
            die();
        }

        $carrier_list = $carrier_list[key($carrier_list)];

        if (empty($carrier_list)) {
            $this->masterpass->destroy();
            $this->errors[] = $this->module->l('No shipping method available for country: ' . $country->name);
            $this->redirectWithNotifications('index.php?controller=cart&action=show');
            die();
        }

        $carriers_return = array();

        foreach ($carrier_list as $carrier) {
            $carrier = $carrier['carrier_list'];
            $carrier = $carrier[key($carrier)];

            $carriers_return[] = $carrier;
        }
        return $carriers_return;
    }
}
