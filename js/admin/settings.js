(function ($) {
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
          cityscope_backend: $('#dlt-cityscope-input').val(),
          action: 'digilan-token-cityscope',
          /** wp_localize_script on digilan-token.php provide settings_data to configure ajax request */
          _ajax_nonce: settings_data._ajax_nonce
        },
        dataType: 'json',
        url: ajaxurl,
        success: function () {
          $('#valid-portal').css('display', 'inline-block');
          $('#invalid-portal').css('display', 'none');
        },
        error: function (response) {
          if (response.status === 200 || response.status === 201) {
            $('#valid-portal').css('display', 'inline-block')
            $('#invalid-portal').css('display', 'none');
          } else {
            $('#valid-portal').css('display', 'none');
            $('#invalid-portal').css('display', 'inline-block');
          }
      }});
    });
  });
})(jQuery);
