/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
*/

$(document).ready(function () {
  $('#pt_qentacheckoutseamless_pay_obligation').on('submit', function () {
    $('#pt_qentacheckoutseamless_pay_obligation').prop( "disabled", true );
  });

  checkConditions();
});

function checkConditions() {
  $("#conditions-to-approve input[type=checkbox]").each(function(){
    $(this).on("change",function(){
      if($(this).is(":checked")) {
        $(".js-payment-option-form button[type=submit]").prop("disabled",false);
      }
      else {
        $(".js-payment-option-form button[type=submit]").prop("disabled",true);
        return false;
      }
    });
  });
}

function qentacardsubmit(event, elem) {
  event.stopPropagation();
  event.stopImmediatePropagation();

  var href = elem.getAttribute("action"),
  hasError = false, form = $(elem), data_ = form.serializeArray(), data = {}, alertBox = $('div.alert.alert-danger',form);

  data_.forEach(function(item){
    data[item.name] = item.value;
  });

  alertBox.html("");
  alertBox.parent().hide();

  if (data.currentName === 'invoice' && typeof qcsinvoiceValidate != "undefined" && !qcsinvoiceValidate(alertBox))
    return false;
  if (data.currentName === 'installment' && typeof qcsinstallmentValidate != "undefined" && !qcsinstallmentValidate(alertBox))
    return false;

  if (data.isSeamless==1) {
    form.find('.has-error').removeClass('has-error');
    var paymentData = {
      'paymentType': data.currentMethod
    };

    var validated = true;
    $('[data-qcs-fieldname]', form).each(function (index, value) {

      if (!qcsValidateField(this))
        validated = false;

      paymentData[$(this).data('qcs-fieldname')] = $(this).val()
    });

    if (validated) {
      qentaCheckoutSeamlessStore(
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

    $('[data-qcs-fieldname]', form).each(function (index, value) {

      if (!qcsValidateField(this))
        hasError = true;

      additionalData[$(this).data('qcs-fieldname')] = $(this).val()
    });

    if (hasError)
      return false;

    href += '&' + $.param(additionalData);

    document.location.href = href;
  }
  return false;
}

function showPaymentModal(url){
  var modal = $("#paymentQcsModal");
  modal.on('show.bs.modal', function(e) {
      $('.modal-body iframe', modal).attr('src',url);
  });
  modal.modal('show');
}
