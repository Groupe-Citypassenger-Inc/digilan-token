(function ($) {
	$(document).ready(function () {
		var isClicked = false;
		$(".dlt-auth").click(function (e) {
			if (!$("#dlt-tos").prop("checked")) {
				$("#dlt-tos").focus();
				e.preventDefault();
				return false;
			}
			$('.page_loader').css("display","block");
			$(".dlt-container").css("display","none");
			$("#dlt-gtu").css("display","none");
			setTimeout(function() {
				document.location.reload();
			}, 15000);
		})
	});
})(jQuery);
