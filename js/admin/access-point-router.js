(function ($) {
  $(document).ready(function () {
    $('#dlt-show-scheduler-router').click(function (e) {
      if ($('#weekly-schedule-router').css('display') == 'none') {
        $('#weekly-schedule-router').css('display', '');
      } else {
        $('#weekly-schedule-router').css('display', 'none');
      }
      if ($('#weekly-schedule-caption-router').css('display') == 'none') {
        $('#weekly-schedule-caption-router').css('display', '');
      } else {
        $('#weekly-schedule-caption-router').css('display', 'none');
      }
    });
    $('#weekly-schedule-router').dayScheduleSelector({
      stringDays: dlt_days,
    });

    $('#weekly-schedule-router').on('click', function (e) {
      var schedule = $(this).data('dayScheduleSelector').serialize();
      var schedule_serialized = JSON.stringify(schedule);
      $('#digilan-token-schedule-router').val(schedule_serialized);
    });

    // Load schedule
    var s = dlt.schedule;
    $('#digilan-token-schedule-router').val(s);
    var currentSchedule = $('#digilan-token-schedule-router').val();
    currentSchedule = JSON.parse(currentSchedule);
    $('#weekly-schedule-router').data('dayScheduleSelector').deserialize(currentSchedule);
  });
})(jQuery);
