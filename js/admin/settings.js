/** Thanks to wp_localize_script on digilan-token.php, we got data stored on an object called settings_data */
(function ($) {
  /**
   * @param {string} color The color of the text to display (for now, red or green)
   * @param {string} status The HTTP response status of the request.
   */
  function test_cityscope_display(color, status) {
    $('#dlt-test-cityscope-result').css('color', color).text('Result: ' + status);
  }
  $(document).ready(function () {
    const currentCityScopeCloud = $('#dlt-cityscope-input').val();
    $('#dlt-cityscope-input').on('keyup', function () {
      if ($(this).val() === currentCityScopeCloud) {
        $('#submit-settings').prop('disabled', true);
      } else {
        $('#submit-settings').prop('disabled', false);
      }
    });
    $('#dlt-test-cityscope').click(function () {
      $.ajax({
        type: 'post',
        data: {
          'cityscope-backend': $('#dlt-cityscope-input').val(),
          'action': 'digilan-token-cityscope',
          '_ajax_nonce': settings_data._ajax_nonce
        },
        dataType: 'json',
        url: ajaxurl,
        success: function (response) {
          test_cityscope_display('green', response.statusText);
        },
        error: function (response) {
          //TODO: check why this request go everytime on the "error" callback even if the status is 200
          const color = response.status === 200 ? 'green' : 'red';
          test_cityscope_display(color, response.statusText);
      }});
    });
  });
})(jQuery);
