/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

$(function() {
   $('#type').on('change', function() {
       var selected = $(this).val();
       var custStmtField = $('#customerStatement');
       $('.qcs-specific.' + selected).each(function(idx, field) {
           $(field).removeClass('qcs-display-none');
           if ($(field).hasClass('qcs-required')) {
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

       $('.qcs-specific:not(.' + selected + ')').each(function(idx, field) {
           $(field).addClass('qcs-display-none');
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
                url:$("#ajaxUrl").val(),
                dataType: 'json',
                type: 'post',
                delay:250,
                data: function (term,page) {
                    return {
                        ajax: true,
                        action: 'GetOrdersSelect2',
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