(function ($) {
  $(document).ready(function () {
    $('#lang-search').on('input', function(input) {
      let search = input.target.value.toLowerCase().trim();
      let list_items = $("#language_list li");
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
      $.ajax({
        type: 'POST',
        data: {
          _ajax_nonce: user_form_data._ajax_nonce,
          action: 'digilan-token-form-language-settings',
          lang: lang,
        },
        dataType: 'json',
        url: ajaxurl,
        success: function () {
          location.reload();
        },
        error: function () {
          alert( "Sorry, we could not add this language, try again later !" );
        },
      });
    };

    $('.lang-flag-delete').click(function() {
      update_language(this.name)
    });

    $('#language_list li').click(function(){
      let lang = $(this).find('img').attr('value');
      update_language(lang)
    });

    $('.lang-select').click(function () {
      $('.language_list_container').show();
    });

    $('.lang-select').focusout(function () {
      // Timeout required, otherwise, pop-up list close before item click handled
      setTimeout(function () {
        $('.language_list_container').hide();
      }, 100);
    });

    $('.form-settings-field-row').on('click', function(value) {
      let currentElement = value.target;
      if (currentElement.classList.contains("delete-field")) {
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
      let row = this.closest(".form-settings-field-row");

      if (isChecked) {
        $(row).addClass('delete-in-progress');
      } else {
        $(row).removeClass('delete-in-progress');
      }
    });

    $('.update-field').on('input', function (value) {
      let row = this.closest("div[name='field-row']");
      let resetButton = $(row).find("input[name='reset-changes-button']")[0];

      $(row).addClass('update-in-progress');
      $(resetButton).attr('disabled', false);

      let fields = $(row).find("input[type='text']");
      let isNoChanges = true;
      for (let i = 0; (i < fields.length) && (isNoChanges); i++) {
        let [prefix, property, field_name, lang] = fields[i].name.split('/');
        isNoChanges = fields[i].value === user_form_fields[field_name][property][lang];
      }

      if (isNoChanges) {
        $(resetButton).attr('disabled', true);
        $(row).removeClass('update-in-progress');
      }
    });

    $('input[name="reset-changes-button"]').on('click', function(value) {
      let row = this.closest("div[name='field-row']");
      $(row).removeClass('update-in-progress');
      $(this).attr('disabled', true);

      let fields = $(row).find("input[type='text']");
      for (let i = 0; i < fields.length; i++) {
        let [prefix, property, field_name, lang] = fields[i].name.split('/');
        fields[i].value = user_form_fields[field_name][property][lang] || '';
      }
    });

    $('#btns-field-type-choice button').on('click', function(value) {
      $('button').removeClass('selected');
      $(this).addClass('selected');

      $('.new_field_row').css('display', 'table-row');
      $('label[name="name"]').css('display', 'block');
      $('label[name="instruction"]').css('display', 'block');

      $('label[name="options"]')
        .css('display', 'none')
        .children('.required_input')
        .removeAttr("required");

      $('label[name="regex"]').css('display', 'none');
      $('label[name="unit"]').css('display', 'none');

      $('#multiple').css('display', 'none');

      let type = this.name;
      document.getElementById("new-field-type").value = type;
      switch (type) {
        case 'text':
        case 'email':
        case 'tel':
        case 'checkbox':
          break;
        case 'number':
          $('label[name="unit"]').css('display', 'block');
          break;
        case 'radio':
          $('label[name="options"]')
            .css('display', 'block')
            .children('.required_input')
            .attr("required", true);
          break;
        case 'select':
          $('#multiple').css('display', 'table-row');
          $('label[name="options"]')
            .css('display', 'block')
            .children('.required_input')
            .attr("required", true);
          break;
      }
    });

    $('#copy-shortcode').on('click', function() {
      let copyText = document.getElementById("form-shortcode");
      let copyButton = document.getElementById("copy-shortcode");

      const buttonWidth = copyButton.offsetWidth;
      copyButton.style.width = `${buttonWidth}px`;
      copyButton.value = js_translation.copied_shortcode;
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(copyText.value);
      copyText.blur();

      $(this).addClass('success');
      setTimeout(function () {
        $(this).removeClass('success');
        copyButton.value = js_translation.copy_shortcode_button;
      }, "2000");
    });

    $('button.field_type').on('click', function () {
      $('#submit-new-field').attr("disabled", false);
    });
  });
})(jQuery);
