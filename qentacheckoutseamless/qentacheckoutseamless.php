<?php

/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class QentaCEECheckoutSeamless
 */
class QentaCheckoutSeamless extends PaymentModule
{
    const WINDOW_NAME = 'CheckoutSeamlessFrame';
    const QCS_OS_AWAITING = 'QCS_OS_AWAITING';
    const QCS_OS_FRAUD = 'QCS_OS_FRAUD';
    /**
     * set this to true, if this plugin is bundled with prestashop
     *
     * @var bool
     */
    protected $isCore = false;
    /**
     * predefined test/demo accounts
     *
     * @var array
     */
    protected $presets = array(
        'demo' => array(
            'customer_id' => 'D200001',
            'shop_id' => 'seamless',
            'secret' => 'B8AKTPWBRMNBV455FG6M2DANE99WU2',
            'backendpw' => 'jcv45z'
        ),
        'test' => array(
            'customer_id' => 'D200411',
            'shop_id' => 'seamless',
            'secret' => 'CHCSH7UGHVVX2P7EHDHSY4T2S4CGYK4QBE4M5YUUG2ND5BEZWNRZW5EJYVJQ',
            'backendpw' => '2g4f9q2m'
        ),
        'test3d' => array(
            'customer_id' => 'D200411',
            'shop_id' => 'seamless3D',
            'secret' => 'DP4TMTPQQWFJW34647RM798E9A5X7E8ATP462Z4VGZK53YEJ3JWXS98B9P4F',
            'backendpw' => '2g4f9q2m'
        )
    );

    // order states
    private $config = array();
    /**
     * admin config page/html
     *
     * @var string
     */
    private $html = '';
    /**
     * validation errors, when saving admin options
     *
     * @var array
     */
    private $postErrors = array();
    /** @var  QentaCheckoutSeamlessOrderManagement */
    private $orderManagement;
    /** @var  QentaCheckoutSeamlessTransaction */
    private $transaction;

