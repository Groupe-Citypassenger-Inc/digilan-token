(function ($) {
  $(document).ready(function () {
    $('#lang-search').on('input', function(input) {
      let search = input.target.value.toLowerCase().trim();
      let list_items = $('#language-list li');
      list_items.each(function(idx, li) {
        let name = $(li).attr('name').toLowerCase();
        if (name.includes(search)) {
          $(li).css('display', 'flex');
        } else {
          $(li).css('display', 'none');
        }
      })
    });

    function update_language(lang) {
      $('body').css('cursor', 'wait');
      $.ajax({
        type: 'POST',
        data: {
          _ajax_nonce: user_form_data._ajax_nonce,
          action: 'digilan-token-update-custom-portal-languages-available',
          custom_portal_lang: lang,
        },
        dataType: 'json',
        url: ajaxurl,
        success: function () {
          location.reload();
        },
        error: function () {
          $('body').css('cursor', 'default');
          alert('Sorry, we could not add this language, try again later !');
        },
      });
    };

    $('.lang-flag-delete').click(function() {
      update_language(this.name);
    });

    $('#language-list li button').click(function(){
      let lang = $(this).find('img').attr('value');
      update_language(lang);
    });

    $('.lang-select').click(function () {
      $('.language-list-container').show();
    });

    $('.lang-select').focusout(function () {
      // Timeout required, otherwise, pop-up list close before item click handled
      setTimeout(function () {
        $('.language-list-container').hide();
      }, 100);
    });

    $('.form-settings-field-row').on('click', function(value) {
      if ($(this).hasClass('header')) {
        return;
      }

      let currentElement = value.target;
      if (currentElement.classList.contains('delete-field')) {
        return;
      }

      let field_row_edit = this.nextElementSibling;
      if (field_row_edit.className.includes('row-visible')) {
        $(this).removeClass('top-row-visible');
        $(field_row_edit).removeClass('bottom-row-visible');
      } else {
        $(this).addClass('top-row-visible');
        $(field_row_edit).addClass('bottom-row-visible');
      };
    });

    $('.delete-field').on('click', function (value) {
      let isChecked = this.checked;
      let row = this.closest('.form-settings-field-row');

      if (isChecked) {
        $(row).addClass('delete-in-progress');
      } else {
        $(row).removeClass('delete-in-progress');
      }
    });

    function check_change(current) {
      let row = current.closest('div[name="field-row"]');
      let resetButton = $(row).find('input[name="reset-changes-button"]');

      $(row).addClass('update-in-progress');
      $(resetButton).attr('disabled', false);
      let fields = $(row).find('input.update-field');
      let isNoChanges = true;
      for (let i = 0; (i < fields.length) && (isNoChanges); i++) {
        let [prefix, field_name, property, lang] = fields[i].name.split('/');
        isNoChanges = fields[i].value === user_form_fields[field_name][property][lang];
      }

      if (isNoChanges) {
        $(resetButton).attr('disabled', true);
        $(row).removeClass('update-in-progress');
      }
    };

    $('.update-field').on('input', function () {
      check_change(this);
    });

    $('input[name="reset-changes-button"]').on('click', function(value) {
      let row = this.closest('div[name="field-row"]');
      $(row).removeClass('update-in-progress');
      $(this).attr('disabled', true);

      let fields = $(row).find('input.update-field');
      for (let i = 0; i < fields.length; i++) {
        let [prefix, field_name, property, lang] = fields[i].name.split('/');

        fields[i].value = null;
        if (user_form_fields[field_name][property][lang]) {
          fields[i].value = user_form_fields[field_name][property][lang];
        }

        if (property !== 'options') {
          continue;
        }

        let list_id = fields[i].id.replace('hidden', 'list');
        let list = $('#' + list_id);
        list.empty();

        let new_option = '<option value="instruction">' + js_translation.click_option_to_delete + '</option>';
        list.append(new_option);

        if (fields[i].value === "") {
          continue;
        }

        let options_list = fields[i].value.split(',');
        options_list.forEach(option => {
          let new_option = '<option value="' + option + '">' + option + '</option>';
          list.append(new_option);
        });
      }
    });

    function show_options_input() {
      $('label[name="options"]')
        .css('display', 'flex')
        .children('.required_input')
        .attr('required', true);
    };

    $('#btns-field-type-choice button').on('click', function(value) {
      $('button').removeClass('selected');
      $(this).addClass('selected');

      $('.new-field-row').css('display', 'table-row');
      $('label[name="name"]').css('display', 'flex');
      $('label[name="instruction"]').css('display', 'flex');

      $('label[name="options"]')
        .css('display', 'none')
        .children('.required_input')
        .removeAttr('required');

      $('#field-number-min-max').css('display', 'none');

      let type = this.name;
      document.getElementById('new-field-type').value = type;
      switch (type) {
        case 'text':
        case 'email':
        case 'tel':
        case 'checkbox':
          break;
        case 'number':
          $('#field-number-min-max').css('display', 'table-row');
          break;
        case 'radio':
          show_options_input();
          break;
        case 'select':
          show_options_input();
          break;
      }
    });

    function add_element_to_list(input_id, list_id, hidden_id) {
      let input = document.getElementById(input_id);

      if (false === input.validity.valid) {
        return;
      }
      if (input.value === '') {
        return;
      }
      let new_value = input.value;

      let hidden_input = $('#' + hidden_id);
      let options_list = hidden_input.val().split(',');
      if (options_list.includes(new_value)) {
        input.value = '';
        return;
      }

      let list = $('#' + list_id);

      let new_option = '<option value="' + new_value + '">' + new_value + '</option>';
      list.append(new_option);

      input.value = '';
      if (hidden_input.val() === '') {
        hidden_input.val(new_value);
      } else {
        let current_options = hidden_input.val();
        hidden_input.val(current_options + ',' + new_value);
      }
      check_change(input);
    }

    $('.new-field-options').keypress(function(event){
      let keycode = (event.keyCode ? event.keyCode : event.which);
      if (keycode === 13){
        event.preventDefault();

        let input_option_id = this.id;
        let list_option_id = input_option_id.replace('input', 'list');
        let hidden_option_id = input_option_id.replace('input', 'hidden');
        add_element_to_list(this.id, list_option_id, hidden_option_id);
      }
    });

    $('.add-new-field-options').on('click', function() {
      let add_option_id = this.id;
      let input_option_id = add_option_id.replace('add', 'input');
      let list_option_id = add_option_id.replace('add', 'list');
      let hidden_option_id = add_option_id.replace('add', 'hidden');
      add_element_to_list(input_option_id, list_option_id, hidden_option_id);
    });

    $('.list-field-options').on('change', function(event) {
      let $me = $(this);
      let value = event.target.value;
      let option = "option[value='" + value + "']";
      $me.find(option).remove();
      $me.val('instruction');

      let list_option_id = event.target.id;
      let hidden_option_id = list_option_id.replace('list', 'hidden');
      let hidden_input = $('#' + hidden_option_id);

      let options_list = hidden_input.val().split(',');
      let options_filter = options_list.filter(option => option !== value);
      let options_as_text = options_filter.join(',');
      hidden_input.val(options_as_text);
      check_change(hidden_input);
    });

    $('#copy-shortcode').on('click', function() {
      let form_shortcode = this.nextElementSibling;
      let copyButton = this;

      const buttonWidth = copyButton.offsetWidth;
      copyButton.style.width = buttonWidth + 'px';
      copyButton.value = js_translation.copied_shortcode;
      form_shortcode.select();
      navigator.clipboard.writeText(form_shortcode.value);
      form_shortcode.blur();

      $(copyButton).addClass('success');
      setTimeout(function () {
        $(copyButton).removeClass('success');
        copyButton.value = js_translation.copy_shortcode_button;
      }, 2000);
    });

    $('button.field-type').on('click', function () {
      $('#submit-new-field').attr('disabled', false);
    });
  });
})(jQuery);
