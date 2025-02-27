@extends('layouts.app')

@section('title', $channel->name)

@section('content')
    <div class="max-w-7xl container mx-auto px-4 py-8">
{{--        <h1 class="text-3xl font-bold mb-6 text-center">{{ $channel->name }}</h1>--}}

        <div class="bg-white shadow-md rounded-lg space-y-4 p-4">
            @forelse ($channel->threads as $thread)
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
