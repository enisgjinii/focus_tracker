<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activity Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-1.13.8/af-2.6.0/b-2.4.2/b-colvis-2.4.2/b-html5-2.4.2/b-print-2.4.2/cr-1.7.0/date-1.5.1/fc-4.3.0/fh-3.4.0/kt-2.11.0/r-2.5.0/rg-1.4.1/rr-1.4.1/sc-2.3.0/sb-1.6.0/sp-2.2.0/sl-1.7.0/sr-1.3.0/datatables.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-1.13.8/af-2.6.0/b-2.4.2/b-colvis-2.4.2/b-html5-2.4.2/b-print-2.4.2/cr-1.7.0/date-1.5.1/fc-4.3.0/fh-3.4.0/kt-2.11.0/r-2.5.0/rg-1.4.1/rr-1.4.1/sc-2.3.0/sb-1.6.0/sp-2.2.0/sl-1.7.0/sr-1.3.0/datatables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500&display=swap" rel="stylesheet">
  <style>
    * {
      font-family: 'Inter', sans-serif;
    }

    body {
      padding-top: 70px;
    }
  </style>
</head>

<body>
  <?php include 'partials/navbar.php'; ?>

  <div class="container mt-5">
    <h2>Application Usage Data</h2>
    <table id="appUsageTable" class="table table-striped table-bordered w-100">
      <thead>
        <tr>
          <th>Application</th>
          <th>Duration (seconds)</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <!-- DataTables will populate the table body -->
      </tbody>
    </table>

    <div class="mt-5">
      <h2>Application Usage Chart</h2>
      <canvas id="appUsageChart" width="400" height="200"></canvas>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      var table = $('#appUsageTable').DataTable({
        ajax: {
          url: 'application_usage.json',
          dataSrc: ''
        },
        columns: [{
            data: 'Application'
          },
          {
            data: 'Duration',
            render: function(data, type, row) {
              return type === 'display' ? formatDuration(data) : data;
            },
          },
          {
            data: "Date",
          }
        ],
        columnDefs: [{
          targets: [0, 1],
          render: function(data, type, row) {
            return type === 'display' && data !== null ? '<div style="white-space: normal;">' + data + '</div>' : data;
          }
        }],
        initComplete: function() {
          updateChart();
        }
      });

      // Reload the table data every 10 seconds
      // setInterval(function() {
      //   table.ajax.reload(null, false);
      // }, 1000);

      // Chart.js
      var ctx = document.getElementById('appUsageChart').getContext('2d');
      var chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [], // Labels will be populated dynamically
          datasets: [{
            label: 'Duration (seconds)',
            data: [],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            },

          }
        }
      });

      // Update chart data when DataTable is redrawn
      $('#appUsageTable').on('draw.dt', function() {
        updateChart();
      });

      function updateChart() {
        var data = table.rows().data().toArray();

        // Sort data by timestamp, replace 'Timestamp' with the actual field name
        data.sort(function(a, b) {
          return new Date(b.Timestamp) - new Date(a.Timestamp);
        });

        var labels = data.map(function(row) {
          return row.Application;
        });
        var durations = data.map(function(row) {
          return row.Duration;
        });

        // Display only the last ten data points
        var lastTenLabels = labels.slice(0, 10); // Select the first ten entries
        var lastTenDurations = durations.slice(0, 10);

        chart.data.labels = lastTenLabels.reverse(); // Reverse the order for proper display
        chart.data.datasets[0].data = lastTenDurations.reverse();
        chart.update();
      }

      function formatDuration(durationInSeconds) {
        var hours = Math.floor(durationInSeconds / 3600);
        var minutes = Math.floor((durationInSeconds % 3600) / 60);
        var seconds = Math.floor(durationInSeconds % 60);

        var formattedDuration = '';

        if (hours > 0) {
          formattedDuration += hours + 'h ';
        }

        if (minutes > 0) {
          formattedDuration += minutes + 'm ';
        }

        if (seconds > 0) {
          formattedDuration += seconds + 's';
        }

        return formattedDuration.trim();
      }

    });
  </script>
</body>

</html>