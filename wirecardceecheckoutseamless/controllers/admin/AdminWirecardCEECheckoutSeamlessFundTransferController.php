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
class AdminWirecardCEECheckoutSeamlessFundTransferController extends ModuleAdminController
{
    /** @var string */
    protected $display = 'add';

    /** @var WirecardCheckoutSeamlessBackend */
    protected $backendClient = null;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('wirecardceecheckoutseamless');
        $this->bootstrap = true;
        $this->backendClient = new WirecardCheckoutSeamlessBackend($this->module);
        $this->tpl_form_vars['back_url'] = $this->context->link->getAdminLink('AdminModules') . '&configure='
            . $this->module->name . '&module_name=' . $this->module->name;

        parent::__construct();
    }

    /**
     * Set toolbar title
     *
     * @return void
     */
    public function initToolbarTitle()
    {
        parent::initToolbarTitle();

        switch ($this->display) {

            case 'add':
                $this->toolbar_title[] = $this->l('Fund transfer');
                $this->addMetaTitle($this->l('Fund transfer'));
                break;
        }
    }

    /**
     * add css/js files
     */
    public function setMedia()
    {
        parent::setMedia();

        $this->addJQueryPlugin('select2');
        $this->addJS($this->module->getPathUri() . 'views/js/admin/fundtransfer.js');
        $this->addCSS($this->module->getPathUri() . 'views/css/admin/styles.css');
    }

    /**
     * render form
     *
     * @return string
     */
    public function renderForm()
    {
        $fieldNoteFmt = sprintf(
            '<a href="https://guides.wirecard.at/doku.php%%s" target="_blank" class="docref">%s</a>',
            $this->l('See documentation')
        );

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Fund transfer for existing order'),
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Currency:'),
                    'desc' => sprintf($fieldNoteFmt, '/request_parameters#currency'),
                    'name' => 'currency',
                    'required' => true,
                    'options' => array(
                        'query' => Currency::getCurrenciesByIdShop($this->context->shop->id),
                        'id' => 'iso_code',
                        'name' => 'name'
                    ),
                    'class' => 'fixed-width-xs'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Amount:'),
                    'name' => 'amount',
                    'required' => true,
                    'class' => 'fixed-width-md',
                    'desc' => sprintf($fieldNoteFmt, '/request_parameters#amount')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Order description:'),
                    'name' => 'orderDescription',
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                    'desc' => sprintf($fieldNoteFmt, '/request_parameters#orderdescription')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Source Ordernumber:'),
                    'name' => 'sourceOrderNumber',
                    'class' => 'fixed-width-md',
                    'required' => true,
                    'desc' => sprintf($fieldNoteFmt, '/request_parameters#sourceordernumber')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Customer statement:'),
                    'name' => 'customerStatement',
                    'class' => 'fixed-width-xl',
                    'desc' => sprintf($fieldNoteFmt, '/request_parameters#customerstatement')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Credit number:'),
                    'name' => 'creditNumber',
                    'class' => 'fixed-width-md'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Order number:'),
                    'name' => 'orderNumber',
                    'class' => 'fixed-width-md',
                    'desc' => sprintf($fieldNoteFmt, '/request_parameters#ordernumber')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Order reference:'),
                    'name' => 'orderReference',
                    'class' => 'fixed-width-xl',
                    'desc' => sprintf($fieldNoteFmt, '/request_parameters#orderreference')
                )
            ),
            'submit' => array(
                'name' => 'transferFund',
                'title' => $this->l('Transfer'),
            )
        );

        return parent::renderForm();
    }

    /**
     * @see AdminController::initProcess()
     */
    public function initProcess()
    {
        parent::initProcess();

        $this->display = 'add';
        if (Tools::isSubmit('transferFund')) {

            $amount = strtr(Tools::getValue('amount'), ',', '.');
            if (!Validate::isFloat($amount)) {
                $this->errors[] = Tools::displayError('Please enter a valid amount');
            }

            if (!Tools::substr(Tools::getValue("sourceOrderNumber"))) {
                $this->errors[] = Tools::displayError('Please enter an order number');
            }

            if (!Tools::substr(Tools::getValue("orderDescription"))) {
                $this->errors[] = Tools::displayError('Please enter an order description');
            }

            if (!Tools::getValue('currency')) {
                $this->errors[] = Tools::displayError('Please select a valid currency');
            }

            if (!count($this->errors)) {
                $this->action = 'transferFund';
            }
        }
    }

    /**
     * execute fund transfer
     * @return bool|WirecardCEE_QMore_Response_Backend_TransferFund
     * @throws WirecardCEE_Stdlib_Exception_InvalidTypeException
     */
    public function processTransferFund()
    {
        $module = Module::getInstanceByName('wirecardceecheckoutseamless');
        $client = $this->backendClient->getClient();
        $client = $client->transferFund(\WirecardCEE_QMore_BackendClient::$TRANSFER_FUND_TYPE_EXISTING);

        if (Tools::strlen(Tools::getValue('orderNumber'))) {
            $client->setOrderNumber(Tools::getValue('orderNumber'));
        }

        if (Tools::strlen(Tools::getValue('orderReference'))) {
            $client->setOrderReference(Tools::getValue('orderReference'));
        }

        if (Tools::strlen(Tools::getValue('creditNumber'))) {
            $client->setCreditNumber(Tools::getValue('creditNumber'));
        }

        if (Tools::strlen(Tools::getValue('customerStatement'))) {
            $client->setCustomerStatement(Tools::getValue('customerStatement'));
        }

        $ret = false;

        //do not need a switch! always existing order!!!

        if (Tools::strlen(Tools::getValue('customerStatement'))) {
            $client->setCustomerStatement(Tools::getValue('customerStatement'));
        }

        $requestData = $client->getRequestData();
        $requestData['password'] = '******';
        $module->log(__METHOD__ . ': ' . Tools::jsonEncode($requestData));

        $ret = $client->send(
            Tools::getValue('amount'),
            Tools::getValue('currency'),
            Tools::getValue('orderDescription'),
            Tools::getValue('sourceOrderNumber')
        );

        $module->log(__METHOD__ . ': ' . Tools::jsonEncode($ret->getResponse()));

        if ($ret !== false) {
            if ($ret->hasFailed()) {
                foreach ($ret->getErrors() as $err) {
                    $this->errors[] = Tools::displayError(Tools::htmlentitiesDecodeUTF8($err->getConsumerMessage()));
                }
            } else {
                $this->confirmations[] = $this->l('Fund transfer submitted successfully');
                if (Tools::strlen($ret->getCreditNumber())) {
                    $this->confirmations[] = $this->l('Credit number: ' . $ret->getCreditNumber());
                    $existingOrderDetails = $this->getExistingOrderDetails(Tools::getValue('sourceOrderNumber'));

                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'wirecard_checkout_seamless_tx (
                        id_order, id_cart, carthash, ordernumber, creditnumber, orderreference,
                        paymentname, paymentmethod, paymentstate, amount, currency, request, response, status, created
                        ) VALUES (
                        ' . $existingOrderDetails['id_order'] . ', ' . $existingOrderDetails['id_cart'] . ',
                        "' . $existingOrderDetails['carthash'] . '",
                        "' . (Tools::strlen(Tools::getValue('orderNumber'))
                                ? Tools::getValue('orderNumber')
                                : $ret->getCreditNumber()) . '",
                        ' . $ret->getCreditNumber() . ', 
                        "' . $existingOrderDetails['orderreference'] . '",
                        "' . $existingOrderDetails['paymentname'] . '", 
                        "' . $existingOrderDetails['paymentmethod'] . '", 
                        "CREDIT",
                        -' . Tools::getValue('amount') . ', 
                        "' . Tools::getValue('currency') . '", 
                        "' . pSQL(Tools::jsonEncode($requestData)) . '",
                        "' . pSQL(Tools::jsonEncode($ret->getResponse())) . '", "ok",NOW())';
                    Db::getInstance()->execute($sql);
                }
            }
        }

        return $ret;
    }

    private function getExistingOrderDetails($ordernumber)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'wirecard_checkout_seamless_tx WHERE ordernumber = ' . $ordernumber
        );
    }
}