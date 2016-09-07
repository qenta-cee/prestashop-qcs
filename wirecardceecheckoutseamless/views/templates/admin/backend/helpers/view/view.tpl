{*
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
 *}

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
    <script type="text/javascript">
    </script>
    <div class="col-lg-8">
        <div class="panel">

            <h3><i class="icon-group"></i> {l s='Transaction information' mod='wirecardceecheckoutseamless'}</h3>


            <div class="form-horizontal">
                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Order:' mod='wirecardceecheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static"><a href="{$orderLink|escape:'htmlall':'UTF-8'}">{$order->reference|escape:'htmlall':'UTF-8'}</a>
                        </p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Status:' mod='wirecardceecheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->status|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Payment method:' mod='wirecardceecheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->paymentmethod|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Payment state:' mod='wirecardceecheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->paymentstate|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Amount:' mod='wirecardceecheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{displayPrice price=$transaction->amount}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Order number:' mod='wirecardceecheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->ordernumber|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Gateway reference number:' mod='wirecardceecheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->gatewayreference|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Created:' mod='wirecardceecheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->created|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-3">
                        <button class="btn btn-default" id="wcs-open-transaction-details">
                            <i class="icon-search"></i>
                            {l s='Details' mod='wirecardceecheckoutseamless'}
                        </button>
                    </div>
                </div>

                <div id="wcs-transaction-details" style="display: none;">

                    <div class="form-group">
                        <label class="col-lg-3 control-label">{l s='Message:' mod='wirecardceecheckoutseamless'}</label>
                        <div class="col-lg-3"><p class="form-control-static">{$transaction->message|escape:'htmlall':'UTF-8'}</p></div>
                    </div>

                    {foreach from=$response key=k item=v}
                        <div class="form-group">
                            <label class="col-lg-3 control-label">{$k|escape:'htmlall':'UTF-8'}</label>
                            <div class="col-lg-3"><p class="form-control-static">{$v|escape:'htmlall':'UTF-8'}</p></div>
                        </div>
                    {/foreach}
                </div>

                <div class="form-group">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-3">
                        <form id="formPaymentOp" method="post"
                              action="{$current_index|escape:'htmlall':'UTF-8'}&amp;viewwirecard_checkout_seamless_tx&amp;id_tx={$transaction->id|escape:'htmlall':'UTF-8'}&amp;token={$smarty.get.token|escape:'htmlall':'UTF-8'}">
                            <input type="hidden" name="amount" class="wcs-amount"/>
                            {foreach from=$operations item=op}
                                {if $op == "DEPOSIT" or $op == "REFUND"}
                                    <input type="text" name="amount-transaction" value=""
                                           autocomplete="off"
                                           id="wcs-amount-transaction"
                                           class="form-control fixed-width-sm pull-left"/>
                                {/if}
                                <button class="btn btn-primary wcs-payment-ops" type="submit"
                                        name="submitWcsBackendOp"
                                        data-payment=""
                                        data-amount-fieldid="wcs-amount-transaction"
                                        value="{$op|escape:'htmlall':'UTF-8'}">
                                    {l s=$op mod='wirecardceecheckoutseamless'}
                                </button>
                            {/foreach}
                        </form>
                    </div>
                </div>


            </div>


            {* payments block *}
            <div id="formPaymentsPanel" class="panel">
                <div class="panel-heading">
                    <i class="icon-money"></i>
                    {l s='Payments' mod='wirecardceecheckoutseamless'} <span class="badge">{$payments|@count|escape:'htmlall':'UTF-8'}</span>
                </div>

                <form id="formPaymentOp" method="post"
                      action="{$current_index|escape:'htmlall':'UTF-8'}&amp;viewwirecard_checkout_seamless_tx&amp;id_tx={$transaction->id|escape:'htmlall':'UTF-8'}&amp;token={$smarty.get.token|escape:'htmlall':'UTF-8'}">
                    <input type="hidden" name="paymentnumber" id="wcs-paymentnumber"/>
                    <input type="hidden" name="amount" class="wcs-amount"/>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th><span class="title_box ">{l s='Number' mod='wirecardceecheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Date' mod='wirecardceecheckoutseamless'}</span></th>
                                <th>
                                    <span class="title_box ">{l s='Gateway reference' mod='wirecardceecheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Payment state' mod='wirecardceecheckoutseamless'}</span></th>
                                <th><span class="title_box ">{l s='Approved' mod='wirecardceecheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Deposited' mod='wirecardceecheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Operations' mod='wirecardceecheckoutseamless'}</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$payments item=payment}
                                <tr>
                                    <td class="text-right">{$payment->getPaymentNumber()|escape:'htmlall':'UTF-8'}</td>
                                    <td>{dateFormat date=$payment->getTimeCreated()->format('Y-m-d H:i:s') full=true}</td>
                                    <td>{$payment->getGatewayReferencenumber()|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$payment->getState()|escape:'htmlall':'UTF-8'}</td>
                                    <td class="text-right">{displayPrice price=$payment->getApproveAmount()}</td>
                                    <td class="text-right">{displayPrice price=$payment->getDepositAmount()}</td>
                                    <td>

                                        {foreach from=$payment->getOperationsAllowed() item=op}
                                            {if !$op}{continue}{/if}
                                            {if $op == "DEPOSIT" or $op == "REFUND"}
                                                <input type="text"
                                                       name="amount-{$payment->getPaymentNumber()|escape:'htmlall':'UTF-8'}"
                                                       value=""
                                                       autocomplete="off"
                                                       id="wcs-amount-{$payment->getPaymentNumber()|escape:'htmlall':'UTF-8'}"
                                                       class="form-control fixed-width-sm pull-left"
                                                        value="{$payment->getApproveAmount()|intval}"/>
                                            {/if}
                                            <button class="btn btn-primary wcs-payment-ops" type="submit"
                                                    name="submitWcsBackendOp"
                                                    data-payment="{$payment->getPaymentNumber()|escape:'htmlall':'UTF-8'}"
                                                    data-amount-fieldid="wcs-amount-{$payment->getPaymentNumber()|escape:'htmlall':'UTF-8'}"
                                                    value="{$op|escape:'htmlall':'UTF-8'}">
                                                {l s=$op mod='wirecardceecheckoutseamless'}
                                            </button>
                                        {/foreach}
                                    </td>
                                </tr>
                                {foreachelse}
                                <tr>
                                    <td class="list-empty hidden-print" colspan="6">
                                        <div class="list-empty-msg">
                                            <i class="icon-warning-sign list-empty-icon"></i>
                                            {l s='No payments are available' mod='wirecardceecheckoutseamless'}
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}

                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            {* end payments block *}


            {* credits block *}
            <div id="formCreditsPanel" class="panel">
                <div class="panel-heading">
                    <i class="icon-money"></i>
                    {l s='Credits' mod='wirecardceecheckoutseamless'} <span class="badge">{$credits|@count|escape:'htmlall':'UTF-8'}</span>
                </div>

                <form id="formCreditOp" method="post"
                      action="{$current_index|escape:'htmlall':'UTF-8'}&amp;viewwirecard_checkout_seamless_tx&amp;id_tx={$transaction->id|escape:'htmlall':'UTF-8'}&amp;token={$smarty.get.token|escape:'htmlall':'UTF-8'}">
                    <input type="hidden" name="creditnumber" id="wcs-creditnumber"/>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th><span class="title_box ">{l s='Number' mod='wirecardceecheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Date' mod='wirecardceecheckoutseamless'}</span></th>
                                <th>
                                    <span class="title_box ">{l s='Gateway reference' mod='wirecardceecheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Payment state' mod='wirecardceecheckoutseamless'}</span></th>
                                <th><span class="title_box ">{l s='Amount' mod='wirecardceecheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box "></span>
                                </th>
                                <th><span class="title_box ">{l s='Operations' mod='wirecardceecheckoutseamless'}</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$credits item=credit}
                                <tr>
                                    <td class="text-right">{$credit->getCreditNumber()|escape:'htmlall':'UTF-8'}</td>
                                    <td>{dateFormat date=$credit->getTimeCreated()->format('Y-m-d H:i:s') full=true}</td>
                                    <td>{$credit->getGatewayReferencenumber()|escape:'htmlall':'UTF-8'}</td>
                                    <td>{$credit->getState()|escape:'htmlall':'UTF-8'}</td>
                                    <td class="text-right">{displayPrice price=$credit->getAmount()}</td>
                                    <td></td>
                                    <td>

                                        {foreach from=$credit->getOperationsAllowed() item=op}
                                            {if !$op}{continue}{/if}
                                            <button class="btn btn-primary wcs-payment-ops" type="submit"
                                                    name="submitWcsBackendOp"
                                                    data-credit="{$credit->getCreditNumber()|escape:'htmlall':'UTF-8'}"
                                                    data-amount-fieldid=""
                                                    value="{$op|escape:'htmlall':'UTF-8'}">
                                                {l s=$op mod='wirecardceecheckoutseamless'}
                                            </button>
                                        {/foreach}
                                    </td>
                                </tr>
                                {foreachelse}
                                <tr>
                                    <td class="list-empty hidden-print" colspan="6">
                                        <div class="list-empty-msg">
                                            <i class="icon-warning-sign list-empty-icon"></i>
                                            {l s='No credits are available' mod='wirecardceecheckoutseamless'}
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}

                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            {* end credits block *}

        </div>
    </div>
    <script type="text/javascript">

        $(document).ready(function () {

            $('.wcs-payment-ops').on('click', function () {
                var paymentnumber = $(this).data('payment');
                if (paymentnumber) {
                    $('#wcs-paymentnumber').val(paymentnumber);
                }

                var creditnumber = $(this).data('credit');
                if (creditnumber) {
                    $('#wcs-creditnumber').val(creditnumber);
                }

                var amountFieldId = '#' + $(this).data('amount-fieldid');
                $('.wcs-amount').val($(amountFieldId).val());
            });

            $('#wcs-open-transaction-details').on('click', function () {
                $('#wcs-transaction-details').toggle('display');
            });
        });


    </script>
{/block}
