@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-100 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="lg:flex flex-col gap-6">
            <!-- Profile Header -->
            <div class="bg-white shadow-lg rounded-xl p-6 flex items-center gap-4">
                <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim($user->email))) }}?d=mp" class="w-16 h-16 rounded-full" alt="{{ $user->name }}">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <small class="text-sm text-gray-500 italic">Since: {{ $user->created_at->diffForHumans() }}</small>
                </div>
            </div>

            <!-- Threads Section -->
            <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Threads</h2>
                @forelse ($user->threads as $thread)
                    <article class="p-4 bg-gray-50 rounded-lg mb-4 last:mb-0">
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-row justify-between items-center">
                                <h3 class="text-xl font-semibold text-gray-800">
                                    <a href="{{ route('threads.show', [$thread->channel->slug, $thread]) }}"
                                       class="hover:text-blue-600 transition duration-300 ease-in-out">
                                        {{ $thread->title }}
                                    </a>
                                </h3>
                                <span class="text-sm text-gray-600">{{ $thread->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-700 leading-relaxed line-clamp-3">{{ $thread->body }}</p>
                        </div>
                    </article>
                @empty
                    <div class="text-center text-gray-600 text-lg font-medium py-8 bg-gray-100 rounded-lg">
                        No threads yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
