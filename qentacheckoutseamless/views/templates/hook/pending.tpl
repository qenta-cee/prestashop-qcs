{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}

{if $status == 'ok'}
    <p>{l s='Thank you for your order.' mod='qentacheckoutseamless'}
        <br/><br/>
        <strong>{l s='Your order is shipped as soon as we receive the confirmation from your bank.' mod='qentacheckoutseamless'}</strong>
        <br/><br/>
        {l s='For any questions or further information contact our' mod='qentacheckoutseamless'} <a
                href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='customer support' mod='qentacheckoutseamless'}</a>.
    </p>
{else}
    <p class="warning">
        {l s='We have noticed that there is a problem with your order. If you think this is an error, contact our' mod='qentacheckoutseamless'}
        <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='customer support'
            mod='qentacheckoutseamless'}</a>.
    </p>
{/if}

