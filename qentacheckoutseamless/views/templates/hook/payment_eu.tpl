{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}

{if $jsUrl != false}
  <p><script src="{$jsUrl}"></script></p>
{/if}

<form onsubmit="return qentacardsubmit(event,this)" id="payment_form" class="payment_form_{$current.name|escape:'htmlall':'UTF-8'}" action="{$action|escape:'htmlall':'UTF-8'}" method="post">
  <input type="hidden" name="isSeamless" value="{$current.payment->isSeamless()|escape:'htmlall':'UTF-8'}">
  <input type="hidden" name="currentName" value="{$current.name|escape:'htmlall':'UTF-8'}">
  <input type="hidden" name="currentMethod" value="{$current.method|escape:'htmlall':'UTF-8'}">
    {if $current.template}
    {include $current.template}
    {/if}
  <div class="form-group" style="display: none">
    <div class="alert alert-danger" role="alert">
    </div>
  </div>
  <div class="form-group">
    <button class="btn btn-primary center-block" type="submit" disabled="disabled">{l s='Order with obligation to pay' mod='qentacheckoutseamless'}</button>
  </div>
</form>
