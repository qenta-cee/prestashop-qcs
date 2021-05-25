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

<div class="required form-group">
    <label class="required"> {l s='Account number' mod='qentacheckoutseamless'}</label>
    <input type="tel" name="bankAccount" autocomplete="off" class="form-control bankaccount is_required qcs-validate"
           data-qcs-fieldname="bankAccount"/>
</div>

<div class="required form-group">
    <label class="required"> {l s='Bank number' mod='qentacheckoutseamless'}</label>
    <input type="tel" name="bankNumber" autocomplete="off" class="form-control banknumber is_required qcs-validate"
           data-qcs-fieldname="bankNumber"/>
</div>
