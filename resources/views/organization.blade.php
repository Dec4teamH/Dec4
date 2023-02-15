<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div>
                    <ul>
                        @foreach ($gh_profs as $gh_prof)
                            <li>
                                <a href="{{ route('repository.show', $gh_prof->id) }}">
                                    {{ $gh_prof->acunt_name }}</a>
                            </li>
                        @endforeach
                        <li><a href="{{ route('repository.show', $gh_prof->id) }}">personal
                                repositories</a>
                        </li>
                    </ul>
                </div>
            </div>
</x-app-layout>
