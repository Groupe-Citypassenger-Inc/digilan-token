(function ($) {
  $(document).ready(function () {
    const currentBeginFrequency = $('#dlt-frequency-begin').val();
    const currentFrequency = $('#dlt-frequency').val();
    const currentMailSubject = $('#dlt-mail-subject').val();
    const currentMailBody = $('#dlt-mail-body').val();
    const currentTestingMail = $('#dlt-test-mail').val();
    function check_mailing_form() {
      if ($('#dlt-frequency-begin').val() === currentBeginFrequency &&
        $('#dlt-frequency').val() === currentFrequency &&
        $('#dlt-mail-subject').val() === currentMailSubject &&
        $('#dlt-mail-body').val() === currentMailBody) {
        $('#dlt-mailing-submit').prop('disabled', true);
      } else {
        $('#dlt-mailing-submit').prop('disabled', false);
      }
    }
    $('#dlt-frequency-begin').on('input', check_mailing_form);
    $('#dlt-frequency').on('input', check_mailing_form);
    $('#dlt-mail-subject').on('input', check_mailing_form);
    $('#dlt-mail-body').on('input', check_mailing_form);
    $('#dlt-test-mail').on('input', function() {
      if ($(this).val() === currentTestingMail) {
        $('#dlt-mailing-test-submit').prop('disabled', true);
      } else {
        $('#dlt-mailing-test-submit').prop('disabled', false);
      }
    });
  });
})(jQuery);
