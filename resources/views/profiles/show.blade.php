@extends('layouts.app')

@section('content')
    <div class="max-w-7xl container mx-auto px-4 py-8">
        <div class="lg:flex lg:space-x-8">
            <div class="flex items-center justify-center gap-2">
                <h1>{{ $user->name }}</h1>
                <small>since: {{ $user->created_at->diffForHumans() }}</small>
            </div>
        </div>
    </div>
@endsection
