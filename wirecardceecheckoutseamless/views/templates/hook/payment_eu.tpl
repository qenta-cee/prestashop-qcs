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

{*{$paymentOption->getMethod()|print_r}*}
{if $jsUrl != false}
  <p><script src="{$jsUrl}"></script></p>
{/if}

<form onsubmit="return wirecardceecardsubmit(event,this)" id="payment_form" class="payment_form_{$current.name}" action="{$action}" method="post">
  <input type="hidden" name="isSeamless" value="{$current.payment->isSeamless()}">
  <input type="hidden" name="currentName" value="{$current.name}">
  <input type="hidden" name="currentMethod" value="{$current.method}">
    {if $current.template}
    {include $current.template}
    {/if}
  <div class="form-group" style="display: none">
    <div class="alert alert-danger" role="alert">
    </div>
  </div>
  <div class="form-group">
    <button class="btn btn-primary center-block" type="submit" disabled="disabled">{l s='Order with obligation to pay' mod='wirecardceecheckoutseamless'}</button>
  </div>
</form>
