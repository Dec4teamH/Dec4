<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div id="graph" class="flex justify-center">

                    <canvas id='canvas' width="500" height="400" data="good"></canvas>

                    <div>
                        @if ($state === 'commit')
                            <div>
                                <select id="state" name="selectbox">
                                    <option value="commit">commit</option>
                                    <option value="merge">merge</option>
                                </select>
                            </div>
                        @else
                            <div>
                                <select id="state" name="selectbox">
                                    <option value="commit">commit</option>
                                    <option value="merge" selected>merge</option>
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            <script>
                // 受け取った変数をjsに渡す
                const state = `{{ $state }}`;
                // コミットの数（仮置き）
                const num = `{{ $data['count'] }}`;
                // サイクルの状態（仮置き）
                const cicle_state = "good"
            </script>
            <script src={{ asset('/js/graph.js') }}></script>
            <script src={{ asset('/js/app.js') }}></script>
</x-app-layout>
