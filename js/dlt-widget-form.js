(function ($) {
	function initColorPicker(widget) {
		widget.find('.dlt-color').wpColorPicker({
			change: _.throttle(function () {
				$(this).trigger('change');
			}, 3000)
		});
	}

	function onFormUpdate(event, widget) {
		initColorPicker(widget);
	}

	$(document).on('widget-added widget-updated', onFormUpdate);

	$(document).ready(function () {
		$('.widget-inside').each(function () {
			initColorPicker($(this));
		});
		$('#widgets-right .widget:has(.dlt-color)').each(function () {
			initColorPicker($(this));
		});
	});
}(jQuery));