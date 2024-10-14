@extends('layouts.app')

@section('title', $thread->title)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg space-y-4 p-4">
            <article>
                <h2 class="text-xl font-semibold mb-2">
                    {{ $thread->title }}
                </h2>
                <p>
                    {{ $thread->body }}
                </p>
            </article>
        </div>
    </div>
@endsection
