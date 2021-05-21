<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

class AdminQentaCheckoutSeamlessFundTransferController extends ModuleAdminController
{
    /** @var string */
    protected $display = 'add';

    /** @var WirecardCheckoutSeamlessBackend */
    protected $backendClient = null;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('qentacheckoutseamless');
        $this->bootstrap = true;
        $this->backendClient = new QentaCheckoutSeamlessBackend($this->module);
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
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

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
            '<a href="https://guides.qenta.com/doku.php%%s" target="_blank" class="docref">%s</a>',
            $this->l('See documentation')
        );

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Fund transfer for existing order'),
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'ajaxUrl'
                ),
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

        $this->fields_value = array(
            'ajaxUrl' => $this->context->link->getAdminLink(
                'AdminModules'
            ) . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name='
            . $this->module->name
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

            if (!Tools::strlen(Tools::getValue("sourceOrderNumber"))) {
                $this->errors[] = Tools::displayError('Please enter an order number');
            }

            if (!Tools::strlen(Tools::getValue("orderDescription"))) {
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
        $module = Module::getInstanceByName('qentacheckoutseamless');
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

                    Db::getInstance()->insert(
                        'qenta_checkout_seamless_tx',
                        array(
                            'id_order' => (int)$existingOrderDetails['id_order'],
                            'id_cart' => (int)$existingOrderDetails['id_cart'],
                            'carthash' => pSQL($existingOrderDetails['carthash']),
                            'ordernumber' => (Tools::strlen(
                                Tools::getValue('orderNumber')
                            )
                                ? pSQL(Tools::getValue('orderNumber'))
                                : pSQL($ret->getCreditNumber())),
                            'creditnumber' => (int)$ret->getCreditNumber(),
                            'orderreference' => pSQL(
                                $existingOrderDetails['orderreference']
                            ),
                            'paymentname' => pSQL($existingOrderDetails['paymentname']),
                            'paymentmethod' => pSQL(
                                $existingOrderDetails['paymentmethod']
                            ),
                            'paymentstate' => 'CREDIT',
                            'amount' => -(float)Tools::getValue('amount'),
                            'currency' => pSQL(Tools::getValue('currency')),
                            'request' => pSQL(
                                Tools::jsonEncode($requestData)
                            ),
                            'response' => pSQL(
                                Tools::jsonEncode($ret->getResponse())
                            ),
                            'status' => 'ok',
                            'created' => 'NOW()'
                        )
                    );
                }
            }
        }

        return $ret;
    }

    private function getExistingOrderDetails($ordernumber)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'qenta_checkout_seamless_tx WHERE ordernumber = "' . pSQL(
                $ordernumber
            ) . '"'
        );
    }
}
