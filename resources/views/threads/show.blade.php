@extends('layouts.app')

@section('title', $thread->title)

@section('content')
    <div class="min-h-screen max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="lg:flex lg:gap-8">
            <!-- Main content area -->
            <div class="lg:w-3/5">
                <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">
                    <article class="p-4 bg-gray-50 rounded-lg">
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">
                            <a href="{{ route('profile.show', $thread->user->name) }}" class="hover:text-blue-600 transition duration-300 ease-in-out">
                                {{ $thread->user->name }}
                            </a>
                            Posted: {{ $thread->title }}
                        </h2>
                        <p class="text-gray-700 leading-relaxed">{{ $thread->body }}</p>
                    </article>
                </div>

                <div class="mt-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Replies</h3>
                    <thread-replies
                        :initial-replies="{{ json_encode($thread->replies) }}"
                        :thread-id="{{ $thread->id }}"
                        :user-id="{{ auth()->id() }}"
                        :is-authenticated="{{ json_encode(Auth::check()) }}"
                        submit-url="{{ route('replies.store', [$thread->channel->slug, $thread]) }}"
                    ></thread-replies>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:w-2/5 mt-8 lg:mt-0 lg:sticky lg:top-4 lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">
                <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Thread Info</h3>
                    <p class="text-gray-700 font-medium">
                        {{ \Illuminate\Support\Str::plural('Reply', $thread->replies->count()) }}: {{ $thread->replies->count() }}
                    </p>
                    <p class="text-gray-700">
                        Created:
                        <span class="text-gray-600">{{ $thread->created_at->diffForHumans() }}</span>
                    </p>
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
