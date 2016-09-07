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

{foreach from=$paymentTypes item=current}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module" id="pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}">
                {if $current.template}
                    <a id="pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_dataopen"
                       class="pt_wirecardcheckoutseamless pt_wcs_{$current.name|escape:'htmlall':'UTF-8'}"
                       href="#"
                       title="{l s='Pay with ' mod='wirecardceecheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}">
                        <span class="pt_logo_container"><span class="pt_wirecardcheckoutseamless_logo">
                            <img src="{$current.img|escape:'htmlall':'UTF-8'}" alt="{$current.name|escape:'htmlall':'UTF-8'}"/>
                            </span>
                            </span>
                            <span class="pt_wirecardcheckoutseamless_text">
                                {l s='Pay with ' mod='wirecardceecheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}
                            </span>
                    </a>
                {else}
                    <a class="pt_wirecardcheckoutseamless pt_wcs_{$current.name|escape:'htmlall':'UTF-8'}"
                       href="{$link->getModuleLink('wirecardceecheckoutseamless', 'paymentExecution', ['paymentType' => $current.name, 'paymentName' => $current.label], true)|escape:'htmlall':'UTF-8'}"
                       title="{l s='Pay with ' mod='wirecardceecheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}">
                        <span class="pt_logo_container"><span class="pt_wirecardcheckoutseamless_logo">
                            <img src="{$current.img|escape:'htmlall':'UTF-8'}" alt="{$current.name|escape:'htmlall':'UTF-8'}"/>
                            </span></span>
                        <span class="pt_wirecardcheckoutseamless_text">
                            {l s='Pay with ' mod='wirecardceecheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}
                        </span>
                    </a>
                {/if}

            </p>

        </div>

    </div>
    {if $current.template}
        <div class="row" id="pt_wcs_{$current.name|escape:'htmlall':'UTF-8'}_data" style="display: none;">
            <div class="col-xs-12">
                <div class="wcs_payment_container">
                    {include $current.template}

                    <div class="form-group">
                        <button id="pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_store"
                                class="btn btn-default button button-medium" name="submitIdentity" type="submit">
                            <span>{l s='Pay with ' mod='wirecardceecheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}<i
                                        class="icon-chevron-right right"></i></span>
                        </button>
                        <div class="bootstrap">
                            <div id="pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox"
                                 class="module_error alert alert-danger" style="display: none">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <ul id="pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msglist"></ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(function () {

                $('#pt_wirecardcheckoutseamless_{$current.name}_dataopen').on('click', function () {

                    $(this).toggleClass("open");
                    $('#pt_wcs_{$current.name|escape:'htmlall':'UTF-8'}_data').toggle();
                    return false;
                });
            });

            {if $current.payment->isSeamless()}
            $(function () {
                var href = {$link->getModuleLink('wirecardceecheckoutseamless', 'paymentExecution', ['paymentType' => $current.name, 'paymentName' => $current.label], true)|json_encode};

                $('#pt_wirecardcheckoutseamless_{$current.name}_store').on('click', function () {

                    $('#pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox').css('display', 'none');
                    $('#pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msglist').empty();


                    var paymentData = {
                        'paymentType': {$current.method|json_encode}
                    };
                    var hasError = false;

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
                    return false;
                });
            });
            {else}
            $(function () {

                $('#pt_wirecardcheckoutseamless_{$current.name}_store').on('click', function () {

                    $('#pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox').css('display', 'none');
                    $('#pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msglist').empty();

                    if (typeof wcs{$current.name|escape:'htmlall':'UTF-8'}Validate != "undefined" && !wcs{$current.name|escape:'htmlall':'UTF-8'}Validate($('#pt_wirecardcheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox')))
                        return false;

                    var href = {$link->getModuleLink('wirecardceecheckoutseamless', 'paymentExecution', ['paymentType' => $current.name, 'paymentName' => $current.label], true)|json_encode};

                    var additionalData = { };
                    var hasError = false;

                    $('#pt_wcs_{$current.name}_data [data-wcs-fieldname]').each(function (index, value) {

                        if (!wcsValidateField(this))
                            hasError = true;

                        additionalData[$(this).data('wcs-fieldname')] = $(this).val()
                    });

                    if (hasError)
                        return false;

                    href += '&' + $.param(additionalData);

                    document.location.href = href;
                    return false;
                });
            });
            {/if}
        </script>
    {/if}

{/foreach}

