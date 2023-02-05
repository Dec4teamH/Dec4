<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Repository') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div>
                    @foreach ($repositories as $repository)
                        <ul>
                            <li>
                                <form action="{{ route('commit.store', $repository->id) }}" method="POST">
                                    @csrf
                                    <button type="submit">{{ $repository->repos_name }}</button>
                                </form>
                            </li>
                        </ul>
                    @endforeach
                </div>
            </div>
</x-app-layout>
