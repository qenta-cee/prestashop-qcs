{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}


<div class="form-group">
    <label>{l s='Account owner' mod='qentacheckoutseamless'}</label>
    <input type="text" name="accountOwner" autocomplete="off" class="form-control accountowner"
           data-qcs-fieldname="accountOwner"/>
</div>

<div class="form-group required">
    <label class="required"> {l s='BIC' mod='qentacheckoutseamless'}</label>
    <input type="tel" name="bankBic" autocomplete="off" class="form-control bankbic is_required qcs-validate"
           data-qcs-fieldname="bankBic"/>
</div>

<div class="required form-group">
    <label class="required"> {l s='IBAN' mod='qentacheckoutseamless'}</label>
    <input type="tel" name="bankAccountIban" autocomplete="off" class="form-control bankaccountiban is_required qcs-validate"
           data-qcs-fieldname="bankAccountIban"/>
</div>
