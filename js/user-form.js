(function($) {
  $(document).ready(function() {
    $('.lang-select').click(function () {
      $('.language-list-container').show();
    });

    $('.lang-select').focusout(function () {
      // Timeout required, otherwise, pop-up list close before item click handled
      setTimeout(function () {
        $('.language-list-container').hide();
      }, 100);
    });

    function update_form_display_language(lang) {
      $.ajax({
        type: 'POST',
        data: {
          _ajax_nonce: user_form_data._ajax_nonce,
          action: 'digilan-token-user-form-language',
          lang: lang,
        },
        dataType: 'json',
        url: '/wordpress/wp-admin/admin-ajax.php',
        success: function () {
          location.reload();
        },
        error: function (message) {
          alert('Sorry, we could not change language, try again later !');
        },
      });
    };

    $('#language-list li').click(function(){
      let lang = $(this).find('img').attr('value');
      update_form_display_language(lang)
    });

    $('.missing-translation').each(function(){
      $(this).prop('title', 'Missing translation');
    })

    function update_data_form(key, input_value) {
      $('input[name="dlt-user-form-hidden/' + key + '"]').val(input_value);
      $('a[name="connection-link-form"]').attr('href', function(i, a) {
        const regex = new RegExp('(' + key + '=?)[a-z]*', 'ig');
        return a.replace( regex, key + '=' + input_value );
      });
    };

    jQuery.each(form_inputs, function(key, value) {
      let name = 'dlt-' + key ;
      $('input[name="' + name + '"]').on('change', function () {
        update_data_form(key, $(this).val());
      })

      $('select[name="' + name + '"]').on('change', function () {
        update_data_form(key, $(this).val());
      });
    })
  });
})(jQuery);
