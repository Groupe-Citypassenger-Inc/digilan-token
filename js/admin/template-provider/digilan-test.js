(function ($) {
  $(document).ready(function () {
    var $test = $('#dlt-test-configuration');
    if ($test.length) {
      $(dlt_test.fields).on('keyup.test', function () {
        $('#dlt-test-button').remove();
        $('#dlt-test-please-save').css('display', 'inline');
        $('input').off('keyup.test');
      });
    }
  });
})(jQuery);
