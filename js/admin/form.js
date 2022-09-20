(function ($) {
  $(document).ready(function () {
    $('#new-field-type').on('change', function(value) {
      let type = value.target.value;
      if (type === "input") {
        $('#inputType').css('display', 'block');
      } else {
        $('#inputType').css('display', 'none');
      }
    });
    $('.form-settings-field-row').on('click', function(value) {
      currentElement = value.target;
      sibling = currentElement.nextElementSibling;

      if (currentElement.localName === 'input') {
        return;
      }
      if (currentElement.localName === 'select') {
        return;
      }

      while (!sibling || !sibling.className.includes('edit-form-settings-field-row')) {
        currentElement = currentElement.parentElement;
        sibling = currentElement.nextElementSibling;
      };

      if (sibling.className.includes('row-visible')) {
        $(currentElement.nextElementSibling).removeClass('row-visible');
      } else {
        $(currentElement.nextElementSibling).addClass('row-visible');
      };
    })
    $('#new-field-name').on('keyup', function () {
      if ($(this).val() === "") {
        console.log("vide");
      } else {
        console.log("salut");
      }
    });

    $('.delete-field').on('click', function (value) {
      let isChecked = value.target.checked
      let row = value.target.closest(".form-settings-field-row");

      if (isChecked) {
        $(row).addClass('delete-in-progress');
      } else {
        $(row).removeClass('delete-in-progress');
      }
    });
    $('#button-choice button').on('click', function(value) {
      $('button').removeClass('selected');
      $(this).addClass('selected');
      $('#name').css('display', 'table-row');

      $('#options').css('display', 'none');
      $('#regex').css('display', 'none');
      $('#placeholder').css('display', 'none');
      $('#instruction').css('display', 'none');
      $('#multiple').css('display', 'none');

      $("#new-field-instruction").attr("required", false);
      $("#new-field-unit").attr("required", false);
      $("#new-field-regex").attr("required", false);
      $("#new-field-options").attr("required", false);

      let type = value.target.name;
      document.getElementById("new-field-type").value = type;
      switch (type) {
        case 'text':
          $('#instruction').css('display', 'table-row');
          $('#regex').css('display', 'table-row');
          $("#new-field-instruction").attr("required", true);
          $("#new-field-regex").attr("required", true);
          break;
        case 'email':
          $('#instruction').css('display', 'table-row');
          $("#new-field-instruction").attr("required", true);
          break;
        case 'tel':
          $('#instruction').css('display', 'table-row');
          $("#new-field-instruction").attr("required", true);
          break;
        case 'number':
          $('#instruction').css('display', 'table-row');
          $("#new-field-instruction").attr("required", true);
          break;
        case 'checkbox':
          $('#instruction').css('display', 'table-row');
          $("#new-field-instruction").attr("required", true);
          break;
        case 'radio':
          $('#instruction').css('display', 'table-row');
          $('#options').css('display', 'table-row');
          $("#new-field-instruction").attr("required", true);
          $("#new-field-options").attr("required", true);
          break;
        case 'select':
          $('#instruction').css('display', 'table-row');
          $('#options').css('display', 'table-row');
          $('#multiple').css('display', 'table-row');
          $("#new-field-instruction").attr("required", true);
          $("#new-field-options").attr("required", true);
          break;
      }
    });
    $('#copy-shortcode').on('click', function() {
      var copyText = document.getElementById("form-shortcode");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(copyText.value);
      copyText.blur();
      $(this).addClass('success');
      setTimeout(() => {
        $(this).removeClass('success');
      }, "2000");
    });
    $('button.field_type').on('click', function () {
      $('#submit-new-field').attr("disabled", false);
    });
  });
})(jQuery);

