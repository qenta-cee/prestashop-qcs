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

{if !$current.payment->isB2B()}
    <div class="required form-group">
        <label class="required"> {l s='Date of Birth' mod='wirecardceecheckoutseamless'}</label>
        <div class="row">
            <input type="hidden" name="birthdate" id="wcs{$current.name|escape:'htmlall':'UTF-8'}birthdate" data-wcs-fieldname="birthdate"/>
            <div class="col-xs-1">
                <select name="days" id="wcs{$current.name|escape:'htmlall':'UTF-8'}day" class="form-control days">
                    <option value="">-</option>
                    {foreach from=$days item=v}
                        <option value="{$v|escape:'htmlall':'UTF-8'}" {if ($sl_day == $v)}selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-xs-1">
                <select name="months" id="wcs{$current.name|escape:'htmlall':'UTF-8'}month" class="form-control months">
                    <option value="">-</option>
                    {foreach from=$months key=k item=v}
                        <option value="{$k|escape:'htmlall':'UTF-8'}" {if ($sl_month == $k)}selected="selected"{/if}>
                            {l s=$v mod='wirecardceecheckoutseamless'}&nbsp;
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="col-xs-1">
                <select name="years" id="wcs{$current.name|escape:'htmlall':'UTF-8'}year" class="form-control years">
                    <option value="">-</option>
                    {foreach from=$years item=v}
                        <option value="{$v|escape:'htmlall':'UTF-8'}" {if ($sl_year == $v)}selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
{/if}

{if $current.payment->hasConsent()}
    <div class="form-group">
        <input type="checkbox" id="wcs{$current.name|escape:'htmlall':'UTF-8'}consent" name="consent" autocomplete="off" class="form-control"/>
        <label class="consent required"> {$current.payment->getConsentTxt()}</label>
    </div>
{/if}

<script type="text/javascript">
    var wcs{$current.name|escape:'htmlall':'UTF-8'}Validate;
    $(function () {
        wcs{$current.name|escape:'htmlall':'UTF-8'}Validate = function(messageBox) {
            var m = $('#wcs{$current.name|escape:'htmlall':'UTF-8'}month').val();
            if (m < 10) m = "0" + m;
            var d = $('#wcs{$current.name|escape:'htmlall':'UTF-8'}day').val();
            if (d < 10) d = "0" + d;

            var dateStr = $('#wcs{$current.name|escape:'htmlall':'UTF-8'}year').val() + '-' + m + '-' + d;
            var minAge = {$current.payment->getMinAge()|json_encode};
            var msg = '';

            if (!wcsValidateMinAge(dateStr, minAge)) {
                msg = {$current.payment->getMinAgeMessage()|json_encode};
                messageBox.find('ul').append('<li>' + msg + '</li>');
            }

            $('#wcs{$current.name|escape:'htmlall':'UTF-8'}birthdate').val(dateStr);

            {if $current.payment->hasConsent()}

                if (!$('#wcs{$current.name|escape:'htmlall':'UTF-8'}consent').attr('checked')) {
                    msg = {$current.payment->getConsentErrorMessage()|json_encode};
                    messageBox.find('ul').append('<li>' + msg + '</li>');
                }
            {/if}

            if (msg.length){
                messageBox.css('display', 'block');
                return false;
            }

            return true;
        }
    });
</script>