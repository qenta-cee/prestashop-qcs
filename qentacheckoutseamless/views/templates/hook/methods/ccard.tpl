{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}


{if $current.payment->getPci3DssSaqAEnable()}

    <div id="pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_iframe" class="qcs_iframe"></div>

    <script type="text/javascript">
        var qenta = new WirecardCEE_DataStorage;
        {if $current.name=='creditcard'}
        qenta.buildIframeCreditCard('pt_qentacheckoutseamless_creditcard_iframe', '100%', '450px');
        {/if}
        {if $current.name=='maestro'}
        qenta.buildIframeMaestro('pt_qentacheckoutseamless_maestro_iframe', '100%', '450px');
        {/if}
        {if $current.name=='creditcardmoto'}
        qenta.buildIframeCreditCardMoto('pt_qentacheckoutseamless_creditcardmoto_iframe', '100%', '450px');
        {/if}
    </script>

{else}

    {if $current.payment->getDisplaycardholder()}
        <div class="form-group">
            <label>{l s='Card holder' mod='qentacheckoutseamless'}</label>
            <input type="text" name="cardholder" autocomplete="off" class="form-control cardholder"
                   placeholder="{l s={$current.payment->getCardholderPlaceholderText()|escape:'htmlall':'UTF-8'} mod='qentacheckoutseamless'}"
                   data-qcs-fieldname="cardholdername"/>
        </div>
    {/if}
    <div class="form-group">
        <label> {l s='Credit card number' mod='qentacheckoutseamless'}</label>
        <input type="tel" name="cardnumber" autocomplete="off" class="form-control cardnumber is_required qcs-validate"
               placeholder="{l s={$current.payment->getPanPlaceholderText()|escape:'htmlall':'UTF-8'} mod='qentacheckoutseamless'}"
               data-qcs-fieldname="pan"/>
    </div>
    <div class="form-group">
        <label> {l s='Expiration date' mod='qentacheckoutseamless'}</label>
        <div class="row">
            <div class="col-xs-2">
                <select name="pt_qcs_{$current.name|escape:'htmlall':'UTF-8'}_expirationMonth" class="form-control month is_required qcs-validate"
                        data-qcs-fieldname="expirationMonth">
                    {foreach $current.payment->getMonths() as $k => $v }
                        <option value="{$k|intval}"{if 0} selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-xs-3">
                <select name="pt_qcs_{$current.name|escape:'htmlall':'UTF-8'}_expirationYear" class="form-control year is_required qcs-validate"
                        data-qcs-fieldname="expirationYear">
                    {foreach $current.payment->getYears() as $k => $v }
                        <option value="{$k|intval}"{if 0} selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>{l s='Card verification code' mod='qentacheckoutseamless'}</label>
        <input type="tel" name="cvc" autocomplete="off" class="form-control cvc"
               placeholder="{l s={$current.payment->getCvcPlaceholderText()|escape:'htmlall':'UTF-8'} mod='qentacheckoutseamless'}"
               data-qcs-fieldname="cardVerifyCode"/>
    </div>
    {if $current.payment->getDisplayIssueNumberField()}
        <div class="form-group">
            <label>{l s='Card issue number' mod='qentacheckoutseamless'}</label>
            <input type="text" name="issuenumber" autocomplete="off" class="form-control issuenumber"
                   data-qcs-fieldname="issueNumber"/>
        </div>
    {/if}

    {if $current.payment->getDisplayIssueDateField()}
        <div class="form-group">
            <label>{l s='Issue date' mod='qentacheckoutseamless'}</label>
            <div class="row">
                <div class="col-sm-1">
                    <select name="pt_qcs_{$current.name|escape:'htmlall':'UTF-8'}_issueMonth" class="form-control month"
                            data-qcs-fieldname="issueMonth">
                        {foreach $current.payment->getMonths() as $k => $v }
                            <option value="{$k|intval}"{if 0} selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-sm-2">
                    <select name="pt_qcs_{$current.name|escape:'htmlall':'UTF-8'}_issueYear" class="form-control year"
                            data-qcs-fieldname="issueYear">
                        {foreach $current.payment->getIssueYears() as $k => $v }
                            <option value="{$k|intval}"{if 0} selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    {/if}

{/if}
