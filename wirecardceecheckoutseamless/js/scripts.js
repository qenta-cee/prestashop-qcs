$(document).ready(function () {
  $('#pt_wirecardcheckoutseamless_pay_obligation').on('click', function () {
    $('#pt_wirecardcheckoutseamless_pay_obligation').prop( "disabled", true );
  });
});

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
