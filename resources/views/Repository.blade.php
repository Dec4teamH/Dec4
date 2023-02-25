<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Repositories') }}
            </h2>
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
                <div>
                    @foreach ($repositories as $repository)
                        <ul>
                            <li>
                                <div class="p-6 bg-white border-b border-gray-200">
                                    <a href="{{ route('detail.show', $repository->id) }}">{{ $repository->repos_name }}</a>
                                </div>
                            </li>
                        </ul>
                    @endforeach
                </div>
            </div>
</x-app-layout>
