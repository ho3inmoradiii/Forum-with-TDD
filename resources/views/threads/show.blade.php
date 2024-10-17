@extends('layouts.app')

@section('title', $thread->title)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg space-y-4 p-4">
            <article>
                <h2 class="text-xl font-semibold mb-2">
                    <a href="#"
                       class="text-blue-600 hover:text-blue-800 font-semibold transition duration-300 ease-in-out">
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
            <thread-replies
                :initial-replies="{{ json_encode($thread->replies) }}"
                :thread-id="{{ $thread->id }}"
                :user-id="{{ auth()->id() }}"
                :is-authenticated="{{ json_encode(Auth::check()) }}"
                submit-url="{{ route('replies.store', $thread) }}"
            ></thread-replies>
        </div>
    </div>
    <button
        type="submit"
        class="hidden items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
    >
        Post Reply
    </button>
@endsection

@push('scripts')
    <script>
        window.threadId = {{ $thread->id }};
    </script>
@endpush
