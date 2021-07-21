{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}

{foreach from=$paymentTypes item=current}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module" id="pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}">
                {if $current.template}
                    <a id="pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_dataopen"
                       class="pt_qentacheckoutseamless pt_qcs_{$current.name|escape:'htmlall':'UTF-8'}"
                       href="#"
                       title="{l s='Pay with ' mod='qentacheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}">
                        <span class="pt_logo_container"><span class="pt_qentacheckoutseamless_logo">
                            <img src="{$current.img|escape:'htmlall':'UTF-8'}" alt="{$current.name|escape:'htmlall':'UTF-8'}"/>
                            </span>
                            </span>
                            <span class="pt_qentacheckoutseamless_text">
                                {l s='Pay with ' mod='qentacheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}
                            </span>
                    </a>
                {else}
                    <a class="pt_qentacheckoutseamless pt_qcs_{$current.name|escape:'htmlall':'UTF-8'}"
                       href="{$link->getModuleLink('qentacheckoutseamless', 'paymentExecution', ['paymentType' => $current.name, 'paymentName' => $current.label], true)|escape:'htmlall':'UTF-8'}"
                       title="{l s='Pay with ' mod='qentacheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}">
                        <span class="pt_logo_container"><span class="pt_qentacheckoutseamless_logo">
                            <img src="{$current.img|escape:'htmlall':'UTF-8'}" alt="{$current.name|escape:'htmlall':'UTF-8'}"/>
                            </span></span>
                        <span class="pt_qentacheckoutseamless_text">
                            {l s='Pay with ' mod='qentacheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}
                        </span>
                    </a>
                {/if}

            </p>

        </div>

    </div>
    {if $current.template}
        <div class="row" id="pt_qcs_{$current.name|escape:'htmlall':'UTF-8'}_data" style="display: none;">
            <div class="col-xs-12">
                <div class="qcs_payment_container">
                    {include $current.template}

                    <div class="form-group">
                        <button id="pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_store"
                                class="btn btn-default button button-medium" name="submitIdentity" type="submit">
                            <span>{l s='Pay with ' mod='qentacheckoutseamless'}{$current.label|escape:'htmlall':'UTF-8'}<i
                                        class="icon-chevron-right right"></i></span>
                        </button>
                        <div class="bootstrap">
                            <div id="pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox"
                                 class="module_error alert alert-danger" style="display: none">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <ul id="pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msglist"></ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(function () {

                $('#pt_qentacheckoutseamless_{$current.name}_dataopen').on('click', function () {

                    $(this).toggleClass("open");
                    $('#pt_qcs_{$current.name|escape:'htmlall':'UTF-8'}_data').toggle();
                    return false;
                });
            });

            {if $current.payment->isSeamless()}
            $(function () {
                var href = {$link->getModuleLink('qentacheckoutseamless', 'paymentExecution', ['paymentType' => $current.name, 'paymentName' => $current.label], true)|json_encode};

                $('#pt_qentacheckoutseamless_{$current.name}_store').on('click', function () {

                    $('#pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox').css('display', 'none');
                    $('#pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msglist').empty();


                    var paymentData = {
                        'paymentType': {$current.method|json_encode}
                    };
                    var hasError = false;

                    $('#pt_qcs_{$current.name}_data [data-qcs-fieldname]').each(function (index, value) {

                        if (!qcsValidateField(this))
                            hasError = true;

                        paymentData[$(this).data('qcs-fieldname')] = $(this).val()
                    });

                    if (hasError)
                        return false;

                    qentaCheckoutSeamlessStore(paymentData, $('#pt_qentacheckoutseamless_{$current.name}_msglist'), function (response) {
                        document.location.href = href;
                    }, function (response) {
                        $('#pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox').css('display', 'block');
                    });
                    return false;
                });
            });
            {else}
            $(function () {

                $('#pt_qentacheckoutseamless_{$current.name}_store').on('click', function () {

                    $('#pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox').css('display', 'none');
                    $('#pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msglist').empty();

                    if (typeof qcs{$current.name|escape:'htmlall':'UTF-8'}Validate != "undefined" && !qcs{$current.name|escape:'htmlall':'UTF-8'}Validate($('#pt_qentacheckoutseamless_{$current.name|escape:'htmlall':'UTF-8'}_msgbox')))
                        return false;

                    var href = {$link->getModuleLink('qentacheckoutseamless', 'paymentExecution', ['paymentType' => $current.name, 'paymentName' => $current.label], true)|json_encode};

                    var additionalData = { };
                    var hasError = false;

                    $('#pt_qcs_{$current.name}_data [data-qcs-fieldname]').each(function (index, value) {

                        if (!qcsValidateField(this))
                            hasError = true;

                        additionalData[$(this).data('qcs-fieldname')] = $(this).val()
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

