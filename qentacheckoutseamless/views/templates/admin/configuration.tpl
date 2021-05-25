{*
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 *}

<div class="qentacheckoutseamless-wrapper">
    <a href="https://qenta-cee.at/" target="_blank" title="www.qenta-cee.at"><img
                class="qentacheckoutseamless-logo" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/qenta-logo.svg" alt="Qenta CEE"
                border="0"/>
    </a><br/>
    <p class="qentacheckoutseamless-intro">{l s='Qenta - Your Full Service Payment Provider - Comprehensive solutions from one single source' mod='qentacheckoutseamless'}</p>
    {l s='Qenta is one of the world´s leading providers of outsourcing and white label solutions for electronic payment transactions.' mod='qentacheckoutseamless'}
    <br/><br/>
    {l s='As independent provider of payment solutions, we accompany our customers along the entire business development. Our payment solutions are perfectly tailored to suit e-Commerce requirements and have made us Austria´s leading payment service provider. Customization, competence, and commitment.' mod='qentacheckoutseamless'}<br/>
    <br/>
    {if $is_core}
    <p><a href="https://checkoutportal.com/{$country|escape:'htmlall'}/{$language|escape:'htmlall'}/prestashop/" target="_blank">
            {l s='Registration for new clients' mod='qentacheckoutseamless'}
        </a></p>
    {/if}
    <p><a href="https://guides.qenta.com/doku.php/plugins_general" target="_blank">{l s='General information regarding Qenta Shop Plugins' mod='qentacheckoutseamless'}</a></p>
    <div style="clear:both;"></div>
    <div class="btn-group">
        <a class="btn btn-default" id="doQcsConfigTest" href="#">
            <i class="icon-check"></i>
            {l s='Test configuration' mod='qentacheckoutseamless'}
        </a>
        <a class="btn btn-default" id="doQcsContactSupport" href="{$link->getAdminLink('AdminQentaCheckoutSeamlessSupport')|escape:'html':'UTF-8'}">
            <i class="icon-question"></i>
            {l s='Contact support' mod='qentacheckoutseamless'}
        </a>
        {if $backendEnabled}
        <a class="btn btn-default" id="doQcsBackendTransactions" href="{$link->getAdminLink('AdminQentaCheckoutSeamlessBackend')|escape:'html':'UTF-8'}">
            <i class=e"icon-mony"></i>
            {l s='Transactions' mod='qentacheckoutseamless'}
        </a>
        <a class="btn btn-default" id="doQcsBackendFundTransfer"
           href="{$link->getAdminLink('AdminQentaCheckoutSeamlessFundTransfer')|escape:'html':'UTF-8'}">
            <i class="icon-exchange"></i>
            {l s='Fund transfer' mod='qentacheckoutseamless'}
        </a>
        {/if}
    </div>
    <div style="clear:both;"></div>
    <p></p>
</div>

<script type="text/javascript">
    $(function () {
        $('#doQcsConfigTest').on('click', function() {
            $.ajax({
                type: 'POST',
                {** this url doesn't work when escaped *}
                url: '{$ajax_configtest_url}',
                dataType: 'json',
                data: {
                    controller: 'AdminModules',
                    action: 'ajaxTestConfig',
                    ajax: true
                },
                success: function (jsonData) {
                    if (jsonData) {
                        $.fancybox({
                            fitToView: true,
                            content: '<div><fieldset><legend>{l s='Test result' mod='qentacheckoutseamless'}</legend>' +
                                '<label>{l s='Status' mod='qentacheckoutseamless'}:</label>' +
                                '<div class="margin-form" style="text-align:left;">' + jsonData.status + '</div><br />' +
                                '<label>{l s='Message' mod='qentacheckoutseamless'}:</label>' +
                                '<div class="margin-form" style="text-align:left;">' + jsonData.message + '</div></fieldset></div>'
                        });
                    }
                }
            });
        });
    });
    {if $backendEnabled}
    $(document).ready(function(){
        var inp = $("#QCS_BASICDATA_BACKENDPW");
        var customerIdInp = $("#QCS_BASICDATA_CUSTOMER_ID");
        var modeSelect = $("#QCS_BASICDATA_CONFIGMODE");

        enableDisableBackendOperations(inp,true);
        correctCustomerId(customerIdInp);
        modeSelectED(modeSelect);
        inp.on("keyup change paste",function(){
            enableDisableBackendOperations($(this));
        });
        customerIdInp.on("keyup change paste", function(){
            $(this).closest(".input-group").toggleClass("has-error", !correctCustomerId($(this)));
        });
        modeSelect.on('blur change',function(){
            modeSelectED($(this));
        });

        $("#configuration_form").submit(function(e){
            $('#QCS_BASICDATA_CUSTOMER_ID, #QCS_BASICDATA_SHOP_ID, #QCS_BASICDATA_SECRET').prop('disabled',false);
        });

        function modeSelectED(e){
            var state = e.val()=='production';
            $('#QCS_BASICDATA_CUSTOMER_ID, #QCS_BASICDATA_SHOP_ID, #QCS_BASICDATA_SECRET').prop('disabled',!state);
        }

        function correctCustomerId(inp){
            return /^D2[0-8]\d{ldelim}4{rdelim}|9[5-9]\d{ldelim}3{rdelim}$/.test(inp.val());
        }

        function enableDisableBackendOperations(inp,init=false){
            if(inp.val().length==0){
                if(init)
                    $("#doQcsBackendTransactions,#doQcsBackendFundTransfer").remove();
                else
                    $("#doQcsBackendTransactions,#doQcsBackendFundTransfer").addClass("disabled");
            }
            else
                $("#doQcsBackendTransactions,#doQcsBackendFundTransfer").removeClass("disabled");
        }
    });
    {/if}
</script>