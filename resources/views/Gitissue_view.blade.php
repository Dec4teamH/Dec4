<html>

<head>

</head>

<body>

    <x-app-layout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <canvas id="myChart" height="100px">

                        <!-- <table border="1">
                    <thead>
                      <tr>
                        @foreach ($weeks as $week)
<th>{{ $week }}　</th>
@endforeach
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        @foreach ($ratios as $ratio)
<td style="text-align: center;">{{ $ratio }}% </td>
@endforeach
                      </tr>
                    </tbody>
                  </table> -->
                </div>
            </div>
        </div>
        </div>
    </x-app-layout>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script type="text/javascript">
        <?php
        $json_weeks = json_encode($weeks);
        $json_ratios = json_encode($ratios);
        ?>

        var labels = <?php echo $json_weeks; ?>;
        var users = <?php echo $json_ratios; ?>;

        const data = {
            labels: labels,
            datasets: [{
                label: 'Open issue ration',
                backgroundColor: 'rgb(255, 99, 132)',
                borderColor: 'rgb(255, 99, 132)',
                data: users,
            }]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    display: true,
                    text: 'Opne_Closeの割合'
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                    }
                }
            }
        };

        const myChart = new Chart(
            document.getElementById('myChart'),
            config
        );
    </script>

</html>
