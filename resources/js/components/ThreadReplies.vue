<template>
    <div>
        <div v-for="reply in replies" :key="reply.id" class="mb-4 p-4 bg-white shadow rounded">
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-3">
                    <img class="h-10 w-10 rounded-full" :src="avatarUrl(reply)" :alt="reply.user.name">
                </div>
                <div class="flex-grow">
                    <div class="flex flex-row-reverse justify-between">
                        <PhHeart :size="32" :color="reply.is_favorited ? 'hotpink' : 'gray'" :weight="reply.is_favorited ? 'fill' : 'duotone'" @click="toggleFavorite(reply)" />
                        <p class="text-gray-700">{{ reply.body }}</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 rtl">
                        Posted by
                        <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold text-sm transition duration-300 ease-in-out">
                            {{ reply.user.name }}
                        </a>
                        <span class="mx-1">•</span>
                        {{ formatDate(reply.created_at) }}
                    </div>
                </div>
            </div>
        </div>

        <reply-form
            :thread-id="threadId"
            :user-id="userId"
            :submit-url="submitUrl"
            @reply-posted="addReply"
            v-if="isAuthenticated"
        ></reply-form>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert" v-else>
            <p>Please <a href="/login" class="font-bold underline">log in</a> to post a reply.</p>
        </div>
    </div>
</template>

<script>
import ReplyForm from './ReplyForm.vue';
import {PhHeart} from "@phosphor-icons/vue";
import axios from 'axios';

export default {
    created() {
        console.log(this.initialReplies, 'ppppppppp')
    },
    components: {
        PhHeart,
        ReplyForm
    },
    props: {
        initialReplies: {
            type: Array,
            required: true
        },
        threadId: {
            type: Number,
            required: true
        },
        userId: {
            type: Number,
            required: true
        },
        submitUrl: {
            type: String,
            required: true
        },
        isAuthenticated: {
            type: Boolean,
            required: true
        }
    },
    data() {
        return {
            replies: this.initialReplies
        };
    },
    methods: {
        async toggleFavorite(reply) {
            try {
                const response = await axios.post(`/replies/${reply.id}/favorite`);
            } catch (error) {
                console.error('Error favorite reply:', error);
            }
        },
        avatarUrl(reply) {
            const email = reply.user.email.toLowerCase().trim();
            const hash = this.simpleHash(email);
            return `https://www.gravatar.com/avatar/${hash}?d=mp`;
        },
        simpleHash(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32-bit integer
            }
            return Math.abs(hash).toString(16).padStart(32, '0');
        },
        addReply(newReply) {
            this.replies.push(newReply);
            console.log(this.replies, 'hi');
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
};
</script>
