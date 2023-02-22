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
                <div id="graph" class="flex justify-center">
                    <form action="{{ route('detail.edit', $id) }}" method="get">
                        @csrf
                        <button type="submit">更新</button>
                    </form>
                    <canvas id='canvas' width="500" height="400" data="good"></canvas>
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
                // コミットの数（仮置き）
                const num = `{{ $evaluation['score'] }}`;
                // サイクルの状態（仮置き）
                const cicle_state = `{{ $evaluation['state'] }}`;
            </script>
            <script src="{{ asset('/js/graph.js') }}"></script>
</x-app-layout>
