@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="mb-4">Create New Thread</h1>
        <create-thread
            :channels="{{ json_encode($channels) }}"
        ></create-thread>
    </div>
@endsection
