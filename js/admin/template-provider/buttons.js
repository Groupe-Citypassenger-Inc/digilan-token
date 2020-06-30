(function ($) {
	window.resetButtonToDefault = function (id) {
		var defaultButtonValues = {
			'#login_label': button_values.login_label,
			'#link_label': button_values.link_label,
			'#unlink_label': button_values.unlink_label,
			'#custom_default_button': button_values.default_button,
			'#custom_icon_button': button_values.icon_button
		};

		var $CodeMirror = jQuery(id).val(defaultButtonValues[id]).siblings(
			'.CodeMirror').get(0);
		if ($CodeMirror && typeof $CodeMirror.CodeMirror !== 'undefined') {
			$CodeMirror.CodeMirror.setValue(defaultButtonValues[id]);
		}
		return false;
	};

	$(document)
		.ready(
			function () {
				$('#custom_default_button_enabled')
					.on(
						'change',
						function () {
							if ($(this).is(':checked')) {
								$(
									'#custom_default_button_textarea_container')
									.css('display', '');

								var $CodeMirror = jQuery(
									'#custom_default_button')
									.siblings('.CodeMirror')
									.get(0);
								if ($CodeMirror
									&& typeof $CodeMirror.CodeMirror !== 'undefined') {
									$CodeMirror.CodeMirror
										.refresh();
								}
							} else {
								$(
									'#custom_default_button_textarea_container')
									.css('display', 'none');
							}
						});

				$('#custom_icon_button_enabled')
					.on(
						'change',
						function () {
							if ($(this).is(':checked')) {
								$(
									'#custom_icon_button_textarea_container')
									.css('display', '');

								var $CodeMirror = jQuery(
									'#custom_icon_button')
									.siblings('.CodeMirror')
									.get(0);
								if ($CodeMirror
									&& typeof $CodeMirror.CodeMirror !== 'undefined') {
									$CodeMirror.CodeMirror
										.refresh();
								}
							} else {
								$(
									'#custom_icon_button_textarea_container')
									.css('display', 'none');
							}
						});
			});
})(jQuery);