<x-app-layout>
  <x-slot name="header">
      <div class="flex">
          <a href="{{ route('detail.show', $id) }}">
              {{ __('Index') }}
          </a>
          <!-- Pullrequest Links -->
          <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
              <a href="/pullrequest/{{$id}}">
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
        $json_start_ratios = json_encode($start_ratios);
        $json_open_count = json_encode($open_totals);
        ?>

        var labels = <?php echo $json_weeks; ?>;
        var op_ratios = <?php echo $json_ratios; ?>;
        var start_ratios = <?php echo $json_start_ratios; ?>;
        var op_totals = <?php echo $json_open_count; ?>;

        const data = {
            labels: labels,
            datasets: [
              {
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
                stepSize:1,
              },
              'start_ratio':{
                id: 'start_ratio',
                type: 'linear',
                position: 'right',
                max: 100,
                min: 0,
                stepSize: 0.5,
              }
            }
          }
        };
  
    const myChart = new Chart(
        document.getElementById('myChart'),
        config
    );
  
</script>
