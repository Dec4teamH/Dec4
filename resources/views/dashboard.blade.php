<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('dashboard.store') }}" method="post">
                    @csrf
                    <input type="text" name='access_token'>
                    <button type='submit'> Create</button>
                </form>
                <div>
                    @isset($gh_names)
                        @foreach ($gh_names as $gh_name)
                            <ul>
                                <li><a href={{ route('dashboard.show', $gh_name) }}>{{ $gh_name }}</a></li>
                            </ul>
                        @endforeach
                    @endisset
                </div>
            </div>
</x-app-layout>
