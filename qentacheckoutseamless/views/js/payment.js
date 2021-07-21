/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/prestashop-qcs/blob/master/LICENSE
 */

var qentaCheckoutSeamlessStore;
var htmlEntityDecode;
$(function () {
  qentaCheckoutSeamlessStore = function (
    data,
    messageContainer,
    onSuccess,
    onError
  ) {
    var qenta = new WirecardCEE_DataStorage();
    qenta.storePaymentInformation(data, function (response) {
      if (response.getErrors()) {
        var errors = response.response.error;
        for (var i = 0; i <= response.response.errors; i++) {
          if (typeof errors[i] === 'undefined') {
            continue;
          }
          messageContainer.append(
            '<li>' + htmlEntityDecode(errors[i].consumerMessage) + '</li>'
          );
        }
        onError(response);
      } else {
        onSuccess(response);
      }
    });
  };
  htmlEntityDecode = function (str) {
    var tarea = document.createElement('textarea');
    tarea.innerHTML = str;
    return tarea.value;
  };

  qcsValidateField = function (field) {
    var result = true;

    if ($(field).hasClass('is_required')) {
      result = $(field).val().length > 0;
      if (!result) $(field).closest('.form-group').addClass('has-error');
    }

    return result;
  };

  qcsValidateMinAge = function (dob, minage) {
    if (!minage) return true;

    var birthdate = new Date(dob);
    var year = birthdate.getFullYear();
    var today = new Date();
    if (year <= 1899 || year >= today.getFullYear() + 1) {
      return false;
    }

    var limit = new Date(
      today.getFullYear() - minage,
      today.getMonth(),
      today.getDate()
    );
    return birthdate < limit;
  };

  $(document).on(
    'focusout',
    'input.qcs-validate, textarea.qcs-validate, select.qcs-validate',
    function () {
      qcsValidateField(this);
    }
  );
});
