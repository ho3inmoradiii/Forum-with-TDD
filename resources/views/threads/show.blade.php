@extends('layouts.app')

@section('title', $thread->title)

@section('content')
    <div class="max-w-7xl container mx-auto px-4 py-8">
        <div class="lg:flex lg:space-x-8">
            <!-- Main content area (60% width on large screens) -->
            <div class="lg:w-3/5">
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
                        submit-url="{{ route('replies.store', [$thread->channel->slug, $thread]) }}"
                    ></thread-replies>
                </div>
            </div>

            <!-- Sidebar (40% width on large screens) -->
            <div class="lg:w-2/5 mt-8 lg:mt-0">
                <div class="bg-white shadow-md rounded-lg p-4">
                    <h3 class="text-lg font-semibold mb-4">Thread Info</h3>
                    <p>{{ \Illuminate\Support\Str::plural('Reply', $thread->replies_count) }}: {{ $thread->replies_count }}</p>
                    <p>Created: {{ $thread->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.threadId = {{ $thread->id }};
    </script>
@endpush
