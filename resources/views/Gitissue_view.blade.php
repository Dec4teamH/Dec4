<x-app-layout>
    <x-slot name="header">
        <div class="flex">
            <a href="{{ route('detail.show', $id) }}">
                {{ __('Index') }}
            </a>
            <!-- Pullrequest Links -->
            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <a href="/pullrequest/{{ $id }}">
                    {{ __('Pullrequest') }}
                </a>
            </div>
            <!-- Issue Link -->
            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <a href="{{ route('issue.show', $id) }}">
                    {{ __('Issue') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <canvas id="myissue" height="100px">


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
            <div id='calendar'></div>
        </div>
    </div>
    </div>
</x-app-layout>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let events = [];
</script>
@for ($i = 0; $i < $calendar[5]; $i++)
    <script>
        events[`{{ $i }}`] = {
            title: `{{ $calendar[0][$i] }}`,
            start: `{{ $calendar[1][$i] }}`,
            backgroundColor: 'green',
            borderColor: 'green'
        };
    </script>
@endfor
@for ($i = 0; $i < $calendar[6]; $i++)
    <script>
        events[`{{ $i }}`] = {
            title: `{{ $calendar[2][$i] }}`,
            start: `{{ $calendar[3][$i] }}`,
            end: `{{ $calendar[4][$i] }}`,

        };
    </script>
@endfor
<script type="text/javascript">
    <?php
    $json_weeks = json_encode($weeks);
    $json_ratios = json_encode($ratios);
    $json_start_ratios = json_encode($start_ratios);
    ?>

    var labels = <?php echo $json_weeks; ?>;
    var op_ratios = <?php echo $json_ratios; ?>;
    var start_ratios = <?php echo $json_start_ratios; ?>;

    const data = {
        labels: labels,
        datasets: [{
                label: 'Open issue ration',
                backgroundColor: 'rgb(255, 99, 132)',
                borderColor: 'rgb(255, 99, 132)',
                data: op_ratios,
            },
            {
                label: 'Start issue ration',
                backgroundColor: 'rgb(160, 217, 262)',
                borderColor: 'rgb(160, 217, 262)',
                data: start_ratios,
            }
        ]
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
        document.getElementById('myissue'),
        config
    );
</script>
