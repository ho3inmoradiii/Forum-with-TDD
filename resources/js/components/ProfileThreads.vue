<script>
import {PhHeart} from "@phosphor-icons/vue";

export default {
    name: "ProfileThreads",
    components: {PhHeart},
    props: {
        initialThreads: {
            type: Array,
            required: true
        },
        userId: {
            type: Number,
            required: true
        },
        isAuthenticated: {
            type: Boolean,
            required: true
        }
    },
    data() {
        return {
            threads: this.initialThreads
        };
    },
    methods: {
        threadsShow(thread) {
            return `/threads/${thread.channel}/${thread.id}`;
        },
        formatDate(dateString) {
            if (!dateString) return 'Unknown date';

            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            const diffInMinutes = Math.floor(diffInSeconds / 60);
            const diffInHours = Math.floor(diffInMinutes / 60);
            const diffInDays = Math.floor(diffInHours / 24);

            if (diffInSeconds < 60) {
                return 'Just now';
            } else if (diffInMinutes < 60) {
                return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
            } else if (diffInHours < 24) {
                return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
            } else if (diffInDays < 7) {
                return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
            } else {
                return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            }
        }
    }
}
</script>

<template>
    <div v-for="thread in threads" :key="thread.id">
        <article class="p-4 bg-gray-50 rounded-lg mb-4 last:mb-0">
            <div class="flex flex-col gap-3">
                <div class="flex flex-row justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-800">
                        <a :href="threadsShow(thread)"
                           class="hover:text-blue-600 transition duration-300 ease-in-out">
                            {{ thread.title }}
                        </a>
                    </h3>
                    <span class="text-sm text-gray-600">{{ formatDate(thread.created_at) }}</span>
                </div>
                <p class="text-gray-700 leading-relaxed line-clamp-3">{{ thread.body }}</p>
<!--                @if($user->id === auth()->id())-->
<!--                <form method="post" action="{{route('threads.destroy', ['thread' => $thread])}}">-->
<!--                    @method('DELETE')-->
<!--                    @csrf-->
<!--                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out">-->
<!--                        Delete Thread-->
<!--                    </button>-->
<!--                </form>-->
<!--                @endif-->
            </div>
        </article>
    </div>
</template>

<style scoped>

</style>
