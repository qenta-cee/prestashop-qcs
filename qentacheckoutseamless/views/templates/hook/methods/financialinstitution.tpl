{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}

<div class="required form-group">
    <label class="required"> {l s='Financial institution' mod='qentacheckoutseamless'}</label>

    <select name="pt_qcs_{$current.name|escape:'htmlall':'UTF-8'}_financialInstitution" data-qcs-fieldname="financialinstitution" class="form-control qcs_financialinstitution is_required qcs-validate" >
        {foreach $current.payment->getFinancialInstitutions() as $fi }
            <option value="{$fi.value|escape:'htmlall':'UTF-8'}"{if 0} selected="selected"{/if}>{$fi.label|escape:'htmlall':'UTF-8'}</option>
        {/foreach}
    </select>

</div>