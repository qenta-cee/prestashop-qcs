/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern
 * Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard
 * CEE range of products and services.
 *
 * They have been tested and approved for full functionality in the standard
 * configuration
 * (status on delivery) of the corresponding shop system. They are under
 * General Public License Version 2 (GPLv2) and can be used, developed and
 * passed on to third parties under the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability
 * for any errors occurring when used in an enhanced, customized shop system
 * configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and
 * requires a comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee
 * their full functionality neither does Wirecard CEE assume liability for any
 * disadvantages related to the use of the plugins. Additionally, Wirecard CEE
 * does not guarantee the full functionality for customized shop systems or
 * installed plugins of other vendors of plugins within the same shop system.
 *
 * Customers are responsible for testing the plugin's functionality before
 * starting productive operation.
 *
 * By installing the plugin into the shop system the customer agrees to these
 * terms of use. Please do not use the plugin if you do not agree to these
 * terms of use!
 *
 * @author    WirecardCEE
 * @copyright WirecardCEE
 * @license   GPLv2
 */

$(document).ready(function () {
  $('#pt_wirecardcheckoutseamless_pay_obligation').on('submit', function () {
    $('#pt_wirecardcheckoutseamless_pay_obligation').prop( "disabled", true );
  });

  checkConditions();
});

function checkConditions() {
  $("#conditions-to-approve input[type=checkbox]").each(function(){
    $(this).on("change",function(){
      if($(this).is(":checked")) {
        $(".js-payment-option-form:visible button:visible").prop("disabled",false);
      }
      else {
        $(".js-payment-option-form:visible button:visible").prop("disabled",true);
        return false;
      }
    });
  });
}

function wirecardceecardsubmit(event, elem) {
  event.stopPropagation();
  event.stopImmediatePropagation();

  var href = elem.getAttribute("action"),
  hasError = false, form = $(elem), data_ = form.serializeArray(), data = {}, alertBox = $('div.alert.alert-danger',form);

  data_.forEach(function(item){
    data[item.name] = item.value;
  });

  alertBox.html("");
  alertBox.parent().hide();

  if (data.currentName === 'invoice' && typeof wcsinvoiceValidate != "undefined" && !wcsinvoiceValidate(alertBox))
    return false;
  if (data.currentName === 'installment' && typeof wcsinstallmentValidate != "undefined" && !wcsinstallmentValidate(alertBox))
    return false;

  if (data.isSeamless==1) {
    form.find('.has-error').removeClass('has-error');
    var paymentData = {
      'paymentType': data.currentMethod
    };

    var validated = true;
    $('[data-wcs-fieldname]', form).each(function (index, value) {

      if (!wcsValidateField(this))
        validated = false;

      paymentData[$(this).data('wcs-fieldname')] = $(this).val()
    });

    if (validated) {
      wirecardCheckoutSeamlessStore(
        paymentData,
        alertBox,
        function (response) {
          document.location.href = href;
        },
        function (response) {
          alertBox.parent().css('display', 'block');
        });
    }

    return false;
  } else {

    var additionalData = { };

    $('[data-wcs-fieldname]', form).each(function (index, value) {

      if (!wcsValidateField(this))
        hasError = true;

      additionalData[$(this).data('wcs-fieldname')] = $(this).val()
    });

    if (hasError)
      return false;

    href += '&' + $.param(additionalData);

    document.location.href = href;
  }
  return false;
}

function showPaymentModal(url){
  var modal = $("#paymentWcsModal");
  modal.on('show.bs.modal', function(e) {
      $('.modal-body iframe', modal).attr('src',url);
  });
  modal.modal('show');
}
