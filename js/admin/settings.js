(function ($) {
  $(document).ready(function () {
    var successMessage = settings_data.successMessage;
    var errorMessage = settings_data.errorMessage;
    var ajax_nonce = settings_data._ajax_nonce;
    $('#dlt-cityscope-input').change(function () {
      $(this).val($(this).val());
    });
    $('#dlt-test-cityscope').click(function () {
      $.ajax({
        type: 'post',
        data: {
	  'cityscope-backend': $('#dlt-cityscope-input').val(),
          'action': 'digilan-token-cityscope',
          '_ajax_nonce': ajax_nonce
	},
        dataType: 'json',
        url: ajaxurl,
        success: function () {
          $('#dlt-test-result').find('span').remove();
          var result = '<span style="color: green">' + successMessage + '</span>';
          $('#dlt-test-result').append(result);
        },
        error: function (err) {
          $('#dlt-test-result').find('span').remove();
          var result = '<span style="color: red">' + errorMessage + ': ' + err.status + '</span>';
          $('#dlt-test-result').append(result);
        }
      });
    });
  });
})(jQuery);
