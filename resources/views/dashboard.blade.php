<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
                <form action="{{ route('dashboard.store') }}" method="post">
                    @csrf
                    <input type="text" name='access_token'>
                    <button type='submit'> Create</button>
                </form>
                @isset($resJsonEmail)
                    <div>
                        <p>{{ $resJsonEmail[0]['email'] }}</p>
                        <p>
                        <ul>
                            <li>username {{ $resJsonUser['login'] }}</li>
                            <li>url {{ $resJsonUser['url'] }}</li>
                            <li>repos {{ $resJsonUser['repos_url'] }}</li>

                            {{-- commit --}}
                            <div>repos
                                @foreach ($resJsonRepos as $repo)
                                    <li>
                                        <div> {{ $repo['name'] }} {{ $repo['commits_url'] }}</div>
                                    </li>
                                @endforeach
                            </div>
                            @foreach ($resJsonCommits as $resJsonCommit)
                                <li>★{{ var_dump($resJsonCommit) }}</li>
                            @endforeach

                            {{-- issue --}}
                            @foreach ($resJsonIssues as $resJsonIssue)
                                <li>▲{{ var_dump($resJsonIssues) }}</li>
                            @endforeach

                            {{-- merge --}}
                            @foreach ($resJsonMerges as $resJsonMerge)
                                <li>■{{ var_dump($resJsonMerge) }}</li>
                            @endforeach
                            {{-- ここはuserのorganizationの権限の設定をしないとエラー --}}
                            <li>orgs {{ $resJsonUser['organizations_url'] }}</li>
                        </ul>
                        </p>
                    </div>
                @endisset
            </div>
        </div>
</x-app-layout>
