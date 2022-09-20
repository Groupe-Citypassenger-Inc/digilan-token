(function($) {
  $(document).ready(function() {
    function update_data_form(key, input_value) {
      $('input[name="dlt-hidden-' + key + '"]').val(input_value);
      $('a[name="connection-link-form"]').attr('href', function(i, a)
      {
        const regex = new RegExp('(' + key + '=?)[a-z]*', 'ig');
        return a.replace( regex, key + '=' + input_value );
      });
    };
    jQuery.each(form_inputs, function(key, value) {
      _name = 'dlt-' + key ;
      $('input[name="' + _name + '"]').on("change", function updated_data() {
        update_data_form(key, $(this).val());
      })
      $('select[name="' + _name + '"]').on("change", function update_data() {
        update_data_form(key, $(this).val());
      });
    })
  });
})(jQuery);
