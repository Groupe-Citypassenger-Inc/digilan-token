(function ($) {
  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    let age_column;
    if (settings.sInstance === 'connection-table'){
      age_column = 2;
    } else if (settings.sInstance === 'user-meta-table') {
      age_column = 1;
    }

    var start = Date.parse($('#dlt-start').val() + ' 00:00:00');
    var end = Date.parse($('#dlt-end').val() + ' 23:59:59');
    var apValidation = Date.parse(data[age_column]); // use data for the age column

    return (
      (isNaN(start) && isNaN(end)) ||
      (isNaN(start) && apValidation <= end) ||
      (start <= apValidation && isNaN(end)) ||
      (start <= apValidation && apValidation <= end)
    );
  });

  $(document).ready(function () {
    $('#dlt-start').on('change', function () {
      $('#dlt-start-date-connections').val($(this).val());
      $('#dlt-start-date-user-meta').val($(this).val());
    });
    $('#dlt-end').on('change', function () {
      $('#dlt-end-date-connections').val($(this).val());
      $('#dlt-end-date-user-meta').val($(this).val());
    });
    var data = dlt_data.datatable;
    var locale = dlt_datatables.locale;
    data = JSON.parse(data);
    if (locale === 'fr_FR') {
      var language = {
        url: dlt_datatables.url,
      };
    } else {
      language = {};
    }
    var digilanTokenTable = $('#connection-table').DataTable({
      data: data,
      columns: [
        { data: 'ap_mac' },
        { data: 'creation' },
        { data: 'ap_validation' },
        { data: 'authentication_mode' },
        { data: 'social_id' },
        { data: 'mac' },
      ],
      language: language,
      order: [[2, 'desc']],
      stateSave: true,
      initComplete: function () {
        this.api()
          .columns(3)
          .every(function () {
            var column = this;
            var auths = {
              Google: 'google',
              Facebook: 'facebook',
              Twitter: 'twitter',
              Transparent: 'transparent',
              Mail: 'mail',
            };
            var select = $('<select id="dlt-connection-table"><option value=""></option></select>')
              .appendTo($(column.header()))
              .on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? '^' + val + '$' : '', true, false).draw();
              })
              .on('click', function (e) {
                e.stopPropagation();
              });
            for (var k in auths) {
              select.append('<option value="' + auths[k] + '">' + k + '</option>');
            }
          });
      },
    });
    $('#dlt-connection-table').on('click', function (e) {
      e.stopPropagation();
    });
    $($.fn.dataTable.tables(true)).css('width', '100%');
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
    /*
     *
     *   USER META TABLE
     *
     */
    let user_meta = dlt_user_meta.datatable;
    user_meta = JSON.parse(user_meta);
    let digilanTokenUserMetaTable = $('#user-meta-table').DataTable({
      data: user_meta,
      columns: [
        { data: 'ap_mac' },
        { data: 'creation' },
        {
          data: 'gender',
          render: function (data, type) {
            if (data === null) {
              return 'N/A';
            }
            return data;
          },
        },
        {
          data: 'age',
          render: function (data, type) {
            if (data === null) {
              return 'N/A';
            }
            return data;
          },
        },
        {
          data: 'nationality',
          render: function (data, type) {
            if (data === null) {
              return 'N/A';
            }
            return dlt_user_meta.nationality_iso_code_to_country[data];
          },
        },
        {
          data: 'stay_length',
          render: function (data, type) {
            if (data === null) {
              return 'N/A';
            }
            return data;
          },
        },
        {
          data: 'user_info',
          render: function (data, type) {
            let json_data = JSON.parse(data);
            delete json_data.gender;
            delete json_data.age;
            delete json_data.nationality;
            delete json_data.stay_length;

            if (Object.keys(json_data).length === 0) {
              return 'N/A';
            }
            return JSON.stringify(json_data);
          },
        },
      ],
      language: language,
      stateSave: true,
    });
    $('#dlt-start, #dlt-end').on('change', function () {
      digilanTokenTable.draw();
      digilanTokenUserMetaTable.draw();
    });
    /*
     *
     *   PIE CHART
     *
     */
    var ctx = document.getElementById('repartitionPieChart').getContext('2d');
    var connections = dlt_data.pie_chart;
    var data = JSON.parse(connections);
    var repartitionPieChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Facebook', 'Twitter', 'Google', 'Transparent', 'Mail'],
        datasets: [
          {
            data: data,
            backgroundColor: [
              'rgba(66, 103, 178, 0.6)',
              'rgba(74, 179, 244, 0.6)',
              'rgba(220, 78, 65, 0.6)',
              'rgba(51, 255, 153, 0.6)',
              'rgba(243, 94, 36, 0.6)',
            ],
            borderColor: [
              'rgba(66, 103, 178, 1)',
              'rgba(74, 179, 244, 1)',
              'rgba(220, 78, 65, 1)',
              'rgba(51, 255, 153, 1)',
              'rgba(243, 94, 36, 1)',
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        title: {
          display: true,
          text: dlt_charts_labels.pie_chart.title,
        },
        tooltips: {
          callbacks: {
            label: function (tooltipItem, data) {
              var dataset = data.datasets[tooltipItem.datasetIndex];
              var total = dataset.data.reduce(function (
                previousValue,
                currentValue,
                currentIndex,
                array
              ) {
                previousValue = parseInt(previousValue);
                currentValue = parseInt(currentValue);
                return previousValue + currentValue;
              });
              var currentValue = dataset.data[tooltipItem.index];
              var percentage = Math.floor((currentValue / total) * 100 + 0.5);
              return percentage + '%';
            },
          },
        },
      },
    });
    /*  aps info */
    var $ulAPs = $('#aps-connections');
    var refDate = Date.now() / 1000;
    apok = '&#10003;';
    apnok = '&#10008;';
    for (apName in dlt_data.access_point) {
      d = new Date(dlt_data.access_point[apName]['date'] * 1000);
      apdate = d.toLocaleString();
      since = refDate - dlt_data.access_point[apName]['date'];
      picto = '<span style="color: #e11;">' + apnok + '</span>';
      input = '<input type=checkbox name="' + apName + '">';
      if (dlt_data.access_point[apName]['ignore']) {
        input = '<input checked type=checkbox name="' + apName + '">';
      }
      if (since < 1200) {
        picto = '<span style="color: #f70;">' + apok + '</span>';
        input = '';
      }
      if (since < 666) {
        picto = '<span style="color: #1e1;">' + apok + '</span>';
        input = '';
      }
      var ele = '<li>' + input + '<b>' + apName + '</b>&nbsp;:&nbsp;';
      ele += apdate + '&nbsp;<span style="">' + picto + '</span></li>';
      $ulAPs.append(ele);
    }
    if ($ulAPs.find('input').length == 0) {
      $('#submit-ignore-settings').prop('disabled', true);
    }

    /*
     *
     *   LINE CHART
     *
     */
    var lineChart = document.getElementById('connectionsChart').getContext('2d');
    var lineChartData = dlt_data.line_chart;
    var daylist = dlt_days;
    var today = new Date();
    var day = daylist[today.getDay()];
    var connectionCounts = JSON.parse(lineChartData);
    var listConnectedPerAP = {};
    var days = [];
    var datasets = [];
    var count = 0;
    var colors = [
      '#0073aa',
      '#0095bd',
      '#00b6b9',
      '#24d2a2',
      '#9ae983',
      '#f9f871',
      '#4372b4',
      '#6c6eb8',
      '#9168b4',
      '#b160a8',
      '#ca5a96',
      '#e49d23',
      '#009a8a',
    ];
    for (hostname in connectionCounts) {
      listConnectedPerAP[hostname] = [];
      for (d = 0; d < 7; ++d) {
        days[6 - d] = daylist[(today.getDay() - (d % 7) + 7) % 7];
        listConnectedPerAP[hostname][d] = connectionCounts[hostname][6 - d];
      }
      var color = colors[count++];
      var line = {
        data: listConnectedPerAP[hostname],
        label: hostname,
        borderColor: color,
        fill: false,
      };
      datasets.push(line);
    }
    var connectChart = new Chart(lineChart, {
      type: 'line',
      data: {
        labels: days,
        datasets: datasets,
      },
      options: {
        title: {
          display: true,
          text: dlt_charts_labels.line_chart.title,
        },
        scales: {
          xAxes: [
            {
              scaleLabel: {
                display: true,
                labelString: dlt_charts_labels.line_chart.xLabel,
              },
            },
          ],
          yAxes: [
            {
              scaleLabel: {
                display: true,
                labelString: dlt_charts_labels.line_chart.yLabel,
              },
              ticks: {
                suggestedMin: 0,
                beginAtZero: 0,
              },
            },
          ],
        },
      },
    });
  });
})(jQuery);
