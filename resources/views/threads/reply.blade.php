<div class="bg-white shadow-md rounded-lg p-4 mb-4">
    <div class="flex items-start">
        <div class="flex-shrink-0 mr-3">
            <img class="h-10 w-10 rounded-full" src="https://www.gravatar.com/avatar/{{ md5($reply->user->email) }}?d=mp" alt="{{ $reply->user->name }}">
        </div>
        <div class="flex-grow">
            <p class="text-gray-700">{{ $reply->body }}</p>
            <div class="mt-2 text-sm text-gray-500">
                Posted by
                <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold text-sm transition duration-300 ease-in-out">
                    {{ $reply->user->name }}
                </a>
                <span class="mx-1">•</span>
                {{ $reply->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
