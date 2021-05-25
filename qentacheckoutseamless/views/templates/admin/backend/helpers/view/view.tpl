{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
    <script type="text/javascript">
    </script>
    <div class="col-lg-8">
        <div class="panel">

            <h3><i class="icon-group"></i> {l s='Transaction information' mod='qentacheckoutseamless'}</h3>


            <div class="form-horizontal">
                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Order:' mod='qentacheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static"><a href="{$orderLink|escape:'htmlall':'UTF-8'}">{$order->reference|escape:'htmlall':'UTF-8'}</a>
                        </p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Status:' mod='qentacheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->status|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Payment method:' mod='qentacheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->paymentmethod|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Payment state:' mod='qentacheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->paymentstate|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Amount:' mod='qentacheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{displayPrice price=$transaction->amount}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Order number:' mod='qentacheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->ordernumber|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Gateway reference number:' mod='qentacheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->gatewayreference|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-3 control-label">{l s='Created:' mod='qentacheckoutseamless'}</label>
                    <div class="col-lg-3"><p class="form-control-static">{$transaction->created|escape:'htmlall':'UTF-8'}</p></div>
                </div>

                <div class="form-group">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-3">
                        <button class="btn btn-default" id="qcs-open-transaction-details">
                            <i class="icon-search"></i>
                            {l s='Details' mod='qentacheckoutseamless'}
                        </button>
                    </div>
                </div>

                <div id="qcs-transaction-details" style="display: none;">

                    <div class="form-group">
                        <label class="col-lg-3 control-label">{l s='Message:' mod='qentacheckoutseamless'}</label>
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
                              action="{$current_index|escape:'htmlall':'UTF-8'}&amp;viewqenta_checkout_seamless_tx&amp;id_tx={$transaction->id|escape:'htmlall':'UTF-8'}&amp;token={$smarty.get.token|escape:'htmlall':'UTF-8'}">
                            <input type="hidden" name="amount" class="qcs-amount"/>
                            {foreach from=$operations item=op}
                                {if $op == "DEPOSIT" or $op == "REFUND"}
                                    <input type="text" name="amount-transaction" value=""
                                           autocomplete="off"
                                           id="qcs-amount-transaction"
                                           class="form-control fixed-width-sm pull-left"/>
                                {/if}
                                <button class="btn btn-primary qcs-payment-ops" type="submit"
                                        name="submitQcsBackendOp"
                                        data-payment=""
                                        data-amount-fieldid="qcs-amount-transaction"
                                        value="{$op|escape:'htmlall':'UTF-8'}">
                                    {l s=$op mod='qentacheckoutseamless'}
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
                    {l s='Payments' mod='qentacheckoutseamless'} <span class="badge">{$payments|@count|escape:'htmlall':'UTF-8'}</span>
                </div>

                <form id="formPaymentOp" method="post"
                      action="{$current_index|escape:'htmlall':'UTF-8'}&amp;viewqenta_checkout_seamless_tx&amp;id_tx={$transaction->id|escape:'htmlall':'UTF-8'}&amp;token={$smarty.get.token|escape:'htmlall':'UTF-8'}">
                    <input type="hidden" name="paymentnumber" id="qcs-paymentnumber"/>
                    <input type="hidden" name="amount" class="qcs-amount"/>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th><span class="title_box ">{l s='Number' mod='qentacheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Date' mod='qentacheckoutseamless'}</span></th>
                                <th>
                                    <span class="title_box ">{l s='Gateway reference' mod='qentacheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Payment state' mod='qentacheckoutseamless'}</span></th>
                                <th><span class="title_box ">{l s='Approved' mod='qentacheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Deposited' mod='qentacheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Operations' mod='qentacheckoutseamless'}</span>
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
                                                       id="qcs-amount-{$payment->getPaymentNumber()|escape:'htmlall':'UTF-8'}"
                                                       class="form-control fixed-width-sm pull-left"
                                                        value="{$payment->getApproveAmount()|intval}"/>
                                            {/if}
                                            <button class="btn btn-primary qcs-payment-ops" type="submit"
                                                    name="submitQcsBackendOp"
                                                    data-payment="{$payment->getPaymentNumber()|escape:'htmlall':'UTF-8'}"
                                                    data-amount-fieldid="qcs-amount-{$payment->getPaymentNumber()|escape:'htmlall':'UTF-8'}"
                                                    value="{$op|escape:'htmlall':'UTF-8'}">
                                                {l s=$op mod='qentacheckoutseamless'}
                                            </button>
                                        {/foreach}
                                    </td>
                                </tr>
                                {foreachelse}
                                <tr>
                                    <td class="list-empty hidden-print" colspan="6">
                                        <div class="list-empty-msg">
                                            <i class="icon-warning-sign list-empty-icon"></i>
                                            {l s='No payments are available' mod='qentacheckoutseamless'}
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
                    {l s='Credits' mod='qentacheckoutseamless'} <span class="badge">{$credits|@count|escape:'htmlall':'UTF-8'}</span>
                </div>

                <form id="formCreditOp" method="post"
                      action="{$current_index|escape:'htmlall':'UTF-8'}&amp;viewqenta_checkout_seamless_tx&amp;id_tx={$transaction->id|escape:'htmlall':'UTF-8'}&amp;token={$smarty.get.token|escape:'htmlall':'UTF-8'}">
                    <input type="hidden" name="creditnumber" id="qcs-creditnumber"/>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th><span class="title_box ">{l s='Number' mod='qentacheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Date' mod='qentacheckoutseamless'}</span></th>
                                <th>
                                    <span class="title_box ">{l s='Gateway reference' mod='qentacheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box ">{l s='Payment state' mod='qentacheckoutseamless'}</span></th>
                                <th><span class="title_box ">{l s='Amount' mod='qentacheckoutseamless'}</span>
                                </th>
                                <th><span class="title_box "></span>
                                </th>
                                <th><span class="title_box ">{l s='Operations' mod='qentacheckoutseamless'}</span>
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
                                            <button class="btn btn-primary qcs-payment-ops" type="submit"
                                                    name="submitQcsBackendOp"
                                                    data-credit="{$credit->getCreditNumber()|escape:'htmlall':'UTF-8'}"
                                                    data-amount-fieldid=""
                                                    value="{$op|escape:'htmlall':'UTF-8'}">
                                                {l s=$op mod='qentacheckoutseamless'}
                                            </button>
                                        {/foreach}
                                    </td>
                                </tr>
                                {foreachelse}
                                <tr>
                                    <td class="list-empty hidden-print" colspan="6">
                                        <div class="list-empty-msg">
                                            <i class="icon-warning-sign list-empty-icon"></i>
                                            {l s='No credits are available' mod='qentacheckoutseamless'}
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

            $('.qcs-payment-ops').on('click', function () {
                var paymentnumber = $(this).data('payment');
                if (paymentnumber) {
                    $('#qcs-paymentnumber').val(paymentnumber);
                }

                var creditnumber = $(this).data('credit');
                if (creditnumber) {
                    $('#qcs-creditnumber').val(creditnumber);
                }

                var amountFieldId = '#' + $(this).data('amount-fieldid');
                $('.qcs-amount').val($(amountFieldId).val());
            });

            $('#qcs-open-transaction-details').on('click', function () {
                $('#qcs-transaction-details').toggle('display');
            });
        });


    </script>
{/block}
