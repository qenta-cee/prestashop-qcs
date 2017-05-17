{extends file='checkout/_partials/cart-detailed-actions.tpl'}

{block name='cart_detailed_actions'}
    {if !empty($walletId)}
        <script src="https://checkout.wirecard.com/masterpass/js/WirecardCheckout.MasterPassClient.js"></script>
        <div class="checkout cart-detailed-actions card-block">
            <div class="text-xs-center">
                <span id="wcs_masterpass_button"></span>
            </div>
        </div>
        <script>
            var data = {
                walletId: '{$walletId}',
                element: 'wcs_masterpass_button',
                callbackUrl: '{$currentUrl}'
            };
            WirecardCheckout.MasterPassClient.checkoutButton(data);
        </script>
    {/if}
{/block}