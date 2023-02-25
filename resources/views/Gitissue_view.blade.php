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
                <div class="p-6 bg-white border-b border-gray-200"><canvas id="myissue" height="100px"></div>
                <div id='calendar'></div>
                @php
                    $date = date('w');
                    $today = date('Y-m-d', strtotime('-' . $date . 'day'));
                    $i = 0;
                @endphp
                <div>
                    <div>openされてから放置されています</div>
                    @foreach ($calendar[7] as $open_at)
                        @if ($open_at->open_date < $today)
                            <div class="p-6 bg-white border-b border-gray-200">
                                <a href={{ $calendar[10][$i] }}>title:{{ $open_at->title }}.
                                    .assignee:{{ $calendar[13][$i] }}</a>
                            </div>
                        @endif
                        @php
                            $i++;
                        @endphp
                    @endforeach
                </div>
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
            title: `title:{{ $calendar[0][$i] }}
                    assignee:{{ $calendar[11][$i] }}`,
            start: `{{ $calendar[1][$i] }}`,
            allDay: true,
            borderColor: "#000",
            color: "#f00",
            url: `{{ $calendar[8][$i] }}`,
        };
    </script>
@endfor
@for ($i = 0; $i < $calendar[6]; $i++)
    <script>
        events[`{{ $i + $calendar[5] }}`] = {
            title: `title:{{ $calendar[2][$i] }}
                    assignee:{{ $calendar[12][$i] }}`,
            start: `{{ $calendar[3][$i] }}`,
            end: `{{ $calendar[4][$i] }}`,
            url: `{{ $calendar[9][$i] }}`,
        };
    </script>
@endfor
<script type="text/javascript">
    <?php
    $json_weeks = json_encode($weeks);
    $json_ratios = json_encode($ratios);
    $json_start_ratios = json_encode($start_ratios);
    $json_open_count = json_encode($open_totals);
    ?>

    var labels = <?php echo $json_weeks; ?>;
    var op_ratios = <?php echo $json_ratios; ?>;
    var start_ratios = <?php echo $json_start_ratios; ?>;
    var op_totals = <?php echo $json_open_count; ?>;

    const data = {
        labels: labels,
        datasets: [{
                type: 'bar',
                label: 'Open issue count',
                backgroundColor: 'rgba(255, 99, 132,0.5)',
                borderColor: 'rgba(255, 99, 132,0.5)',
                data: op_totals,
                yAxisID: 'open_total',
            },
            {
                type: 'line',
                label: 'Start issue ratio',
                backgroundColor: 'rgb(160, 217, 262)',
                borderColor: 'rgb(160, 217, 262)',
                data: start_ratios,
                yAxisID: 'start_ratio',
            }
        ]
    };

    const config = {
        data: data,
        options: {
            responsive: true,
            scales: {
                'open_total': {
                    type: 'linear',
                    position: 'left',
                    min: 0,
                    stepSize: 1,
                },
                'start_ratio': {
                    id: 'start_ratio',
                    type: 'linear',
                    position: 'right',
                    max: 100,
                    min: 0,
                    stepSize: 1,
                }
            }
        }
    };


    const myChart = new Chart(
        document.getElementById('myissue'),
        config
    );
</script>
