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
              <a href="{{ route('issue.index', $id) }}">
                  {{ __('Issue') }}
              </a>
          </div>
        </div>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{ __('You are successful!!')}}
            </div>
        </div>
        <div>
            <canvas id="myChart"></canvas>
        </div>
        <script>
            // 受け取った変数をjsに渡す
            // ghアカウント名(labels用)
            const acounts = @json($members);

            // 過去一週間分の日付(datasets->label用)
            const day0 = `{{ $weeks[0] }}`;
            const day1 = `{{ $weeks[1] }}`;
            const day2 = `{{ $weeks[2] }}`;
            const day3 = `{{ $weeks[3] }}`;
            const day4 = `{{ $weeks[4] }}`;
            const day5 = `{{ $weeks[5] }}`;
            const day6 = `{{ $weeks[6] }}`;

            // プルリク数(datasets->data用)
            const count0 = @json($counts[0]);
            const count1 = @json($counts[1]);
            const count2 = @json($counts[2]);
            const count3 = @json($counts[3]);
            const count4 = @json($counts[4]);
            const count5 = @json($counts[5]);
            const count6 = @json($counts[6]);
        </script>    

    </div>    
</x-app-layout>
