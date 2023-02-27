<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Repositories') }}
            </h2>
            <div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div>
                    @foreach ($repositories as $repository)
                        <ul>
                            <li>
                                <div class="p-6 bg-white border-b border-gray-200">
                                    <a
                                        href="{{ route('detail.show', $repository->id) }}">{{ $repository->repos_name }}</a>
                                </div>
                            </li>
                        </ul>
                    @endforeach
                </div>
            </div>
</x-app-layout>
