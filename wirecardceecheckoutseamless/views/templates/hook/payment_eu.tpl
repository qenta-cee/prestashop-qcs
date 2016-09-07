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

<form id="wirecardcheckoutseamless_payment_form_{$current.name|escape:'htmlall':'UTF-8'}" class="payment_option_form wcs_payment_form_eu" action="#" method="post">
    @hiddenSubmit
    {if $current.template}
    {include $current.template}
    {/if}
</form>

<script type="text/javascript">
    $(function () {

        $('#wirecardcheckoutseamless_payment_form_{$current.name}').on('submit', function (e) {

            var href = {$link->getModuleLink('wirecardceecheckoutseamless', 'paymentExecution', ['paymentType' => $current.name, 'paymentName' => $current.label], true)|json_encode};
            var hasError = false;

            $('#pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox').css('display', 'none');
            $('#pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msglist').empty();

        {if $current.payment->isSeamless()}

                var paymentData = {
                    'paymentType': {$current.method|json_encode}
                };

                $('#pt_wcs_{$current.name}_data [data-wcs-fieldname]').each(function (index, value) {

                    if (!wcsValidateField(this))
                        hasError = true;

                    paymentData[$(this).data('wcs-fieldname')] = $(this).val()
                });

                if (hasError)
                    return false;

                wirecardCheckoutSeamlessStore(paymentData, $('#pt_wirecardcheckoutseamless_{$current.name}_msglist'), function (response) {
                    document.location.href = href;
                }, function (response) {
                    $('#pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox').css('display', 'block');
                });

            {else}

                if (typeof wcs{$current.name|escape:'htmlall':'UTF-8'}Validate != "undefined" && !wcs{$current.name|escape:'htmlall':'UTF-8'}Validate($('#pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox')))
                    return false;

                var additionalData = { };


                $('#pt_wcs_{$current.name}_data [data-wcs-fieldname]').each(function (index, value) {

                    if (!wcsValidateField(this))
                        hasError = true;

                    additionalData[$(this).data('wcs-fieldname')] = $(this).val()
                });

                if (hasError)
                    return false;

                href += '&' + $.param(additionalData);

                document.location.href = href;

            {/if}

            return false;
        });

        {if $current.payment->isSeamless()}
        $('#wirecardcheckoutseamless_payment_form_{$current.name}').parent().parent().children('p').children('a:first').on('click', function (e) {
            $('#wirecardcheckoutseamless_payment_form_{$current.name|escape:'htmlall':'UTF-8'}').parent().css('display', 'block');
            $('#wirecardcheckoutseamless_payment_form_{$current.name|escape:'htmlall':'UTF-8'}').css('display', 'block');
            return true;
        });
        {/if}
    });

</script>
