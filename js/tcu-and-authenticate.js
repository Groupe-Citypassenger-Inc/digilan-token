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
				$('.page_loader').css("display","block");
				$("#dlt-gtu").css("display","none");
				setTimeout(function() {
					if (document.readyState === 'complete') {
						$('.page_loader').css("display","none");
						$("#dlt-gtu").css("display","block");
						clearTimeout(this);
						isClicked=false;
					} else {
						document.location.reload();
					}
				}, 15000);
			}
		})
	});
})(jQuery);
