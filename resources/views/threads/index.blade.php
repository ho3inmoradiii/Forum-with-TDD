@extends('layouts.app')

@section('title', 'Forum threads')

@section('content')
    <div class="min-h-screen max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if (session('message'))
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg mb-6">
                {{ session('message') }}
            </div>
        @endif

        <h1 class="text-3xl font-bold text-gray-900 text-center mb-8">Forum Threads</h1>

        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">
            @forelse ($threads as $thread)
                <article class="p-4 bg-gray-50 rounded-lg mb-4 last:mb-0">
                    <div class="flex flex-col gap-3">
                        <div class="flex flex-row justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-800">
                                <a href="{{ route('threads.show', [$thread->channel->slug, $thread]) }}"
                                   class="hover:text-blue-600 transition duration-300 ease-in-out">
                                    {{ $thread->title }}
                                </a>
                            </h2>
                            <span class="text-sm text-gray-600">
                                {{ $thread->replies_count }} {{ Str::plural('reply', $thread->replies_count) }}
                            </span>
                        </div>
                        <p class="text-gray-700 leading-relaxed line-clamp-3">{{ $thread->body }}</p>
                    </div>
                </article>
            @empty
                <div class="text-center text-gray-600 text-lg font-medium py-8 bg-gray-100 rounded-lg">
                    No threads yet. Be the first to post!
                </div>
            @endforelse
        </div>
    </div>
@endsection
