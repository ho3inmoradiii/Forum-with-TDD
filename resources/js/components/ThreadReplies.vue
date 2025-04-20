<template>
    <div>
        <div v-for="reply in replies" :key="reply.id" class="mb-4 p-4 bg-gray-50 rounded-lg shadow-md border border-gray-100">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <img class="h-12 w-12 rounded-full" :src="avatarUrl(reply)" :alt="reply.user.name">
                </div>
                <div class="flex-grow">
                    <div class="flex flex-row-reverse justify-between items-center gap-4">
                        <div class="w-8 h-8">
                            <PhHeart class="cursor-pointer" :size="32" :color="reply.is_favorited ? 'hotpink' : 'gray'" :weight="reply.is_favorited ? 'fill' : 'duotone'" @click="toggleFavorite(reply)" />
                        </div>
                        <p class="text-gray-700 leading-relaxed">{{ reply.body }}</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-600 flex items-center gap-2">
                        Posted by
                        <a :href="profileUrl(reply)" class="text-blue-600 hover:text-blue-800 font-semibold transition duration-300 ease-in-out">
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
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg" role="alert" v-else>
            <p>Please <a href="/login" class="font-bold underline hover:text-yellow-900">log in</a> to post a reply.</p>
        </div>
    </div>
</template>

<script>
import ReplyForm from './ReplyForm.vue';
import { PhHeart } from "@phosphor-icons/vue";
import axios from 'axios';
import { toast } from 'vue3-toastify';

export default {
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
        },
        initialReplyCount: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            replies: this.initialReplies.map(reply => ({
                ...reply,
                is_favorited: reply.is_favorited || false
            })),
            replyCount: this.initialReplyCount
        };
    },
    methods: {
        profileUrl(reply) {
            return `/profile/${reply.user.name}`;
        },
        async toggleFavorite(reply) {
            if (this.isAuthenticated) {
                try {
                    if (!reply.is_favorited) {
                        await axios.post(`/replies/${reply.id}/favorite`);
                        reply.is_favorited = true;
                        toast.success('Reply successfully added to favorites.');
                    } else {
                        await axios.delete(`/replies/${reply.id}/favorite`);
                        reply.is_favorited = false;
                        toast.success('Reply successfully removed from favorites.');
                    }
                } catch (error) {
                    console.error('Error favorite reply:', error);
                    toast.error('Something went wrong. Please try again.');
                }
            } else {
                toast.error('To favorite a reply, you must log in.');
                setTimeout(function() { window.location.href = '/login' }, 2000);
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
            this.replyCount++;
            console.log('Emitting reply-count-updated with count:', this.replyCount);
            window.emitter.emit('reply-count-updated', this.replyCount);
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
