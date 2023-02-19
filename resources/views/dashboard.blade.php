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
                    <input type="text" name='access_token' style="width:600px">
                    <button type='submit'> Create</button>
                </form>
                <div style="display: flex">
                    <ul>
                        @isset($gh_names)
                            @foreach ($gh_names as $gh_name)
                                <li class='flex'> <a href="{{ route('organization.show', $gh_name[0]->id) }}">
                                        {{ $gh_name[0]->acunt_name }}</a>


                                    <!-- 🔽 削除ボタン -->
                                    <form action="{{ route('dashboard.destroy', $gh_name[0]->id) }}" method="POST"
                                        onsubmit=" return alert()" class="text-left">
                                        @method('delete')
                                        @csrf
                                        <button type="submit
                                            class="mr-2 ml-2
                                            text-sm hover:bg-gray-200 hover:shadow-none text-white py-1 px-2
                                            focus:outline-none focus:shadow-outline">
                                            <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24"
                                                stroke="black">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        @endisset
                    </ul>
                </div>
            </div>
            <script src="{{ asset('/js/app.js') }}"></script>
</x-app-layout>
