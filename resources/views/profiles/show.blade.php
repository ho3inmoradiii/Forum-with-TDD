@extends('layouts.app')

@section('content')
    <div class="min-h-screen max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="lg:flex flex-col gap-6">
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 shadow-lg rounded-xl p-6 flex items-center gap-4 animate-fade-in">
                <div class="relative group">
                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim($user->email))) }}?d=mp" class="w-16 h-16 rounded-full transition-transform duration-300 group-hover:scale-110" alt="{{ $user->name }}">
                    <div class="absolute inset-0 rounded-full border-2 border-blue-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <small class="text-sm text-gray-600 italic">Since: {{ $user->created_at->diffForHumans() }}</small>
                </div>
            </div>

            <!-- Activities Section -->
            <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100 animate-fade-in">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Activities</h2>
                <profile-threads
                    :user-with-activities="{{ json_encode($user) }}"
                    :user-id="{{ (int)auth()->id() }}"
                >
                </profile-threads>
            </div>
        </div>
    </div>
@endsection

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }
</style>
