(function ($) {
  $(document).ready(function () {
    $('.dlt-auth').click(function (e) {
      if (!$('#dlt-tos').prop('checked')) {
        $('#dlt-tos').focus();
        e.preventDefault();
      }
    });
  });
})(jQuery);
