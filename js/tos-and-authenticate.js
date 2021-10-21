(function ($) {
	$(document).ready(function () {
		$(".dlt-auth").click(function (e) {
			if (!$("#dlt-tos").prop("checked")) {
				$("#dlt-tos").focus();
				e.preventDefault();
				return false;
			}
			var loader = '<div class="page_loader"><img src="'+url_img+'" alt="loader" /></div>';
			$('#dlt-center').replaceWith(loader);
			setTimeout(function() {
				document.location.reload();
			}, 15000);
		})
	});
})(jQuery);
