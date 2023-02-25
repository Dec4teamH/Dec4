<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @include('common.errors')
                <div class="p-6 bg-white border-b border-gray-200">
                    <p>{{ __('Githubのaccess tokenを入力してください') }}</p>
                    <form action="{{ route('dashboard.store') }}" method="post">
                        @csrf
                        <div class="flex flex-col mb-4">
                            <input type="text" name='access_token' style="width:800px">
                        </div>
                        <div class="flex">
                            <button type='submit'
                                class="bg-green-500  font-medium text-sm mx-2 py-1 px-2 w-28 rounded-full">
                                {{ __('Create') }}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="p-6 bg-white border-b border-gray-200">
                    <ul>
                        @isset($gh_names)
                            @foreach ($gh_names as $gh_name)
                                <li class="flex justify-between">
                                    <a href="{{ route('organization.show', $gh_name[0]->id) }}">
                                        {{ $gh_name[0]->acunt_name }}
                                    </a>

                                    <!-- 🔽 削除ボタン -->
                                    <form action="{{ route('dashboard.destroy', $gh_name[0]->id) }}" method="POST"
                                        onsubmit=" return alert()" class="text-left">
                                        @method('delete')
                                        @csrf
                                        <button type="submit"
                                            class="bg-red-500 font-medium text-sm text-white mx-2 py-1 px-2 w-28 rounded-full">
                                            {{ __('Delete') }}
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
