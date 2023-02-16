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
                    @if ($error)
                        <p>リポジトリにアクセスできませんでした。</p>
                        <p>このリポジトリは、空である可能性または権限がこのユーザにないリポジトリです</p>
                </div>
            </div>
        </div>
    </div>
@else
    <canvas id='canvas' width="500" height="400" data="good"></canvas>

    <div>
        @if ($state === 'commit')
            <div>
                <select id="state" name="selectbox">
                    <option value="comgit mit">commit</option>
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
    <div>
        @php
            $i = 0;
        @endphp
        @foreach ($data['commit'] as $commit)
            @php
                $url = 'https://github.com/' . $data['user']->owner_name . '/' . $data['user']->repos_name . '/commit/' . $commit->sha;
            @endphp
            @if (array_key_exists(0 + $i, $data['merge']))
                @if ($data['merge'][0 + $i] === $commit)
                    @php
                        $i++;
                    @endphp
                    <a href={{ $url }} class="text-red-500">
                        <p>{{ $commit->message }}</p>
                    </a>
                @else
                    <a href={{ $url }}>
                        <p>{{ $commit->message }}</p>
                    </a>
                @endif
            @else
                <a href={{ $url }}>
                    <p>{{ $commit->message }}</p>
                </a>
            @endif
        @endforeach
    </div>
    </div>

    <script>
        // 受け取った変数をjsに渡す
        const state = `{{ $state }}`;
        // コミットの数（仮置き）
        const num = `{{ $data['count'] }}`;
        // サイクルの状態（仮置き）
        const cicle_state = "good";
    </script>
    <script src={{ asset('/js/graph.js') }}></script>
    <script src={{ asset('/js/app.js') }}></script>
    @endif
</x-app-layout>
