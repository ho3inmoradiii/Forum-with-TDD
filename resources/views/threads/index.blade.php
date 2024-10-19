@extends('layouts.app')

@section('title', 'Forum threads')

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (session('message'))
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                {{ session('message') }}
            </div>
        @endif
        <h1 class="text-3xl font-bold my-6 text-center">Forum threads</h1>

        <div class="bg-white shadow-md rounded-lg space-y-4 p-4">
            @forelse ($threads as $thread)
                <div>
                    <article>
                        <a href="{{ route('threads.show', [$thread->channel->slug, $thread]) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-lg transition duration-300 ease-in-out">
                            {{ $thread->title }}
                        </a>
                        <p>
                            {{ $thread->body }}
                        </p>
                    </article>
                </div>
                <hr />
            @empty
                <p class="text-center text-gray-500">هیچ موضوعی یافت نشد.</p>
            @endforelse
        </div>
    </div>
@endsection
