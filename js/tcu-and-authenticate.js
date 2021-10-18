(function ($) {
	$(document).ready(function () {
		var isClicked = false;
		$(".dlt-auth").click(function (e) {
			if (!$("#dlt-tos").prop("checked")) {
				$("#dlt-tos").focus();
				e.preventDefault();
				return false;
			}
			if (isClicked) {
				e.preventDefault();
			} else {
				isClicked=true;
				$('.dlt-auth').css("opacity",0.5);
			}
		})
	});
})(jQuery);
