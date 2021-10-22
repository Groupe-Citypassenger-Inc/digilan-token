(function ($) {
	$(document).ready(function () {
		$(".dlt-auth").click(function (e) {
			if (!$("#dlt-tos").prop("checked")) {
				$("#dlt-tos").focus();
				e.preventDefault();
				return false;
			}
			if ($(this).attr('id') == "dlt-mail-btn") {
				$('form#dlt-mail-form').submit();
			}
			var loader = '<div class="page_loader"><img src="'+url_img+'" alt="loader" /></div>';
			$('#dlt-center').replaceWith(loader);
		})
	});
})(jQuery);
