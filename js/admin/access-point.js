(function ($) {
    var loaded_parameters = dlt_ap;
    $(document).ready(function () {
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

        $("#dlt-select-all").on("change", function (e) {
            if ($(this).is(":checked")) {
                $(this).val(1);
                return;
            }
            $(this).val(0);
        });

        // Load initial values
        var hostname = $("#digilan-token-select-hostname").val();
        $("#digilan-token-ssid-input").val(loaded_parameters[hostname]['ssid']);
        $("#digilan-token-schedule").val(loaded_parameters[hostname]['schedule']);
        $("#digilan-token-country-input").val(loaded_parameters[hostname]['country_code']);
        var currentSchedule = $("#digilan-token-schedule").val();
        currentSchedule = JSON.parse(currentSchedule);
        $("#weekly-schedule").data('dayScheduleSelector').deserialize(currentSchedule);
        // Load values when another hostname is selected.
        $("#digilan-token-select-hostname").change(function () {
            var hostname = $(this).val();
            $("#digilan-token-ssid-input").val(loaded_parameters[hostname]['ssid']);
            $("#digilan-token-schedule").val(loaded_parameters[hostname]['schedule']);
            $("#digilan-token-country-input").val(loaded_parameters[hostname]['country_code']);
            var mac = loaded_parameters[hostname]['mac'];
            $("#digilan-portal-preview").attr('src', dlt_ap.url + "?digilan-token-action=hide_bar&mac=" + mac);
            var currentSchedule = $("#digilan-token-schedule").val();
            currentSchedule = JSON.parse(currentSchedule);
            $("#weekly-schedule").data('dayScheduleSelector').deserialize(currentSchedule);
        });
    });

})(jQuery);
