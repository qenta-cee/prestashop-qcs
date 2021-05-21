{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}
{if !$current.payment->isB2B()}
    <div class="required form-group">
        <label class="required"> {l s='Date of Birth' mod='qentacheckoutseamless'}</label>
        <div class="row">
            <input type="hidden" name="birthdate" id="wcs{$current.name|escape:'htmlall':'UTF-8'}birthdate" data-wcs-fieldname="birthdate"/>
            <div class="col-sm-2">
                <select name="days" id="wcs{$current.name|escape:'htmlall':'UTF-8'}day" class="form-control days">
                    <option value="">-</option>
                    {foreach from=$days item=v}
                        <option value="{$v|escape:'htmlall':'UTF-8'}" {if ($sl_day == $v)}selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-sm-2">
                <select name="months" id="wcs{$current.name|escape:'htmlall':'UTF-8'}month" class="form-control months">
                    <option value="">-</option>
                    {foreach from=$months key=k item=v}
                        <option value="{$k|escape:'htmlall':'UTF-8'}" {if ($sl_month == $k)}selected="selected"{/if}>
                            {l s=$v mod='qentacheckoutseamless'}&nbsp;
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="col-sm-3">
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

<ul>
  <li>
    <div class="pull-xs-left">
      <span class="custom-checkbox">
        <input id="wcs{$current.name|escape:'htmlall':'UTF-8'}consent" name="consent" type="checkbox">
        <span><i class="material-icons checkbox-checked">&#xE5CA;</i></span>
      </span>
    </div>
    <div class="condition-label">
      <label class="js-terms" for="consent">
          {(utf8_encode($current.payment->getConsentTxt())) nofilter}
      </label>
    </div>
  </li>
</ul>
{/if}

<script type="text/javascript">
  var wcs{$current.name|escape:'htmlall':'UTF-8'}Validate;
    wcs{$current.name|escape:'htmlall':'UTF-8'}Validate = function(messageBox) {
        var m = $('#wcs{$current.name|escape:'htmlall':'UTF-8'}month').val();
        if (m < 10) m = "0" + m;
        var d = $('#wcs{$current.name|escape:'htmlall':'UTF-8'}day').val();
        if (d < 10) d = "0" + d;

        var dateStr = $('#wcs{$current.name|escape:'htmlall':'UTF-8'}year').val() + '-' + m + '-' + d;
        var minAge = {$current.payment->getMinAge()|intval};
        var msg = '';

        if (!wcsValidateMinAge(dateStr, minAge)) {
            {* escape was causing encoding issues *}
            msg = '{$current.payment->getMinAgeMessage() nofilter}';
            messageBox.append('<li>' + msg + '</li>');
        }

        $('#wcs{$current.name|escape:'htmlall':'UTF-8'}birthdate').val(dateStr);

        {if $current.payment->hasConsent()}

            if (!$('#wcs{$current.name|escape:'htmlall':'UTF-8'}consent').is(':checked')) {
                msg = '{$current.payment->getConsentErrorMessage()|escape:'htmlall':'UTF-8'}';
                messageBox.append('<li>' + msg + '</li>');
            }
        {/if}

        if (msg.length){
          messageBox.parent().show();
          return false;
        }

        return true;
    }
</script>
