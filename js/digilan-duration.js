(function ($) {
  $(document).ready(function () {
    moment.locale(digilan_duration.locale);
    var time_to_next_opening = moment.duration(digilan_duration.duration).humanize(true);
    $('#digilan-token-closed-message').append(time_to_next_opening);
  });
})(jQuery);
