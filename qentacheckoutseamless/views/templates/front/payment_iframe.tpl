{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}

{extends file='page.tpl'}
{block name='content'}

  <section id="content">
    <div class="card card-block">
      <div class="row">
        <div class="col-xs-12">

          {if isset($nbProducts) && $nbProducts <= 0}
            <p class="alert alert-warning">{l s='Your shopping cart is empty.' mod='qentacheckoutseamless'}</p>
          {else}
            <p class="cart_navigation clearfix" id="cart_navigation">
              <a href="{url}order" class="button-exclusive btn btn-default">
                <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='qentacheckoutseamless'}
              </a>
              <a id="qcsIframeBox" class="button-exclusive btn btn-default" href="{$redirectUrl|escape:'htmlall':'UTF-8'}" data-toggle="modal" data-target="#paymentQcsModal" title="{l s='Qenta Checkout Seamless payment' mod='qentacheckoutseamless'}">Open iFrame Modal</a>
            </p>
          {/if}
        </div>
      </div>
    </div>
  </section>

  <div class="modal fade" id="paymentQcsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <iframe width="100%" height="600px" frameborder="0"></iframe>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.onload = function() {
      showPaymentModal('{$redirectUrl|escape:'htmlall':'UTF-8'}');
    }
  </script>

{/block}
