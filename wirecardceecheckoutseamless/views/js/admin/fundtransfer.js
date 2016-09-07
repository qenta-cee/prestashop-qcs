/**
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
 */

$(function() {
   $('#type').on('change', function() {
       var selected = $(this).val();
       var custStmtField = $('#customerStatement');
       $('.wcs-specific.' + selected).each(function(idx, field) {
           $(field).removeClass('wcs-display-none');
           if ($(field).hasClass('wcs-required')) {
               $(field).find('label').addClass('required');
               $(field).find(':input').attr('required', 'required');
               if (selected == 'sepa-ct' || selected == 'existingorder')
               {
                   custStmtField.attr('required', null);
                   var formGroup = custStmtField.parent().parent();
                   formGroup.removeClass('required');
                   formGroup.find('label').removeClass('required');
               }
           }
       });
       
       $('.wcs-specific:not(.' + selected + ')').each(function(idx, field) {
           $(field).addClass('wcs-display-none');
           $(field).find('label').removeClass('required');
           $(field).find(':input').attr('required', null);
           if (selected != 'sepa-ct' && selected != 'existingorder')
           {
               custStmtField.attr('required', 'required');
               var formGroup = custStmtField.parent().parent();
               formGroup.addClass('required');
               formGroup.find('label').addClass('required');
           }
       });
   }).trigger('change');

    $(document).ready(function(){
        $('#sourceOrderNumber').select2({
            ajax: {
                url:'../modules/wirecardceecheckoutseamless/ajax.php',
                dataType: 'json',
                type: 'post',
                delay:250,
                data: function (term,page) {
                    return {
                        method: 'getOrdersSelect2',
                        q: term,
                        page: page
                    };
                },
                results: function (data, params) {

                    return {
                        results: data.results,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: false
            }
        });
        $('#sourceOrderNumber').on("change",function(evt){
            added = evt.added;
            if( added.hasOwnProperty('currency')){
                $("#currency").val(added.currency);
                $("#currency").addClass("btn-warning");
                setTimeout(function(){
                    $("#currency").removeClass("btn-warning");
                },400);
            }
        });
    });
});