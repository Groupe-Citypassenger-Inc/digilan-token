(function ($) {
    var loaded_parameters = dlt_ap;
    $(document).ready(function () {
        /**
         * Check the validity of the code to enable or not the submit button
         * @param {String} code The 4 char code to check
         * @param {String} button The button to activate if the code is correct
         */
        function check_code_validity(code, button) {
            const regex_for_activation_code = new RegExp("^[A-Z0-9]{4}$");
            if (regex_for_activation_code.test(code)) {
                $(button).prop('disabled', false);
            } else {
                $(button).prop('disabled', true);
            }
        }
        // Check the 4 char code when the plugin is NOT activated
        $('#digilan-token-activation-field').on('keyup', function() {
            check_code_validity($(this).val(), $('#submit-activation-code'));
        });
        // Check the 4 char code when the plugin is already activated
        $('#digilan-token-activation-wifi4eu-field').on('keyup', function() {
            check_code_validity($(this).val(), $('#submit-activation-wifi4eu'));
        });
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
        if (hostname) {
            $("#digilan-token-ssid-input").val(loaded_parameters[hostname]['ssid']);
            $("#digilan-token-schedule").val(loaded_parameters[hostname]['schedule']);
            $("#digilan-token-country-input").val(loaded_parameters[hostname]['country_code']);
        }
        var currentSchedule = $("#digilan-token-schedule").val();
        if (currentSchedule.length !== 0) {
            currentSchedule = JSON.parse(currentSchedule);
            $("#weekly-schedule").data('dayScheduleSelector').deserialize(currentSchedule);
        }
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
