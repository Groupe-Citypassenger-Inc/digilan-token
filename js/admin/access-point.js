(function ($) {
    var loaded_parameters = dlt_ap;
    $(document).ready(function () {
        $("#dlt-select-all").on("change", function (e) {
            if ($(this).is(":checked")) {
                $(this).val(1);
                return;
            }
            $(this).val(0);
        });
        $("#digilan-token-select-hostname").change(function () {
            var hostname = $(this).val();
            $("#digilan-token-ssid-input").val(loaded_parameters[hostname]['ssid']);
            $("#digilan-token-lpage-input").val(loaded_parameters[hostname]['landing-page']);
            $("#digilan-token-schedule").val(loaded_parameters[hostname]['schedule']);
            $("#digilan-token-country-input").val(loaded_parameters[hostname]['country_code']);
            var mac = loaded_parameters[hostname]['mac'];
            $("#digilan-portal-preview").attr('src', dlt_ap.url + "?digilan-token-action=hide_bar&mac=" + mac);
            var currentSchedule = $("#digilan-token-schedule").val();
            currentSchedule = JSON.parse(currentSchedule);
            $("#weekly-schedule").data('dayScheduleSelector').deserialize(currentSchedule);
        });
        function handle_hostname() {
            const hostname = $("#digilan-token-select-hostname").val();
            if (hostname === null) {
                return;
            } else {
                $("#digilan-token-ssid-input").val(loaded_parameters[hostname]['ssid']);
                $("#digilan-token-lpage-input").val(loaded_parameters[hostname]['landing-page']);
                $("#digilan-token-schedule").val(loaded_parameters[hostname]['schedule']);
                $("#digilan-token-country-input").val(loaded_parameters[hostname]['country_code']);
            }
        }
        $("#dlt-show-scheduler").click(function (e) {
            if ($("#weekly-schedule").css('display') == 'none') {
                $('#weekly-schedule').css('display', '');
            } else {
                $('#weekly-schedule').css('display', 'none');
            }
            if ($("#weekly-schedule-caption").css('display') == 'none') {
                $("#weekly-schedule-caption").css('display', '');
            } else {
                $("#weekly-schedule-caption").css('display', 'none');
            }
        });
        $("#weekly-schedule").dayScheduleSelector({
            stringDays: dlt_days
        });
        $("#weekly-schedule").on("click", function (e) {
            var schedule = $(this).data("dayScheduleSelector").serialize();
            var schedule_serialized = JSON.stringify(schedule);
            $("#digilan-token-schedule").val(schedule_serialized);
        });
        $("#open_qrcode_modal").on("click", function() {
            $("#qrcode").empty();
            window.wfqr($("#digilan-token-ssid-input").val(), "", "nopass", document.getElementById("qrcode"));
            $("#qrcode-bg-modal").css("display", "flex");
        });
        $("#close_qrcode_modal").on("click", function() {
            $("#qrcode-bg-modal").css("display", "none");
        });
        function handle_current_schedule() {
            let currentSchedule = $("#digilan-token-schedule").val();
            if (currentSchedule.length === 0) {
                return;
            } else {
                currentSchedule = JSON.parse(currentSchedule);
                $("#weekly-schedule").data('dayScheduleSelector').deserialize(currentSchedule);
            }
        }
        handle_hostname();
        handle_current_schedule();
    });
})(jQuery);
