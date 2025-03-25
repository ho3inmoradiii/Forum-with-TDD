@extends('layouts.app')

@section('title', 'Forum threads')

@section('content')
    <div class="max-w-7xl container mx-auto px-4 py-8">
        @if (session('message'))
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                {{ session('message') }}
            </div>
        @endif
        <h1 class="text-3xl font-bold my-6 text-center">Forum threads</h1>

        <div class="bg-white shadow-md rounded-lg space-y-4 p-4">
            @forelse ($threads as $thread)
                <article>
                    <div class="flex flex-col justify-between gap-4">
                        <div class="flex flex-row justify-between items-center">
                            <h2>
                                <a href="{{ route('threads.show', [$thread->channel->slug, $thread]) }}"
                                   class="text-blue-600 hover:text-blue-800 font-semibold text-lg transition duration-300 ease-in-out">
                                    {{ $thread->title }}
                                </a>
                            </h2>
                            <strong class="flex flex-row gap-2">
                                {{ $thread->replies_count }} {{ Str::plural('reply', $thread->replies_count) }}
                            </strong>
                        </div>
                        <p>
                            {{ $thread->body }}
                        </p>
                    </div>
                </article>
                <hr/>
            @empty
                <p class="text-center text-gray-500">No threads yet.</p>
            @endforelse
        </div>
    </div>
@endsection
