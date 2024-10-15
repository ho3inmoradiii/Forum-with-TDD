@extends('layouts.app')

@section('title', $thread->title)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg space-y-4 p-4">
            <article>
                <h2 class="text-xl font-semibold mb-2">
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold transition duration-300 ease-in-out">
                        {{ $thread->user->name }}
                    </a>
                    Posted:
                    {{ $thread->title }}
                </h2>
                <p class="text-gray-700">
                    {{ $thread->body }}
                </p>
            </article>
        </div>

        <div class="mt-8">
            <h3 class="text-xl font-semibold mb-4">Replies</h3>
            @forelse ($thread->replies as $reply)
                @include('threads.reply')
            @empty
                <p class="text-gray-500 italic">No replies yet.</p>
            @endforelse
        </div>
    </div>
@endsection
