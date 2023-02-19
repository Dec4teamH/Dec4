<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pullrequest') }}
        </h2>
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
            // コミットの数（仮置き）
            const day0 = `{{ $weeks[0] }}`;
            const day1 = `{{ $weeks[1] }}`;
            const day2 = `{{ $weeks[2] }}`;
            const day3 = `{{ $weeks[3] }}`;
            const day4 = `{{ $weeks[4] }}`;
            const day5 = `{{ $weeks[5] }}`;
            const day6 = `{{ $weeks[6] }}`;

            // サイクルの状態（仮置き）
            const sum0 = `{{ $counts[0][0]['sum'] }}`;
            const sum1 = `{{ $counts[1][0]['sum'] }}`;
            const sum2 = `{{ $counts[2][0]['sum'] }}`;
            const sum3 = `{{ $counts[3][0]['sum'] }}`;
            const sum4 = `{{ $counts[4][0]['sum'] }}`;
            const sum5 = `{{ $counts[5][0]['sum'] }}`;
            const sum6 = `{{ $counts[6][0]['sum'] }}`;
        </script>    

    </div>    
</x-app-layout>