    /**
     * QentaCEECheckoutSeamless constructor.
     */
    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }

        ini_set(
            'include_path',
            ini_get('include_path')
                . PATH_SEPARATOR . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor'
                . PATH_SEPARATOR . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'models'
        );

        require_once 'wirecardcee_autoload.php';

        $this->config = $this->config();
        $this->name = 'qentacheckoutseamless';
        $this->tab = 'payments_gateways';
        $this->version = '3.0.0';
        $this->author = 'Qenta';
        $this->controllers = array(
            'confirm',
            'payment',
            'paymentExecution',
            'paymentIFrame'
        );
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('QENTA Checkout Seamless');
        $this->description = $this->l('QENTA Checkout Seamless payment module');
        $this->confirmUninstall = $this->l('Are you sure you want to delete these details?');
    }

    /**
     * available config params
     *
     * @return array
     */
    protected function config()
    {
        return array(
            'basicdata' => array(
                'tab' => $this->l('Access data'),
                'fields' => array(
                    array(
                        'name' => 'configmode',
                        'label' => $this->l('Configuration'),
                        'type' => 'select',
                        'default' => 'production',
                        'required' => true,
                        'options' => 'getConfigurationModes',
                        'doc' => $this->l('For integration, select predefined configuration settings or \'Production\' for live systems'),
                    ),
                    array(
                        'name' => 'customer_id',
                        'label' => $this->l('Customer ID'),
                        'type' => 'text',
                        'default' => 'D200001',
                        'maxchar' => 7,
                        'required' => true,
                        'sanitize' => 'trim',
                        'doc' => $this->l('Customer number you received from QENTA (customerId, i.e. D2#####).'),
                        'docref' => 'https://guides.qenta.com/request_parameters#customerid',
                    ),
                    array(
                        'name' => 'shop_id',
                        'label' => $this->l('Shop ID'),
                        'type' => 'text',
                        'default' => 'seamless',
                        'maxchar' => 16,
                        'sanitize' => 'trim',
                        'doc' => $this->l('Shop identifier in case of more than one shop.'),
                        'docref' => 'https://guides.qenta.com/request_parameters#shopid',
                    ),
                    array(
                        'name' => 'secret',
                        'label' => $this->l('Secret'),
                        'type' => 'text',
                        'default' => 'B8AKTPWBRMNBV455FG6M2DANE99WU2',
                        'required' => true,
                        'sanitize' => 'trim',
                        'cssclass' => 'fixed-width-xxxl',
                        'doc' => $this->l('String which you received from Qenta for signing and validating data to prove their authenticity.'),
                        'docref' => 'https://guides.qenta.com/security:start#secret_and_fingerprint',
                    ),
                    array(
                        'name' => 'backendpw',
                        'label' => $this->l('Back-end password'),
                        'type' => 'text',
                        'default' => 'jcv45z',
                        'sanitize' => 'trim',
                        'doc' => $this->l('Password for back-end operations (Toolkit).'),
                        'docref' => 'https://guides.qenta.com/doku.php/back-end_operations:technical_qcs:start
                        #password',
                    )
                )
            ),
            'options' => array(
                'tab' => $this->l('General settings'),
                'fields' => array(
                    array(
                        'name' => 'order_creation',
                        'label' => $this->l('Create orders'),
                        'type' => 'select',
                        'options' => 'getOrderCreationOptions',
                        'default' => 'before',
                        'doc' => array(
                            $this->l('Selecting \'Always\', orders are created even if the payment process leads to failed payment.'),
                            $this->l('Selecting \'Only for successful payments\', orders are created if the payment process was successful.')
                        )
                    ),
                    array(
                        'name' => 'transaction_id',
                        'label' => $this->l('Transaction ID'),
                        'type' => 'select',
                        'options' => 'getTransactionIdOptions',
                        'default' => 'gatewayReferenceNumber',
                        'doc' => array(
                            $this->l('QENTA order number: Unique number defined by QENTA identifying the payment.'),
                            $this->l('Gateway reference number: Reference number defined by the processor or acquirer.')
                        )
                    ),
                    array(
                        'name' => 'shopname',
                        'label' => $this->l('Shop reference in posting text'),
                        'type' => 'text',
                        'maxchar' => 9,
                        'doc' => $this->l('Reference to your online shop on your consumer\'s bank statement, limited to 9 characters.')
                    ),
                    array(
                        'name' => 'send_shippingdata',
                        'label' => $this->l('Forward consumer shipping data'),
                        'default' => 1,
                        'type' => 'onoff',
                        'doc' => $this->l('Forwarding shipping data about your consumer to the respective financial service provider.')
                    ),
                    array(
                        'name' => 'send_billingdata',
                        'label' => $this->l('Forward consumer billing data'),
                        'default' => 1,
                        'type' => 'onoff',
                        'doc' => $this->l('Forwarding billing data about your consumer to the respective financial service provider.')
                    ),
                    array(
                        'name' => 'send_basketinformation',
                        'label' => $this->l('Forward basket data'),
                        'default' => 1,
                        'type' => 'onoff',
                        'doc' => $this->l('Forwarding basket data to the respective financial service provider.')
                    ),
                    array(
                        'name' => 'sendconfirmationemail',
                        'label' => $this->l('Notification e-mail'),
                        'type' => 'onoff',
                        'doc' => array(
                            $this->l('Receiving notification by e-mail regarding the orders of your consumers if an error occurred in the communication between QENTA and your online shop.'),
                            $this->l('Please contact our sales teams to activate this feature.')
                        ),
                        'docref' => 'https://guides.qenta.com/sales'
                    ),
                    array(
                        'name' => 'autodeposit',
                        'label' => $this->l('Automated deposit'),
                        'default' => 0,
                        'group' => 'pt',
                        'type' => 'onoff',
                        'doc' => array(
                            $this->l('Enabling an automated deposit of payments.'),
                            $this->l('Please contact our sales teams to activate this feature.')
                        ),
                        'docref' => 'https://guides.qenta.com/sales'
                    ),
                    array(
                        'name' => 'payolution_terms',
                        'label' => $this->l('payolution terms'),
                        'type' => 'onoff',
                        'default' => 1,
                        'doc' => $this->l('Consumer must accept payolution terms during the checkout process.'),
                        'docref' => 'https://guides.qenta.com/payment_methods:invoice:payolution'
                    ),
                    array(
                        'name' => 'payolution_mid',
                        'label' => $this->l('payolution mID'),
                        'type' => 'text',
                        'doc' => $this->l('Your payolution merchant ID, non-base64-encoded.')
                    ),
                )
            ),
            'creditcardoptions' => array(
                'tab' => $this->l('Credit card'),
                'fields' => array(
                    array(
                        'name' => 'pci3_dss_saq_a_enable',
                        'label' => $this->l('SAQ A compliance'),
                        'type' => 'onoff',
                        'default' => 0,
                        'doc' => $this->l('Selecting \'NO\', the stringent SAQ A-EP is applicable. Selecting \'YES\', QENTA Checkout Seamless is integrated with the \'PCI DSS SAQ A Compliance\' feature and SAQ A is applicable.'),
                        'docref' => 'https://guides.qenta.com/doku.php/qcs:pci3_fallback:start'
                    ),
                    array(
                        'name' => 'ccardmoto_usergroup',
                        'label' => $this->l('Allowing MoTo for group'),
                        'type' => 'select',
                        'options' => 'getUserGroups',
                        'doc' => $this->l('Credit Card - Mail Order and Telephone Order (MoTo) must never be offered to any consumer in your online shop.'),
                        'docref' => 'https://guides.qenta.com/payment_methods:ccard_moto:start'
                    ),
                    array(
                        'name' => 'iframe_css_url',
                        'label' => $this->l('Iframe CSS-URL'),
                        'type' => 'text',
                        'default' => 'iframe.css',
                        'cssclass' => 'fixed-width-xxl',
                        'doc' => $this->l('Entry of a name for the CSS file in order to customize the iframe input fields when using the \'PCI DSS SAQ A Compliance\' feature. File must be placed in the \'view/css\' directory of the plugin.'),
                        'docref' => 'https://guides.qenta.com/doku.php/qcs:pci3_fallback:start#customization_via_css'
                    ),
                    array(
                        'name' => 'pan_placeholder',
                        'label' => $this->l('Credit card number placeholder text'),
                        'type' => 'text',
                        'doc' => $this->l('Placeholder text for the credit card number field.')
                    ),
                    array(
                        'name' => 'displayexpirationdate_placeholder',
                        'default' => 0,
                        'label' => $this->l('Display expiration date field'),
                        'type' => 'onoff',
                        'doc' => $this->l('Display input field to enter the expiration date in your credit card form during the checkout process.'),
                    ),
                    array(
                        'name' => 'displaycardholder',
                        'label' => $this->l('Display card holder field'),
                        'type' => 'onoff',
                        'default' => 1,
                        'doc' => $this->l('Display input field to enter the card holder name in your credit card form during the checkout process.'),
                    ),
                    array(
                        'name' => 'cardholder_placeholder',
                        'label' => $this->l('Card holder placeholder text'),
                        'type' => 'text',
                        'doc' => $this->l('Placeholder text for the card holder field.')
                    ),
                    array(
                        'name' => 'displaycvc',
                        'label' => $this->l('Display CVC field'),
                        'default' => 1,
                        'type' => 'onoff',
                        'doc' => $this->l('Display input field to enter the CVC in your credit card form during the checkout process.'),
                    ),
                    array(
                        'name' => 'cvc_placeholder',
                        'label' => $this->l('CVC placeholder text'),
                        'type' => 'text',
                        'doc' => $this->l('Placeholder text for the CVC field.')
                    ),
                    array(
                        'name' => 'displayissuedate',
                        'label' => $this->l('Display issue date field'),
                        'default' => 0,
                        'type' => 'onoff',
                        'doc' => $this->l('Display input field for issDisplay input field to enter the credit card issue date in your credit card form during the checkout process. Some credit cards do not have an issue date.')
                    ),
                    array(
                        'name' => 'displayissuedate_placeholder',
                        'label' => $this->l('Display issue date placeholder text'),
                        'default' => 0,
                        'type' => 'onoff',
                        'doc' => $this->l('Display placeholder text for the issue date field. Only applicable if the \'PCI DSS SAQ A Compliance\' feature is enabled.')
                    ),
                    array(
                        'name' => 'displayissuenumber',
                        'label' => $this->l('Display issue number field'),
                        'default' => 0,
                        'type' => 'onoff',
                        'doc' => $this->l('Display input field to enter the credit card issue number in your credit card form during the checkout process. Some credit cards do not have an issue number.')
                    ),
                    array(
                        'name' => 'issuenumber_placeholder',
                        'label' => $this->l('Issue number placeholder text'),
                        'type' => 'text',
                        'doc' => $this->l('Display placeholder text for the credit card issue number field.')
                    )
                )
            ),
            'invoiceoptions' => array(
                'tab' => $this->l('Invoice'),
                'fields' => array(
                    array(
                        'name' => 'invoice_provider',
                        'label' => $this->l('Invoice provider'),
                        'type' => 'select',
                        'group' => 'pt',
                        //'default'  => 'qenta', // XXX problem with ee
                        'default' => 'payolution',
                        'required' => true,
                        'options' => 'getInvoiceProviders'
                    ),
                    array(
                        'name' => 'invoice_billingshipping_same',
                        'label' => $this->l('Billing/shipping address must be identical'),
                        'type' => 'onoff',
                        'default' => 1,
                        'group' => 'pt'
                    ),
                    array(
                        'name' => 'invoice_billing_countries',
                        'label' => $this->l('Allowed billing countries'),
                        'type' => 'select',
                        'multiple' => true,
                        'size' => 10,
                        'default' => array('AT', 'DE', 'CH'),
                        'options' => 'getCountries',
                        'group' => 'pt',
                    ),
                    array(
                        'name' => 'invoice_shipping_countries',
                        'label' => $this->l('Allowed shipping countries'),
                        'type' => 'select',
                        'multiple' => true,
                        'size' => 10,
                        'default' => array('AT', 'DE', 'CH'),
                        'options' => 'getCountries',
                        'group' => 'pt',
                    ),
                    array(
                        'name' => 'invoice_currencies',
                        'label' => $this->l('Accepted currencies'),
                        'type' => 'select',
                        'multiple' => true,
                        'default' => array('EUR'),
                        'options' => 'getCurrencies',
                        'group' => 'pt',
                    ),
                    array(
                        'name' => 'invoice_min_age',
                        'label' => $this->l('Minimum age'),
                        'type' => 'text',
                        'group' => 'pt',
                        'default' => 18,
                        'validator' => 'numeric',
                        'cssclass' => 'fixed-width-md',
                        'doc' => $this->l('Only applicable for RatePay.')
                    ),
                    array(
                        'name' => 'invoice_amount_min',
                        'label' => $this->l('Minimum amount'),
                        'type' => 'text',
                        'group' => 'pt',
                        'default' => 10,
                        'validator' => 'numeric',
                        'cssclass' => 'fixed-width-md',
                        'suffix' => 'EUR'
                    ),
                    array(
                        'name' => 'invoice_amount_max',
                        'label' => $this->l('Maximum amount'),
                        'type' => 'text',
                        'group' => 'pt',
                        'default' => 3500,
                        'validator' => 'numeric',
                        'cssclass' => 'fixed-width-md',
                        'suffix' => 'EUR'
                    ),
                    array(
                        'name' => 'invoice_basketsize_min',
                        'label' => $this->l('Minimum basket size'),
                        'type' => 'text',
                        'group' => 'pt',
                        'validator' => 'numeric',
                        'cssclass' => 'fixed-width-md',
                    ),
                    array(
                        'name' => 'invoice_basketsize_max',
                        'label' => $this->l('Maximum basket size'),
                        'type' => 'text',
                        'group' => 'pt',
                        'validator' => 'numeric',
                        'cssclass' => 'fixed-width-md',
                    ),
                )
            ),
            'installmentoptions' => array(
                'tab' => $this->l('Installment'),
                'fields' => array(
                    array(
                        'name' => 'installment_provider',
                        'label' => $this->l('Installment provider'),
                        'type' => 'select',
                        'group' => 'pt',
                        'default' => 'payolution',
                        'required' => true,
                        'options' => 'getInstallmentProviders'
                    ),
                    array(
                        'name' => 'installment_billingshipping_same',
                        'label' => $this->l('Billing/shipping address must be identical'),
                        'type' => 'onoff',
                        'default' => 1,
                        'group' => 'pt'
                    ),
                    array(
                        'name' => 'installment_billing_countries',
                        'label' => $this->l('Allowed billing countries'),
                        'type' => 'select',
                        'multiple' => true,
                        'size' => 10,
                        'default' => array('AT', 'DE', 'CH'),
                        'options' => 'getCountries',
                        'group' => 'pt',
                    ),
                    array(
                        'name' => 'installment_shipping_countries',
                        'label' => $this->l('Allowed shipping countries'),
                        'type' => 'select',
                        'multiple' => true,
                        'size' => 10,
                        'default' => array('AT', 'DE', 'CH'),
                        'options' => 'getCountries',
                        'group' => 'pt',
                    ),
                    array(
                        'name' => 'installment_currencies',
                        'label' => $this->l('Accepted currencies'),
                        'type' => 'select',
                        'multiple' => true,
                        'default' => array('EUR'),
                        'options' => 'getCurrencies',
                        'group' => 'pt',
                    ),
                    array(
                        'name' => 'installment_min_age',
                        'label' => $this->l('Minimum age'),
                        'type' => 'text',
                        'group' => 'pt',
                        'validator' => 'numeric',
                        'default' => 18,
                        'cssclass' => 'fixed-width-md',
                        'doc' => 'Only applicable for RatePay.'
                    ),
                    array(
                        'name' => 'installment_amount_min',
                        'label' => $this->l('Minimum amount'),
                        'type' => 'text',
                        'group' => 'pt',
                        'validator' => 'numeric',
                        'default' => 150,
                        'cssclass' => 'fixed-width-md',
                        'suffix' => 'EUR'
                    ),
                    array(
                        'name' => 'installment_amount_max',
                        'label' => $this->l('Maximum amount'),
                        'type' => 'text',
                        'group' => 'pt',
                        'default' => 3500,
                        'validator' => 'numeric',
                        'cssclass' => 'fixed-width-md',
                        'suffix' => 'EUR'
                    ),
                    array(
                        'name' => 'installment_basketsize_min',
                        'label' => $this->l('Minimum basket size'),
                        'type' => 'text',
                        'group' => 'pt',
                        'validator' => 'numeric',
                        'cssclass' => 'fixed-width-md',
                    ),
                    array(
                        'name' => 'installment_basketsize_max',
                        'label' => $this->l('Maximum basket size'),
                        'type' => 'text',
                        'group' => 'pt',
                        'validator' => 'numeric',
                        'cssclass' => 'fixed-width-md',
                    ),
                )
            ),
            'standardpayments' => array(
                'tab' => $this->l('Standard payments'),
                'fields' => array(
                    array(
                        'name' => 'creditcard',
                        'label' => $this->l('Credit Card'),
                        'type' => 'onoff',
                        'default' => 1,
                        'class' => 'Ccard',
                        'template' => 'ccard.tpl',
                        'seamless' => true,
                        'logo' => 'cc.png'
                    ),
                    array(
                        'name' => 'creditcardmoto',
                        'label' => $this->l('Credit Card - Mail Order and Telephone Order'),
                        'type' => 'onoff',
                        'default' => 0,
                        'class' => 'CcardMoto',
                        'template' => 'ccard.tpl',
                        'seamless' => true,
                        'logo' => 'ccMoto.png'
                    ),
                    array(
                        'name' => 'maestro',
                        'label' => $this->l('Maestro SecureCode'),
                        'type' => 'onoff',
                        'default' => 0,
                        'class' => 'Maestro',
                        'template' => 'ccard.tpl',
                        'seamless' => true,
                        'logo' => 'maestro_secure_code.png'
                    ),
                    array(
                        'name' => 'sofortbanking',
                        'label' => $this->l('Online bank transfer.'),
                        'type' => 'onoff',
                        'default' => 1,
                        'class' => 'Sofortbanking',
                        'logo' => 'sofortbanking.png'
                    ),
                    array(
                        'name' => 'paypal',
                        'label' => $this->l('PayPal'),
                        'type' => 'onoff',
                        'default' => 1,
                        'class' => 'Paypal',
                        'logo' => 'paypal.png'
                    ),
                    array(
                        'name' => 'sepa',
                        'label' => $this->l('SEPA Direct Debit'),
                        'type' => 'onoff',
                        'default' => 1,
                        'class' => 'Sepa',
                        'seamless' => true,
                        'template' => 'sepa.tpl',
                        'logo' => 'sepadd.png'
                    ),
                    array(
                        'name' => 'invoice',
                        'label' => $this->l('Invoice'),
                        'type' => 'onoff',
                        'default' => 1,
                        'class' => 'Invoice',
                        'template' => 'invoiceinstallment.tpl',
                        'logo' => 'invoice.png'
                    ),
                    array(
                        'name' => 'invoiceb2b',
                        'label' => $this->l('Invoice B2B'),
                        'type' => 'onoff',
                        'default' => 1,
                        'class' => 'InvoiceB2B',
                        'logo' => 'invoiceb2b.png'
                    ),
                )
            ),
            'bankingpayments' => array(
                'tab' => $this->l('Banking payments'),
                'fields' => array(
                    array(
                        'name' => 'eps',
                        'label' => $this->l('eps-Überweisung'),
                        'type' => 'onoff',
                        'class' => 'Eps',
                        'template' => 'financialinstitution.tpl',
                        'logo' => 'eps.png'
                    ),
                    array(
                        'name' => 'ideal',
                        'label' => $this->l('iDEAL'),
                        'type' => 'onoff',
                        'class' => 'Ideal',
                        'template' => 'financialinstitution.tpl',
                        'logo' => 'ideal.png'
                    ),
                    array(
                        'name' => 'giropay',
                        'label' => $this->l('giropay'),
                        'type' => 'onoff',
                        'class' => 'Giropay',
                        'seamless' => true,
                        'template' => 'giropay.tpl',
                        'logo' => 'giropay.png'
                    ),
                    array(
                        'name' => 'tatrapay',
                        'label' => $this->l('TatraPay'),
                        'type' => 'onoff',
                        'class' => 'Tatrapay',
                        'logo' => 'tatrapay.png'
                    ),
                    array(
                        'name' => 'trustpay',
                        'label' => $this->l('TrustPay'),
                        'type' => 'onoff',
                        'class' => 'Trustpay',
                        'template' => 'financialinstitution.tpl',
                        'logo' => 'trustpay.png'
                    ),
                    array(
                        'name' => 'bmc',
                        'label' => $this->l('Bancontact/Mister Cash'),
                        'type' => 'onoff',
                        'class' => 'Bmc',
                        'logo' => 'bmc.png'
                    ),
                    array(
                        'name' => 'poli',
                        'label' => $this->l('POLi'),
                        'type' => 'onoff',
                        'class' => 'Poli',
                        'logo' => 'poli.png'
                    ),
                    array(
                        'name' => 'p24',
                        'label' => $this->l('Przelewy24'),
                        'type' => 'onoff',
                        'class' => 'P24',
                        'logo' => 'p24.png'
                    ),
                    array(
                        'name' => 'ekonto',
                        'label' => $this->l('eKonto'),
                        'type' => 'onoff',
                        'class' => 'Ekonto',
                        'logo' => 'ekonto.png'
                    ),
                    array(
                        'name' => 'trustly',
                        'label' => $this->l('Trustly'),
                        'type' => 'onoff',
                        'class' => 'Trustly',
                        'logo' => 'trustly.png'
                    )
                )
            ),
            'alternativepayments' => array(
                'tab' => $this->l('Alternative payments'),
                'fields' => array(
                    array(
                        'name' => 'paysafecard',
                        'label' => $this->l('paysafecard'),
                        'type' => 'onoff',
                        'class' => 'Paysafecard',
                        'logo' => 'paysafecard.png'
                    ),
                    array(
                        'name' => 'epaybg',
                        'label' => $this->l('ePay.bg'),
                        'type' => 'onoff',
                        'class' => 'Epaybg',
                        'logo' => 'epaybg.png'
                    ),
                    array(
                        'name' => 'installment',
                        'label' => $this->l('Installment'),
                        'type' => 'onoff',
                        'class' => 'Installment',
                        'template' => 'invoiceinstallment.tpl',
                        'logo' => 'installment.png'
                    ),
                    array(
                        'name' => 'moneta',
                        'label' => $this->l('moneta.ru'),
                        'type' => 'onoff',
                        'class' => 'Moneta',
                        'logo' => 'moneta.png'
                    ),
                    array(
                        'name' => 'skrillwallet',
                        'label' => $this->l('Skrill Digital Wallet'),
                        'type' => 'onoff',
                        'class' => 'Skrillwallet',
                        'logo' => 'skrillwallet.png'
                    ),

                )
            ),
            'mobilepayments' => array(
                'tab' => $this->l('Mobile payments'),
                'fields' => array(
                    array(
                        'name' => 'paybox',
                        'label' => $this->l('paybox'),
                        'type' => 'onoff',
                        'class' => 'Paybox',
                        'seamless' => true,
                        'template' => 'paybox.tpl',
                        'logo' => 'paybox.png'
                    )
                )
            ),
            'voucherpayments' => array(
                'tab' => $this->l('Voucher payments'),
                'fields' => array(
                    array(
                        'name' => 'voucher',
                        'label' => $this->l('My Voucher'),
                        'type' => 'onoff',
                        'class' => 'Voucher',
                        'seamless' => true,
                        'template' => 'voucher.tpl',
                        'logo' => 'voucher.png'
                    ),
                )
            ),
        );
    }

    /**
     * Module/hooks installation
     *
     * @return bool
     * @throws PrestaShopException
     * @throws PrestaShopExceptionDispl
     */
    public function install()
    {
        if (
            !parent::install()
            || !$this->registerHook('displayPaymentReturn')
            || !$this->registerHook('backOfficeHeader')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->registerHook('paymentOptions')
            || !$this->setDefaults()
        ) {
            return false;
        }

        $this->installTabs();


        if (!$this->createTable()) {
            return false;
        }

        if (!$this->addMissingColumns()) {
            return false;
        }

        // http://forge.prestashop.com/browse/PSCFV-1712
        if ($this->registerHook('displayPDFInvoice') === false) {
            return false;
        }

        if (!Configuration::get(self::QCS_OS_AWAITING)) {

            /** @var OrderStateCore $orderState */
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'de') {
                    $orderState->name[$language['id_lang']] = 'Checkout Seamless Bezahlung ausständig';
                } else {
                    $orderState->name[$language['id_lang']] = 'Checkout Seamless payment awaiting';
                }
            }
            $orderState->send_email = false;
            $orderState->color = 'lightblue';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name = 'qentacheckoutseamless';
            if ($orderState->add()) {
                copy(
                    dirname(__FILE__) . '/views/img/awaiting_payment.gif',
                    dirname(__FILE__) . '/../../img/os/' . (int)($orderState->id) . '.gif'
                );
            }
            Configuration::updateValue(
                self::QCS_OS_AWAITING,
                (int)($orderState->id)
            );
        }

        if (!Configuration::get(self::QCS_OS_FRAUD)) {

            /** @var OrderStateCore $orderState */
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'de') {
                    $orderState->name[$language['id_lang']] = 'Checkout Seamless Betrugsversuch';
                } else {
                    $orderState->name[$language['id_lang']] = 'Checkout Seamless fraud detected';
                }
            }
            $orderState->send_email = false;
            $orderState->color = '#8f0621';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name = 'qentacheckoutseamless';
            if ($orderState->add()) {
                copy(
                    dirname(__FILE__) . '/views/img/os_fraud.gif',
                    dirname(__FILE__) . '/../../img/os/' . (int)($orderState->id) . '.gif'
                );
            }
            Configuration::updateValue(
                self::QCS_OS_FRAUD,
                (int)($orderState->id)
            );
        }

        return true;
    }

    private function getColumnDefs()
    {
        return array(
            "id_tx" => array("INT(10) UNSIGNED", "NOT NULL", "AUTO_INCREMENT"),
            "id_order" => array("INT(10)", "NULL"),
            "id_cart" => array("INT(10) UNSIGNED", "NOT NULL"),
            "carthash" => array("VARCHAR(255)"),
            "ordernumber" => array("VARCHAR(32)", "NULL"),
            "creditnumber" => array("INT(9)", "NULL"),
            "orderreference" => array("VARCHAR(128)", "NULL"),
            "paymentname" => array("VARCHAR(32)", "NULL"),
            "paymentmethod" => array("VARCHAR(32)", "NOT NULL"),
            "paymentstate" => array("VARCHAR(32)", "NOT NULL"),
            "gatewayreference" => array("VARCHAR(32)", "NULL"),
            "amount" => array("FLOAT", "NOT NULL"),
            "currency" => array("VARCHAR(3)", "NOT NULL"),
            "message" => array("VARCHAR(255)", "NULL"),
            "request" => array("TEXT", "NULL"),
            "response" => array("TEXT", "NULL"),
            "status" => array("ENUM ('ok', 'error')", "NOT NULL", "DEFAULT 'ok'"),
            "created" => array("DATETIME", "NOT NULL"),
            "modified" => array("DATETIME", "NULL"),
        );
    }

    private function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS  `' . _DB_PREFIX_ . 'qenta_checkout_seamless_tx` (';
        foreach ($this->getColumnDefs() as $column => $definitions) {
            $sql .= "\n" . "\t" . $column . ' ';
            foreach ($definitions as $definition) {
                $sql .= $definition . ' ';
            }
            $sql .= ',';
        }
        $sql .= "\n" . 'PRIMARY KEY (`id_tx`)';
        $sql .= "\n" . ') ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * @return bool
     */
    private function addMissingColumns()
    {
        $columns_db = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'qenta_checkout_seamless_tx`');
        $columns = array();

        $column_definitions = $this->getColumnDefs();

        $sql = null;
        if ($columns_db) {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'qenta_checkout_seamless_tx` ';
            foreach ($columns_db as $column) {
                $columns[] = $column['Field'];
            }

            foreach ($column_definitions as $column => $definitions) {
                if (!in_array($column, $columns)) {
                    $sql .= "\n" . 'ADD COLUMN `' . $column . '` ';
                    foreach ($definitions as $definition) {
                        $sql .= $definition . ' ';
                    }
                    $sql = rtrim($sql, " ");
                    $sql .= ',';
                }
            }

            $sql = rtrim($sql, ",") . ";";
        }
        return $sql == null ? true : Db::getInstance()->execute($sql);
    }

    /**
     * set configuration value defaults
     *
     * @return bool
     */
    private function setDefaults()
    {
        foreach ($this->config as $groupKey => $group) {
            foreach ($group['fields'] as $f) {
                if (array_key_exists('default', $f)) {
                    $configGroup = isset($f['group']) ? $f['group'] : $groupKey;

                    if (isset($f['class'])) {
                        $configGroup = 'pt';
                    }
                    $p = $this->buildParamName($configGroup, $f['name']);
                    $defVal = $f['default'];
                    if (is_array($defVal)) {
                        $defVal = Tools::jsonEncode($defVal);
                    }

                    if (!Configuration::updateValue($p, $defVal)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * build prestashop internal parameter name
     *
     * @param $group
     * @param $name
     *
     * @return string
     */
    protected function buildParamName($group, $name)
    {
        return sprintf(
            'QCS_%s_%s',
            Tools::strtoupper($group),
            Tools::strtoupper($name)
        );
    }

    /**
     * register tabs
     */
    public function installTabs()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminQentaCheckoutSeamlessBackend';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'QENTA Transactions';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = $this->name;
        $tab->add();

        $tab = new Tab();
        $tab->active = 0;
        $tab->class_name = 'AdminQentaCheckoutSeamlessSupport';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'QENTA Checkout Seamless Support';
        }
        $tab->module = $this->name;
        $tab->add();

        $tab = new Tab();
        $tab->active = 0;
        $tab->class_name = 'AdminQentaCheckoutSeamlessFundTransfer';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'QENTA Checkout Seamless Fund Transfer';
        }
        $tab->module = $this->name;
        $tab->add();
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        foreach ($this->getAllConfigurationParameters() as $parameter) {
            Configuration::deleteByName($parameter['param_name']);
        }

        Configuration::deleteByName(self::QCS_OS_AWAITING);

        $this->uninstallTabs();

        return parent::uninstall();
    }

    /**
     * return alls configuration parameters
     *
     * @return array
     */
    public function getAllConfigurationParameters()
    {
        $params = array();
        foreach ($this->config as $groupKey => $group) {
            foreach ($group['fields'] as $f) {
                $configGroup = isset($f['group']) ? $f['group'] : $groupKey;

                if (isset($f['class'])) {
                    $configGroup = 'pt';
                }

                $f['param_name'] = $this->buildParamName(
                    $configGroup,
                    $f['name']
                );
                $params[] = $f;
            }
        }

        return $params;
    }

    /**
     * remove tabs
     */
    public function uninstallTabs()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminQentaCheckoutSeamlessBackend');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }

        $id_tab = (int)Tab::getIdFromClassName('AdminQentaCheckoutSeamlessSupport');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }

        $id_tab = (int)Tab::getIdFromClassName('AdminQentaCheckoutSeamlessFundTransfer');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
    }

    /**
     * get page content, dispatch ajax submit
     *
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    public function getContent()
    {
        $this->createTable();
        if (Tools::isSubmit('ajax')) {
            return $this->postProcess();
        } else {
            $this->html = '<h2>' . $this->displayName . '</h2>';

            if (Tools::isSubmit('btnSubmit')) {
                $this->postValidation();
                if (!count($this->postErrors)) {
                    $this->postProcess();
                } else {
                    foreach ($this->postErrors as $err) {
                        $this->html .= $this->displayError(html_entity_decode($err));
                    }
                }
            }

            $context = $this->context;
            $country = Tools::strtolower($context->country->iso_code);
            $language = $context->language->iso_code;

            if ($language != $country && $language = 'en') {
                $language = 'en';
            }

            if (!in_array($country, array('gb', 'de', 'it', 'es', 'pl', 'nl', 'fr'))) {
                $country = 'de';
                $language = 'en';
            }


            $backendEnabled = Configuration::get('QCS_BASICDATA_BACKENDPW');
            $this->context->smarty->assign(
                array(
                    'country' => $country,
                    'language' => $language,
                    'shopversion' => _PS_VERSION_,
                    'pluginversion' => $this->version,
                    'is_core' => $this->isCore,
                    'module_dir' => $this->_path,
                    'link' => $this->context->link,
                    'backendEnabled' => $backendEnabled != null && Tools::strlen($backendEnabled) > 0,
                    'ajax_configtest_url' => $this->context->link->getAdminLink('AdminModules') . '&configure='
                        . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name
                )
            );
            $this->html .= $this->context->smarty->fetch(
                dirname(__FILE__) . '/views/templates/admin/configuration.tpl'
            );
            $this->html .= $this->renderForm();

            return $this->html;
        }
    }

    public function ajaxProcessGetOrdersSelect2()
    {
        $term = Tools::getValue('q');
        if (!Tools::strlen($term)) {
            $term = '';
        } else {
            $term = 'AND ordernumber LIKE "' . pSQL($term) . '%"';
        }

        $page = Tools::getValue('page');
        $resultCount = 25;

        $offset = ($page - 1) * $resultCount;

        $limit = " LIMIT " . $offset . ", " . $resultCount;

        $sql = 'SELECT ordernumber,currency FROM ' . _DB_PREFIX_ .
            'qenta_checkout_seamless_tx WHERE ordernumber IS NOT NULL AND paymentstate != "CREDIT"';
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
    }

    /**
     * process form post
     */
    private function postProcess()
    {
        if (Tools::isSubmit('ajax')) {
            if (Tools::getValue('action') == 'ajaxTestConfig') {
                $this->ajaxTestConfig();
            }
        } else {
            if (Tools::isSubmit('btnSubmit')) {
                foreach ($this->getAllConfigurationParameters() as $parameter) {
                    $val = Tools::getValue($parameter['param_name']);

                    if (isset($parameter['sanitize'])) {
                        switch ($parameter['sanitize']) {
                            case 'trim':
                                $val = trim($val);
                                break;
                        }
                    }

                    if (is_array($val)) {
                        $val = Tools::jsonEncode($val);
                    }

                    Configuration::updateValue($parameter['param_name'], $val);
                }
            }
            $this->html .= $this->displayConfirmation($this->l('Settings updated'));
        }
    }

    /**
     * send test initiation, check if credentials are ok
     */
    public function ajaxTestConfig()
    {
        $status = 'ok';
        $message = $this->l('The merchant configuration was successfuly tested.');
        $client = new QentaCheckoutSeamlessBackend($this);
        if (!$client->isAvailable()) {
            $status = 'failed';
            $message = $this->l('Back-end password is not set.');
        } else {
            $init = new WirecardCEE_QMore_FrontendClient($this->getConfigArray());
            $init->setPluginVersion($this->getPluginVersion());
            $init->setConfirmUrl($this->getConfirmUrl());

            $init->setOrderReference(sprintf('Configtest #', uniqid()));

            $returnUrl = $this->context->link->getModuleLink(
                $this->name,
                'back',
                array(
                    'id_cart' => 0,
                    'id_module' => (int)$this->id,
                    'id_tx' => 0,
                    'key' => uniqid()
                ),
                true
            );

            $consumerData = new WirecardCEE_Stdlib_ConsumerData();
            $consumerData->setIpAddress('127.0.0.1');
            $consumerData->setUserAgent(
                'Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko)
                Mobile/7B405'
            );

            $init->setAmount('10')
                ->setCurrency('EUR')
                ->setPaymentType(WirecardCEE_QMore_PaymentType::CCARD)
                ->setOrderDescription('Configtest #' . uniqid())
                ->setSuccessUrl($returnUrl)
                ->setPendingUrl($returnUrl)
                ->setCancelUrl($returnUrl)
                ->setFailureUrl($returnUrl)
                ->setServiceUrl($this->getServiceUrl())
                ->setDuplicateRequestCheck(false)
                ->setAutoDeposit($this->getConfigValue('pt', 'autodeposit'))
                ->setWindowName($this->getWindowName())
                ->setConsumerData($consumerData);

            try {
                $initResponse = $init->initiate();

                if ($initResponse->getStatus() == WirecardCEE_QMore_Response_Initiation::STATE_FAILURE) {
                    $msg = implode(
                        ',',
                        array_map(
                            function ($e) {
                                /** @var \WirecardCEEQMoreError $e */
                                return $e->getConsumerMessage();
                            },
                            $initResponse->getErrors()
                        )
                    );
                    if (!Tools::strlen($msg)) {
                        $msg = $initResponse = implode(
                            ',',
                            array_map(
                                function ($e) {
                                    /** @var \WirecardCEEQMoreError $e */
                                    return $e->getPaySysMessage();
                                },
                                $initResponse->getErrors()
                            )
                        );
                    }

                    throw new Exception($msg);
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                $status = 'failed';
            }
        }

        die(Tools::jsonEncode(
            array(
                'status' => htmlspecialchars($status),
                'message' => htmlspecialchars($message)
            )
        ));
    }

    /**
     * return config data as needed by the client library
     *
     * @return array
     */
    public function getConfigArray()
    {
        $cfg = array('LANGUAGE' => $this->getLanguage());
        $cfg['CUSTOMER_ID'] = $this->getConfigValue('basicdata', 'customer_id');
        $cfg['SHOP_ID'] = $this->getConfigValue('basicdata', 'shop_id');
        $cfg['SECRET'] = $this->getConfigValue('basicdata', 'secret');

        return $cfg;
    }

    /**
     * return current language
     *
     * @return array
     */
    public function getLanguage()
    {
        return $this->context->language->iso_code;
    }

    /**
     * get config value, take presets into account
     *
     * @param $group
     * @param $field
     *
     * @return string
     */
    public function getConfigValue($group, $field)
    {
        if ($group == 'basicdata') {
            $mode = Configuration::get(
                $this->buildParamName(
                    'basicdata',
                    'configmode'
                )
            );

            if (isset($this->presets[$mode]) && isset($this->presets[$mode][$field])) {
                return $this->presets[$mode][$field];
            }
        }

        return Configuration::get($this->buildParamName($group, $field));
    }

    /**
     * return plugin version
     *
     * @return string
     */
    public function getPluginVersion()
    {
        return WirecardCEE_QMore_FrontendClient::generatePluginVersion(
            $this->isCore ? 'Prestashop core' : 'Prestashop',
            _PS_VERSION_,
            $this->name,
            $this->version
        );
    }

    /**
     * return confirmation url (server2server)
     *
     * @return array
     */
    public function getConfirmUrl()
    {
        return $this->context->link->getModuleLink($this->name, 'confirm', array(), true);
    }

    /**
     * return service url
     *
     * @return array
     */
    public function getServiceUrl()
    {
        return $this->context->link->getPageLink('contact', true);
    }

    /**
     * return window name
     *
     * @return array
     */
    public function getWindowName()
    {
        return self::WINDOW_NAME;
    }

    /**
     * validate post parameters
     */
    private function postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $configmode = Tools::getValue('QCS_BASICDATA_CONFIGMODE');

            foreach ($this->getAllConfigurationParameters() as $parameter) {
                $val = Tools::getValue($parameter['param_name']);

                if (isset($parameter['sanitize'])) {
                    switch ($parameter['sanitize']) {
                        case 'trim':
                            $val = trim($val);
                            break;
                    }
                }

                if (isset($parameter['required']) && $parameter['required'] && !Tools::strlen($val)) {
                    if (in_array(
                        $parameter['name'],
                        array(
                            'customer_id',
                            'shop_id',
                            'secret',
                            'backendpw'
                        )
                    )) {
                        if ($configmode == 'production') {
                            $this->postErrors[] = $parameter['label'] . ' ' . $this->l('is required.');
                        }
                    } else {
                        $this->postErrors[] = $parameter['label'] . ' ' . $this->l('is required.');
                    }
                }

                if ($parameter['name'] == 'customer_id') {
                    preg_match("/^D2[0-8]\d{4}|9[5-9]\d{3}$/", $val, $output);
                    if (count($output) !== 1) {
                        $this->postErrors[] = $this->l('Incorrect customer ID.');
                    }
                }

                if (!isset($parameter['validator'])) {
                    continue;
                }

                switch ($parameter['validator']) {
                    case 'numeric':
                        if (Tools::strlen($val) && !is_numeric($val)) {
                            $this->postErrors[] = $parameter['label'] . ' ' . $this->l(' must be a number.');
                        }
                        break;
                }
            }
        }
    }

    /**
     * render form
     *
     * @return string
     */
    private function renderForm()
    {
        $radio_type = 'switch';

        $radio_options = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Enabled')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('Disabled')
            )
        );

        $input_fields = array();
        $tabs = array();
        foreach ($this->config as $groupKey => $group) {
            $tabs[$groupKey] = $this->l($group['tab']);
            foreach ($group['fields'] as $f) {
                $configGroup = isset($f['group']) ? $f['group'] : $groupKey;
                if (isset($f['class'])) {
                    $configGroup = 'pt';
                }

                $elem = array(
                    'name' => $this->buildParamName($configGroup, $f['name']),
                    'label' => $this->l($f['label']),
                    'tab' => $groupKey,
                    'type' => $f['type'],
                    'required' => isset($f['required']) && $f['required']
                );

                if (isset($f['cssclass'])) {
                    $elem['class'] = $f['cssclass'];
                }

                if (isset($f['doc'])) {
                    if (is_array($f['doc'])) {
                        $elem['desc'] = '';
                        foreach ($f['doc'] as $d) {
                            if (Tools::strlen($elem['desc'])) {
                                $elem['desc'] .= '<br/>';
                            }

                            $elem['desc'] .= $this->l($d);
                        }
                    } else {
                        $elem['desc'] = $this->l($f['doc']);
                    }
                }

                if (isset($f['docref'])) {
                    $elem['desc'] = isset($elem['desc']) ? $elem['desc'] . ' ' : '';
                    $elem['desc'] .= sprintf(
                        '<a target="_blank" href="%s">%s <i class="icon-external-link"></i></a>',
                        $f['docref'],
                        $this->l('More information')
                    );
                }

                switch ($f['type']) {
                    case 'text':
                        if (!isset($elem['class'])) {
                            $elem['class'] = 'fixed-width-xl';
                        }

                        if (isset($f['maxchar'])) {
                            $elem['maxlength'] = $elem['maxchar'] = $f['maxchar'];
                        }
                        break;

                    case 'onoff':
                        $elem['type'] = $radio_type;
                        $elem['class'] = 't';
                        $elem['is_bool'] = true;
                        $elem['values'] = $radio_options;
                        break;

                    case 'select':
                        if (isset($f['multiple'])) {
                            $elem['multiple'] = $f['multiple'];
                        }

                        if (isset($f['size'])) {
                            $elem['size'] = $f['size'];
                        }

                        if (isset($f['options'])) {
                            $optfunc = $f['options'];
                            $options = array();
                            if (is_array($optfunc)) {
                                $options = $optfunc;
                            }

                            if (method_exists($this, $optfunc)) {
                                $options = $this->$optfunc();
                            }

                            $elem['options'] = array(
                                'query' => $options,
                                'id' => 'key',
                                'name' => 'value'
                            );
                        }
                        break;

                    default:
                        break;
                }

                $input_fields[] = $elem;
            }
        }

        $fields_form_settings = array(
            'form' => array(
                'tabs' => $tabs,
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $input_fields,
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );


        /** @var HelperFormCore $helper */
        $helper = new HelperForm();
        $helper->show_toolbar = false;

        /** @var LanguageCore $lang */
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'ajax_configtest_url' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name
                . '&tab_module=' . $this->tab . '&module_name=' . $this->name
        );

        return $helper->generateForm(array($fields_form_settings));
    }

    /**
     * return saved config parameter values
     *
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $values = array();
        foreach ($this->getAllConfigurationParameters() as $parameter) {
            $val = Configuration::get($parameter['param_name']);
            if (isset($parameter['multiple']) && $parameter['multiple']) {
                if (!is_array($val)) {
                    $val = Tools::strlen($val) ? Tools::jsonDecode($val) : array();
                }

                $x = array();
                foreach ($val as $v) {
                    $x[$v] = $v;
                }
                $pname = $parameter['param_name'] . '[]';
                $values[$pname] = $x;
            } else {
                $values[$parameter['param_name']] = $val;
            }
        }

        return $values;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        if (!isset($params['order']) || ($params['order']->module != $this->name)) {
            return false;
        }
        if (isset($params['order']) && Validate::isLoadedObject($params['order']) && isset($params['order']->valid)) {
            $this->smarty->assign(array(
                'id_order' => $params['order']->id,
                'valid' => $params['order']->valid,
                'status' => 'ok'
            ));
        }

        if (isset($params['order']->reference) && !empty($params['order']->reference)) {
            $this->smarty->assign('reference', $params['order']->reference);
        }
        $this->smarty->assign(array(
            'shop_name' => $this->context->shop->name,
            'reference' => $params['order']->reference,
            'contact_url' => $this->context->link->getPageLink('contact', true),
            'status' => 'ok'
        ));

        return $this->fetch('module:qentacheckoutseamless/views/templates/hook/payment_return.tpl');
    }

    /**
     * return all enabled paymenttypes
     *
     * @param $cart
     *
     * @return array
     */
    private function getEnabledPaymentTypes($cart)
    {
        $lang = Language::getIsoById($cart->id_lang);
        if (!in_array($lang, array('de', 'en'))) {
            $lang = 'en';
        }

        $paymentTypes = array();

        foreach ($this->getPaymentTypes() as $paymentType) {

            /** @var QentaCheckoutSeamlessPayment $paymentType */
            if (!$paymentType->isAvailable($cart)) {
                continue;
            }

            $type = array(
                'payment' => $paymentType,
                'name' => $paymentType->getName(),
                'method' => $paymentType->getMethod(),
                'label' => $this->l($paymentType->getLabel()),
                'template' => $paymentType->getTemplate(),
                'img' => Media::getMediaPath(
                    dirname(__FILE__) . '/views/img/paymenttypes/' . $paymentType->getLogo($lang)
                )
            );

            $paymentTypes[] = $type;
        }

        return $paymentTypes;
    }

    /**
     * return paymenttype objects
     *
     * @param null $paymentType
     *
     * @return array
     */
    private function getPaymentTypes($paymentType = null)
    {
        $types = array();
        foreach ($this->config as $group) {
            foreach ($group['fields'] as $f) {
                if (array_key_exists('class', $f)) {
                    if ($paymentType !== null && $f['name'] != $paymentType) {
                        continue;
                    }
                    $className = 'QentaCheckoutSeamlessPayment' . $f['class'];
                    $f['group'] = 'pt';

                    $pt = new $className($this, $f, $this->getTransaction());
                    $types[] = $pt;
                }
            }
        }
        return $types;
    }

    /**
     * get transaction management object
     *
     * @return WirecardCheckoutSeamlessTransaction
     */
    private function getTransaction()
    {
        if (!isset($this->transaction)) {
            $this->transaction = new QentaCheckoutSeamlessTransaction();
        }

        return $this->transaction;
    }

    /**
     * log with PrestaShopLogger (contains date and severity)
     * @param $text
     * @param int $severity
     * @param null $id_employee
     */
    public function log($text, $severity = 1, $id_employee = null)
    {
        $log = new PrestaShopLogger();
        $log->severity = (int)$severity;
        $log->error_code = null;
        $log->message = $text;
        $log->date_add = date('Y-m-d H:i:s');
        $log->date_upd = date('Y-m-d H:i:s');

        if (isset(Context::getContext()->employee) && Validate::isLoadedObject(Context::getContext()->employee)) {
            $id_employee = Context::getContext()->employee->id;
        }
        if ($id_employee !== null) {
            $log->id_employee = (int)$id_employee;
        }

        $log->add();
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $controllerArray = array('order');
        if (in_array($this->context->controller->php_self, $controllerArray)) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-style',
                'modules/' . $this->name . '/views/css/style.css',
                array(
                    'media' => 'all',
                    'priority' => 200,
                )
            );

            $this->context->controller->registerJavascript(
                'module-qcs-simple-lib',
                'modules/' . $this->name . '/views/js/scripts.js',
                array(
                    'priority' => 202,
                    'attribute' => 'async',
                )
            );

            $this->context->controller->registerJavascript(
                'module-qcs-payment',
                'modules/' . $this->name . '/views/js/payment.js',
                array(
                    'priority' => 201,
                    'attribute' => 'async',
                )
            );
        }
    }


    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return false;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return false;
        }

        unset($this->context->cookie->qcsRedirectUrl);

        $timestamp = microtime();
        $customerId = $this->getConfigValue('basicdata', 'customer_id');
        $consumerDeviceId = md5($customerId . "_" . $timestamp);

        if (!isset($this->context->cookie->qcsConsumerDeviceId)) {
            $this->context->cookie->qcsConsumerDeviceId = $consumerDeviceId;
        }

        if ((Configuration::get('QCS_PT_INVOICE_PROVIDER') == 'ratepay' && (bool)Configuration::get('QCS_PT_INVOICE')) ||
            (Configuration::get('QCS_PT_INSTALLMENT_PROVIDER') == 'ratepay' && (bool)Configuration::get('QCS_PT_INSTALLMENT'))
        ) {
            echo "<script language='JavaScript'>
                var di = {t:'" . $this->context->cookie->qcsConsumerDeviceId . "',v:'WDWL',l:'Checkout'};
              </script>
              <script type='text/javascript' src='//d.ratepay.com/" . $this->context->cookie->qcsConsumerDeviceId . "/di.js'></script>
              <noscript>
              <link rel='stylesheet' type='text/css' href='//d.ratepay.com/di.css?t=" . $this->context->cookie->qcsConsumerDeviceId . "&v=WDWL&l=Checkout'>
            </noscript>
            <object type='application/x-shockwave-flash' data='//d.ratepay.com/WDWL/c.swf' width='0' height='0'>
                <param name='movie' value='//d.ratepay.com/WDWL/c.swf' />
                <param name='flashvars' value='t=" . $this->context->cookie->qcsConsumerDeviceId . "&v=WDWL'/>
                <param name='AllowScriptAccess' value='always'/>
            </object>";
        }

        $paymentTypes = $this->getEnabledPaymentTypes($params['cart']);

        $result = array();

        $dsModel = new QentaCheckoutSeamlessDataStorage($this);
        $cart = new Cart($this->context->cookie->id_cart);
        $jsUrl = "";

        try {
            $response = $dsModel->init($cart);

            if (!$response->hasFailed()) {
                $this->log(__METHOD__ . ':storageid:' . $response->getStorageId());
                $this->log(__METHOD__ . ':jsurl:' . $response->getJavascriptUrl());

                $jsUrl =  $response->getJavascriptUrl();

                $this->context->cookie->qcsStorageId = $response->getStorageId();
                $this->context->cookie->write();
            } else {
                $dsErrors = $response->getErrors();
                $this->log(
                    __METHOD__ . ':storage init failed:' . print_r(
                        $dsErrors,
                        true
                    )
                );

                return false;
            }
        } catch (Exception $e) {
            $this->log(__METHOD__ . ':' . $e->getMessage());

            return false;
        }

        $urlIncluded = false;
        foreach ($paymentTypes as $paymentType) {
            $payment = new PaymentOption();
            $payment->setLogo($paymentType['img'])
                ->setCallToActionText($this->l('Pay using') . ' ' . $this->l($paymentType['label']));

            $paymentType['template'] = 'module:qentacheckoutseamless/views/templates/hook/methods/' . $paymentType['template'];
            $this->context->smarty->assign(
                array(
                    'current' => $paymentType,
                    'days' => Tools::dateDays(),
                    'months' => Tools::dateMonths(),
                    'years' => Tools::dateYears(),
                    'jsUrl' => $urlIncluded ? false : $jsUrl,
                    'action' => $this->context->link->getModuleLink(
                        $this->name,
                        'paymentExecution',
                        array(
                            'paymentType' => $paymentType['name'],
                            'paymentName' => $paymentType['label']
                        ),
                        true
                    )
                )
            );

            if ($paymentType['template'] !== null) {
                $urlIncluded = true;
                $payment->setBinary(true);
                $payment->setForm($this->context->smarty->fetch('module:qentacheckoutseamless/views/templates/hook/payment_eu.tpl')); //methods/'.$paymentType['template']));
            }

            $result[] = $payment;
        }

        return count($result) ? $result : false;
    }

    /**
     * order information page after checkout return
     *
     * @param $params
     *
     * @return string|void
     */
    public function hookDisplayPaymentReturn($params)
    {
        if (!$this->active) {
            return '';
        }

        $id_tx = (int)Tools::getValue('id_tx');

        unset($this->context->cookie->qcsRedirectUrl);

        $txData = $this->getTransaction()->get($id_tx);
        if (!is_array($txData)) {
            $this->log(__METHOD__ . ":tx data for id: $id_tx not found");

            return $this->display(__FILE__, 'payment_return.tpl');
        }

        if ($txData['paymentstate'] == WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS) {
            $this->smarty->assign(
                array(
                    'status' => 'ok'
                )
            );

            return $this->display(__FILE__, 'payment_return.tpl');
        }

        if ($txData['paymentstate'] == WirecardCEE_QMore_ReturnFactory::STATE_PENDING) {
            $this->smarty->assign(
                array(
                    'status' => 'ok'
                )
            );

            return $this->display(__FILE__, 'pending.tpl');
        }

        $params = array();
        // order has been created before payment
        // we need to reorder
        if ($txData['id_order']) {
            $params = array(
                'submitReorder' => true,
                'id_order' => (int)$txData['id_order']
            );
        }

        $cart = new Cart($txData['id_cart']);

        if (Configuration::get('PS_ORDER_PROCESS_TYPE')) {
            Tools::redirect(
                $this->context->link->getPageLink('order-opc', true, $cart->id_lang, $params)
            );
        }

        $this->context->cookie->qcsMessage = $this->l('An error occurred during the payment process.');

        Tools::redirect(
            $this->context->link->getPageLink('order', true, $cart->id_lang, $params)
        );

        return '';
    }

    /**
     * add gateway reference number to invoice
     *
     * @param $params
     *
     * @return string
     */
    public function hookDisplayPDFInvoice($params)
    {
        $invoice = $params['object'];

        $msg = $this->getPaymentMessage($invoice->id_order);

        if (preg_match("/paymentType: *([^;]+);.*gatewayReferenceNumber: *([^;]+)/i", $msg, $matches)) {
            $paymentType = $matches[1];
            $gatewayReferenceNumber = $matches[2];
        } else {
            return '';
        }

        $ret = sprintf(
            $this->l(
                'Your Paymenttype is %s. Please use this number %s as reference for your bank account transactions.'
            ),
            $this->l($paymentType),
            $gatewayReferenceNumber
        );

        return $ret;
    }

    /**
     * return payment message from order
     *
     * @param $id_order
     *
     * @return false|null|string
     *
     */
    private function getPaymentMessage($id_order)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT `message`
                 FROM `' . _DB_PREFIX_ . 'message`
             WHERE `id_order` = ' . (int)$id_order . '
                 AND private = 1
                 AND message LIKE \'%paymentType%\'
             ORDER BY `id_message`
        '
        );
    }

    /**
     * add CSS/JS to admin pages
     */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJquery();
        $this->context->controller->addJqueryPlugin('fancybox');

        $this->context->controller->addCSS($this->_path . 'views/css/admin/styles.css');
    }

    /**
     * display error message after checkout failure
     */
    public function hookDisplayHeader()
    {
        $controllerArray = array('order');

        $context = Context::getContext();

        if (
            in_array($context->controller->php_self, $controllerArray)
            && $context->cookie->qcsMessage
        ) {
            if (strpos($context->cookie->qcsMessage, "<br />")) {
                $msgs = explode("<br />", $context->cookie->qcsMessage);
                foreach ($msgs as $msg) {
                    if (Tools::strlen($msg) < 5) {
                        continue;
                    }
                    $context->controller->errors[] = Tools::displayError(html_entity_decode($msg));
                }
            } else {
                $context->controller->errors[] = Tools::displayError(html_entity_decode($context->cookie->qcsMessage));
            }
            unset($context->cookie->qcsMessage);
        }
    }

    /**
     * initiate payment
     *
     * @param $paymentTypeName
     * @param $additionalData
     *
     * @throws Exception
     */
    public function initiatePayment($paymentTypeName, $additionalData)
    {
        if (!$this->context->cookie->qcsRedirectUrl) {
            $paymentType = $this->getPaymentType($paymentTypeName);
            if ($paymentType === null) {
                throw new Exception($this->l('This payment method is not available.'));
            }

            if (!$this->context->cookie->id_cart) {
                throw new Exception($this->l('Unable to load basket.'));
            }

            $id_cart = $this->context->cookie->id_cart;
            $cart = new Cart($id_cart);

            if (
                isset($additionalData['birthdate'])
                && preg_match(
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',
                    $additionalData['birthdate']
                )
            ) {
                $c = new Customer($cart->id_customer);
                $c->birthday = $additionalData['birthdate'];
                $c->save();
            }

            if (!$paymentType->isAvailable($cart)) {
                throw new Exception($this->l('Payment method not enabled.'));
            }

            $id_order = null;
            try {
                if ($this->getConfigValue('options', 'order_creation') == 'before') {
                    $id_order = $this->getOrderManagement()->createOder($cart, $this->getAwaitingState());
                    $this->getOrderManagement()->updatePaymentInformation($id_order, $paymentType);

                    $initResponse = $paymentType->initiate($id_cart, $id_order, $additionalData);
                } else {
                    $initResponse = $paymentType->initiate($id_cart, null, $additionalData);
                }

                unset($this->context->cookie->qcsConsumerDeviceId);

                if ($initResponse->getStatus() == \WirecardCEE_QMore_Response_Initiation::STATE_FAILURE) {
                    $message = $this->l('An error occurred during the payment process');
                    if ($initResponse->getNumberOfErrors() > 0) {
                        $msg = implode(
                            ',',
                            array_map(
                                function ($e) {
                                    /** @var \WirecardCEEQMoreError $e */
                                    return $e->getConsumerMessage();
                                },
                                $initResponse->getErrors()
                            )
                        );

                        if (Tools::strlen($msg)) {
                            $message = $msg;
                        }

                        $this->log(__METHOD__ . ':' . $msg);
                    }

                    $params = array();
                    if ($id_order !== null) {
                        $this->getOrderManagement()->setOrderState($id_order, _PS_OS_ERROR_);
                        $params = array(
                            'submitReorder' => true,
                            'id_order' => (int)$id_order
                        );
                    }
                    $this->context->cookie->qcsMessage = $message;
                    Tools::redirect(
                        $this->context->link->getPageLink('order', true, $cart->id_lang, $params)
                    );
                }

                $this->context->cookie->qcsRedirectUrl = $initResponse->getRedirectUrl();
                $this->context->cookie->write();
            } catch (Exception $e) {
                $params = array();
                if ($id_order !== null) {
                    $this->getOrderManagement()->setOrderState($id_order, _PS_OS_ERROR_);
                    $params = array(
                        'submitReorder' => true,
                        'id_order' => (int)$id_order
                    );
                }
                $this->context->cookie->qcsMessage = $this->l('An error occurred during the payment process');
                $this->log(__METHOD__ . ':' . $e->getMessage());
                $this->log(__METHOD__ . ':' . $e->getTraceAsString());

                Tools::redirect(
                    $this->context->link->getPageLink('order', true, $cart->id_lang, $params)
                );
            }
        }

        if ($this->getPaymentType($paymentTypeName)->getPaymentMethod() == WirecardCEE_Stdlib_PaymentTypeAbstract::SOFORTUEBERWEISUNG) {
            Tools::redirect($this->context->cookie->qcsRedirectUrl);
        } else {
            Tools::redirect($this->context->link->getModuleLink($this->name, 'paymentIFrame'));
        }
    }

    /**
     * @param $paymentType
     *
     * @return QentaCheckoutSeamlessPayment |null
     */
    private function getPaymentType($paymentType)
    {
        $found = $this->getPaymentTypes($paymentType);
        if (count($found) != 1) {
            return null;
        }

        return $found[0];
    }

    /**
     * get order management object
     *
     * @return WirecardCheckoutSeamlessOrderManagement
     */
    private function getOrderManagement()
    {
        if (!isset($this->orderManagement)) {
            $this->orderManagement = new QentaCheckoutSeamlessOrderManagement($this);
        }

        return $this->orderManagement;
    }

    /**
     * get awaiting order state
     *
     * @return string
     */
    public function getAwaitingState()
    {
        return Configuration::get(self::QCS_OS_AWAITING);
    }

    /**
     * handle confirm (server2server request) response
     *
     * @return string
     * @throws PrestaShopDatabaseException
     */
    public function confirmResponse()
    {
        if (!$this->active) {
            return (WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString($this->l("Module is not active!")));
        }

        $response = Tools::file_get_contents('php://input');

        $this->log(__METHOD__ . ':raw:' . $response);

        $return = null;
        try {
            $return = WirecardCEE_QMore_ReturnFactory::getInstance(
                $response,
                $this->getConfigValue('basicdata', 'secret')
            );

            if (!$return->validate()) {
                throw new \Exception('Validation error: invalid response');
            }

            $this->log(
                __METHOD__ . ':' . print_r(
                    $return->getReturned(),
                    true
                )
            );

            if (!Tools::strlen($return->psQcsTxId)) {
                throw new \Exception('QENTA transaction id is missing');
            }

            $transactionData = $this->getTransaction()->get($return->psQcsTxId);
            if ($transactionData === false) {
                throw new \Exception('Transaction data not found: ' . $return->psQcsTxId);
            }

            $paymentType = $this->getPaymentType($transactionData['paymentname']);
            if ($paymentType === null) {
                throw new \Exception('Paymenttype not found: ' . $transactionData['paymentname']);
            }

            $this->getOrderManagement()->processOrder($paymentType, $transactionData, $return);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . ':' . $e->getMessage());
            $this->log(__METHOD__ . ':' . $e->getTraceAsString());

            if ($return !== null && Tools::strlen($return->psQcsTxId)) {
                $this->getTransaction()->updateTransaction(
                    $return->psQcsTxId,
                    array('status' => 'error', 'message' => $e->getMessage())
                );
            }

            return (WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString($e->getMessage()));
        }

        return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString();
    }

    /**
     * return to shop from checkout page
     *
     * @return string|void
     * @throws Exception
     */
    public function back()
    {
        if (!$this->active) {
            return false;
        }

        if (!Tools::getIsset('id_cart') || !Tools::getIsset('id_module') || !Tools::getIsset('id_tx')) {
            throw new \Exception('Invalid Request. moduleId, cartId, txId not set');
        }

        $transactionData = $this->getTransaction()->get((int)Tools::getValue('id_tx'));
        if ($transactionData === false) {
            throw new \Exception('Transaction data not found: ' . Tools::getValue('id_tx'));
        }

        unset($this->context->cookie->qcsRedirectUrl);

        $cart = new Cart($transactionData['id_cart']);

        if (
            $transactionData['paymentstate'] == WirecardCEE_QMore_ReturnFactory::STATE_FAILURE ||
            $transactionData['paymentstate'] == WirecardCEE_QMore_ReturnFactory::STATE_CANCEL
        ) {
            $this->context->cookie->qcsMessage = html_entity_decode($transactionData['message']);

            $page = 'order';
            if (Configuration::get('PS_ORDER_PROCESS_TYPE')) {
                $page = 'order-opc';
            }

            $params = array();
            if ($transactionData['id_order']) {
                $params = array(
                    'submitReorder' => true,
                    'id_order' => (int)$transactionData['id_order']
                );
            }

            $this->smarty->assign(
                array(
                    'orderConfirmation' => $this->context->link->getPageLink(
                        $page,
                        true,
                        $cart->id_lang,
                        $params
                    ),
                    'this_path' => _THEME_CSS_DIR_
                )
            );

            return $this->display(__FILE__, 'back.tpl');
        }


        $id_order = (int)$transactionData['id_order'];
        $params = array(
            'id_cart' => (int)Tools::getValue('id_cart'),
            'id_module' => (int)Tools::getValue('id_module'),
            'id_order' => $id_order,
            'id_tx' => (int)Tools::getValue('id_tx'),
            'key' => Tools::getValue('key', null)
        );

        $this->smarty->assign(
            array(
                'orderConfirmation' =>
                $this->context->link->getPageLink(
                    'order-confirmation',
                    true,
                    null,
                    $params
                ),
                'this_path' => _THEME_CSS_DIR_
            )
        );

        return $this->display(__FILE__, 'back.tpl');
    }

    /**
     * return module display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * get fraud order state
     *
     * @return string
     */
    public function getFraudState()
    {
        return Configuration::get(self::QCS_OS_FRAUD);
    }

    /**
     * build cart hash, used to detect cart modifications during checkout
     * @param Cart $cart
     *
     * @return string
     */
    public function computeCartHash(Cart $cart)
    {
        return sha1($cart->getOrderTotal() + $cart->id_currency + $cart->id_customer + count($cart->getProducts()));
    }

    /**
     * return data store storage id
     *
     * @return array
     */
    public function getStorageId()
    {
        return $this->context->cookie->qcsStorageId;
    }

    /**
     * return datastorage return url
     *
     * @return array
     */
    public function getDataStorageReturnUrl()
    {
        return $this->context->link->getModuleLink($this->name, 'dataStorageReturn', array(), true);
    }

    /**
     * return duplicate request check option
     *
     * @return bool
     */
    public function getDuplicateRequestCheck()
    {
        return true;
    }

    /**
     * @param Cart $cart
     * @param $id_tx
     *
     * @return string
     */
    public function getReturnUrl($cart, $id_tx)
    {
        $params = array(
            'id_cart' => (int)$cart->id,
            'id_module' => (int)$this->id,
            'id_tx' => (int)$id_tx,
            'key' => $cart->secure_key
        );

        return $this->context->link->getModuleLink($this->name, 'back', $params, true);
    }

    /**
     * get context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getPaymentTranslations()
    {
        return array(
            'minAgeMessage' => $this->l('You have to be %d years or older to use this payment.'),
            'consentErrorMessage' => $this->l('Please accept the consent terms!'),
            'consentTxt' => $this->l('I agree that the data which are necessary for the liquidation of invoice payments and which are used to complete the identity and credit check are transmitted to payolution.  My %s can be revoked at any time with future effect.'),
            'consent' => $this->l('consent')
        );
    }

    /**
     * return available currency iso codes
     *
     * @return array
     */
    protected function getCurrencies()
    {
        $currencies = Currency::getCurrencies();
        $ret = array();
        foreach ($currencies as $currency) {
            $ret[] = array(
                'key' => $currency['iso_code'],
                'value' => $currency['name']
            );
        }

        return $ret;
    }

    /**
     * return available country iso codes
     *
     * @return array
     */
    protected function getCountries()
    {
        $cookie = $this->context->cookie;
        $countries = Country::getCountries($cookie->id_lang);
        $ret = array();
        foreach ($countries as $country) {
            $ret[] = array(
                'key' => $country['iso_code'],
                'value' => $country['name']
            );
        }

        return $ret;
    }

    /**
     * return available usergroups iso codes
     *
     * @return array
     */
    protected function getUserGroups()
    {
        $cookie = $this->context->cookie;
        $groups = Group::getGroups($cookie->id_lang);
        $visitor_group = Configuration::get('PS_UNIDENTIFIED_GROUP');
        $guest_group = Configuration::get('PS_GUEST_GROUP');
        $cust_group = Configuration::get('PS_CUSTOMER_GROUP');
        $ret = array();
        foreach ($groups as $g) {
            // exclude standard groups
            if (in_array(
                $g['id_group'],
                array($visitor_group, $guest_group, $cust_group)
            )) {
                continue;
            }

            $ret[] = array('key' => $g['id_group'], 'value' => $g['name']);
        }

        return $ret;
    }

    /**
     * return options for installment providers select
     *
     * @return array
     */
    protected function getInstallmentProviders()
    {
        return array(
            array('key' => 'payolution', 'value' => 'payolution'),
            array('key' => 'ratepay', 'value' => 'RatePay'),
        );
    }

    /**
     * return options for invoice providers select
     *
     * @return array
     */
    protected function getInvoiceProviders()
    {
        return array(
            array('key' => 'payolution', 'value' => 'payolution'),
            array('key' => 'ratepay', 'value' => 'RatePay'),
        );
    }

    /**
     * return options for transactionid config select
     *
     * @return array
     */
    private function getTransactionIdOptions()
    {
        return array(
            array(
                'key' => 'orderNumber',
                'value' => $this->l('QENTA order number')
            ),
            array(
                'key' => 'gatewayReferenceNumber',
                'value' => $this->l('Gateway reference number')
            )
        );
    }

    /**
     * return options for order creation config select
     *
     * @return array
     */
    private function getOrderCreationOptions()
    {
        return array(
            array('key' => 'before', 'value' => $this->l('Always')),
            array(
                'key' => 'after',
                'value' => $this->l('Only for successful payments')
            ),
        );
    }

    /**
     * return options for configuration modes select
     *
     * @return array
     */
    private function getConfigurationModes()
    {
        return array(
            array('key' => 'production', 'value' => $this->l('Production')),
            array('key' => 'demo', 'value' => $this->l('Demo')),
            array('key' => 'test', 'value' => $this->l('Test')),
            array('key' => 'test3d', 'value' => $this->l('Test 3D'))
        );
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }
}
