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


{if $current.payment->getPci3DssSaqAEnable()}

    <div id="pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_iframe" class="wcs_iframe"></div>

    <script type="text/javascript">
        var wirecardCee = new WirecardCEE_DataStorage;
        {if $current.name=='creditcard'}
        wirecardCee.buildIframeCreditCard('pt_wirecardcheckoutseamless_creditcard_iframe', '100%', '450px');
        {/if}
        {if $current.name=='maestro'}
        wirecardCee.buildIframeMaestro('pt_wirecardcheckoutseamless_maestro_iframe', '100%', '450px');
        {/if}
        {if $current.name=='creditcardmoto'}
        wirecardCee.buildIframeCreditCardMoto('pt_wirecardcheckoutseamless_creditcardmoto_iframe', '100%', '450px');
        {/if}
    </script>

{else}

    {if $current.payment->getDisplaycardholder()}
        <div class="form-group">
            <label>{l s='Card holder' mod='wirecardceecheckoutseamless'}</label>
            <input type="text" name="cardholder" autocomplete="off" class="form-control cardholder"
                   placeholder="{l s={$current.payment->getCardholderPlaceholderText()|escape:'htmlall':'UTF-8'} mod='wirecardceecheckoutseamless'}"
                   data-wcs-fieldname="cardholdername"/>
        </div>
    {/if}
    <div class="form-group">
        <label> {l s='Credit card number' mod='wirecardceecheckoutseamless'}</label>
        <input type="tel" name="cardnumber" autocomplete="off" class="form-control cardnumber is_required wcs-validate"
               placeholder="{l s={$current.payment->getPanPlaceholderText()|escape:'htmlall':'UTF-8'} mod='wirecardceecheckoutseamless'}"
               data-wcs-fieldname="pan"/>
    </div>
    <div class="form-group">
        <label> {l s='Expiration date' mod='wirecardceecheckoutseamless'}</label>
        <div class="row">
            <div class="col-xs-2">
                <select name="pt_wcs_{$current.name|escape:'htmlall':'UTF-8'}_expirationMonth" class="form-control month is_required wcs-validate"
                        data-wcs-fieldname="expirationMonth">
                    {foreach $current.payment->getMonths() as $k => $v }
                        <option value="{$k|intval}"{if 0} selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-xs-3">
                <select name="pt_wcs_{$current.name|escape:'htmlall':'UTF-8'}_expirationYear" class="form-control year is_required wcs-validate"
                        data-wcs-fieldname="expirationYear">
                    {foreach $current.payment->getYears() as $k => $v }
                        <option value="{$k|intval}"{if 0} selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>{l s='Card verification code' mod='wirecardceecheckoutseamless'}</label>
        <input type="tel" name="cvc" autocomplete="off" class="form-control cvc"
               placeholder="{l s={$current.payment->getCvcPlaceholderText()|escape:'htmlall':'UTF-8'} mod='wirecardceecheckoutseamless'}"
               data-wcs-fieldname="cardVerifyCode"/>
    </div>
    {if $current.payment->getDisplayIssueNumberField()}
        <div class="form-group">
            <label>{l s='Card issue number' mod='wirecardceecheckoutseamless'}</label>
            <input type="text" name="issuenumber" autocomplete="off" class="form-control issuenumber"
                   data-wcs-fieldname="issueNumber"/>
        </div>
    {/if}

    {if $current.payment->getDisplayIssueDateField()}
        <div class="form-group">
            <label>{l s='Issue date' mod='wirecardceecheckoutseamless'}</label>
            <div class="row">
                <div class="col-sm-1">
                    <select name="pt_wcs_{$current.name|escape:'htmlall':'UTF-8'}_issueMonth" class="form-control month"
                            data-wcs-fieldname="issueMonth">
                        {foreach $current.payment->getMonths() as $k => $v }
                            <option value="{$k|intval}"{if 0} selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-sm-2">
                    <select name="pt_wcs_{$current.name|escape:'htmlall':'UTF-8'}_issueYear" class="form-control year"
                            data-wcs-fieldname="issueYear">
                        {foreach $current.payment->getIssueYears() as $k => $v }
                            <option value="{$k|intval}"{if 0} selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    {/if}

{/if}
