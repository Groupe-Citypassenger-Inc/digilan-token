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
          /** wp_localize_script on digilan-token.php provide settings_data to configure ajax request */
          '_ajax_nonce': settings_data._ajax_nonce
        },
        dataType: 'json',
        url: ajaxurl,
        success: function (response) {
          test_cityscope_display('green', response.statusText);
        },
        error: function (response) {
          const color = (response.status === 200 || response.status === 201) ? 'green' : 'red';
          test_cityscope_display(color, response.statusText);
      }});
    });
  });
})(jQuery);
