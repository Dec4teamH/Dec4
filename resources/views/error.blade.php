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
                    {{ __('下のリンクからリロードしてください') }}
                </div>
                <a href="https://github.com/login/oauth/authorize?client_id=6e4baa33ed2b392eb5e4&scope=user">Log in
                    with GitHub</a>
            </div>
        </div>
    </div>
</x-app-layout>
{{-- このページは簡易的なものです --}}
{{-- リロードボタンを作るか要検討 --}}
