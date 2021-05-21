{*
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/
 *}
{extends file='page.tpl'}
{block name='content'}
<section id="content">
  <div class="card card-block">
    <div class="row">
      <div class="col-xs-12">
        <h1 class="page-heading">{l s='Order summary' mod='qentacheckoutseamless'}</h1>

      {if isset($nbProducts) && $nbProducts <= 0}
        <p class="alert alert-warning">{l s='Your shopping cart is empty.' mod='qentacheckoutseamless'}</p>
      {else}
        <form id="qentacheckoutseamless_transaction" action="{url}module/qentacheckoutseamless/payment?paymentType={$paymentType}&financialinstitution={$financialinstitution}&birthdate={$birthdate}" method="post">
          <div class="box">
            <h3 class="page-subheading">{l s='Qenta Checkout Seamless payment' mod='qentacheckoutseamless'}</h3>
            <p class="">
              <strong class="dark">
                {l s='You have chosen to pay with ' mod='qentacheckoutseamless'}{$paymentName|escape:'htmlall':'UTF-8'}.
              </strong>
            </p>
            <p>
              - {l s='Total amount of your order:' mod='qentacheckoutseamless'}
              <span id="amount" class="price">{$total|escape:'htmlall':'UTF-8'}</span>
            </p>
            <p>- {l s='Please confirm your order by clicking "Order with obligation to pay".' mod='qentacheckoutseamless'}</p>
          </div>
          <p class="cart_navigation clearfix" id="cart_navigation">
            <a href="{url}?controller=order" class="button-exclusive btn btn-default">
              <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='qentacheckoutseamless'}
            </a>
            <button type="submit" id="pt_qentacheckoutseamless_pay_obligation" class="btn btn-primary">
              <span>{l s='Order with obligation to pay' mod='qentacheckoutseamless'}</span>
            </button>
          </p>
        </form>

        {/if}

      </div>
    </div>
  </div>
</section>
{/block}
