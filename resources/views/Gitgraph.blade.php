<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
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
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <a href="{{ route('issue.show', $id) }}">
                        {{ __('Issue') }}
                    </a>
                </div>
            </div>
            <div>
                <form action="{{ route('detail.edit', $id) }}" method="get">
                    @csrf
                    <button type="submit">
                        <svg class="h-8 w-8 text-green-500"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  
                            <polyline points="1 4 1 10 7 10" />  
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div id="graph" class="flex justify-center">
                    <canvas id='canvas' width="500" height="400" data="good"></canvas>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg my-2">
                    <p class="px-6 font-bold text-3xl text-gray-800 border-b border-gray-200">{{'評価'}}</p>
                    <div class="pb-6 bg-white">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h2 class="font-bold text-xl text-gray-800 leading-tight">{{'A : 非常に開発が進んでいます'}}</h2>
                        </div>
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h2 class="font-bold text-xl text-gray-800 leading-tight">{{'B : まずまず開発が進んでいます'}}</h2>
                        </div>
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h2 class="font-bold text-xl text-gray-800 leading-tight">{{'C : あまり開発が進んでいません'}}</h2>
                        </div>
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h2 class="font-bold text-xl text-gray-800 leading-tight">{{'D : 開発が進んでいません'}}</h2>
                        </div>
                    </div>

                    <p class="px-6 pt-6 font-bold text-3xl text-gray-800 border-b border-gray-200">{{'評価基準'}}</p>
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div>
                            {{'issueの割合'}}
                            <h2 class="font-bold text-xl text-gray-800 leading-tight">{{$evaluation['rate']*100}}%　　{{$evaluation['rate_state']}}</h2>
                        </div>
                    </div>
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div>
                            {{'issueの取り掛かる平均日'}}
                            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                                @if($evaluation['start_ave']=='None')
                                    {{$evaluation['start_ave']}}　　{{$evaluation['start_state']}}
                                @else
                                    {{$evaluation['start_ave']}}日　　{{$evaluation['start_state']}}
                                @endif
                            </h2>
                        </div>
                    </div>
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div>
                            {{'1日の平均プルリクエスト数'}}
                            <h2 class="font-bold text-xl text-gray-800 leading-tight">{{$evaluation['pullreq_ave']}}回　　{{$evaluation['pullreq_state']}}</h2>
                        </div>
                    </div>
                </div>
                <p class="px-6 pt-6 font-bold text-3xl text-gray-800 border-b border-gray-200">{{'コミット履歴'}}</p>
                <div class="px-6 py-2">
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
                // コミットの数（仮置き）
                const num = `{{ $evaluation['total_score'] }}`;
                // サイクルの状態（仮置き）
                const cicle_state = `{{ $evaluation['total_state'] }}`;
            </script>
            <script src="{{ asset('/js/graph.js') }}"></script>
</x-app-layout>
