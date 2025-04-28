<template>
    <div>
        <template v-if="replies.length > 0">
            <div v-for="reply in replies" :key="reply.id" class="mb-4 p-4 bg-gray-50 rounded-lg shadow-md border border-gray-100 flex flex-col items-start">
                <div class="flex items-start gap-4 w-full">
                    <div class="flex-shrink-0">
                        <img class="h-12 w-12 rounded-full" :src="avatarUrl(reply)" :alt="reply.user.name">
                    </div>
                    <div class="flex-grow">
                        <div class="flex flex-row-reverse justify-between items-start gap-4">
                            <div class="flex flex-row gap-2 items-center">
                                <div class="w-8 h-8">
                                    <PhHeart class="cursor-pointer" :size="32" :color="reply.is_favorited ? 'hotpink' : 'gray'" :weight="reply.is_favorited ? 'fill' : 'duotone'" @click="toggleFavorite(reply)" />
                                </div>
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
                <reply-form
                    :submit-url="getEditUrl(reply)"
                    :user-id="userId"
                    :thread-id="threadId"
                    v-if="isEditing === reply.id"
                    :reply="reply"
                    class="mt-3"
                    @reply-edited="editReply"
                    @cancel-edit="cancelEdit"
                />
                <div class="flex items-start border-t w-full pt-4 mt-4 gap-3" v-if="reply.user.id === userId">
                    <button
                        @click="showConfirm(reply)"
                        :disabled="isDeleting === reply.id"
                        class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 hover:shadow-md disabled:bg-gray-400 disabled:hover:cursor-not-allowed disabled:opacity-60"
                    >
                        <PhTrash :size="16" color="white" weight="bold" />
                        <span>{{ isDeleting === reply.id ? 'Deleting...' : 'Delete' }}</span>
                    </button>
                    <button
                        @click="isEditing = reply.id"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 hover:shadow-md"
                    >
                        <PhPencil :size="16" color="white" weight="bold" />
                        <span>{{ isEditing === reply.id ? 'Editing...' : 'Edit' }}</span>
                    </button>
                </div>
            </div>
        </template>
        <div v-else class="flex items-center gap-2 bg-gray-100 border-l-4 border-gray-300 text-gray-700 p-4 shadow-lg rounded-xl">
            <PhChatCircleDots :size="20" color="#4B5563" weight="bold" />
            <span>No replies have been posted for this thread.</span>
        </div>

        <confirm-dialog
            :is-open="showDeleteReplyDialog"
            title="Delete Reply"
            message="Are you sure you want to delete this reply? This action cannot be undone."
            confirm-text="Delete"
            @confirm="confirmReplyDelete"
            @cancel="showDeleteReplyDialog = false"
        />

        <reply-form
            :thread-id="threadId"
            :user-id="userId"
            :submit-url="submitUrl"
            @reply-posted="addReply"
            v-if="isAuthenticated"
            class="mt-4"
        ></reply-form>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg" role="alert" v-else>
            <p>Please <a href="/login" class="font-bold underline hover:text-yellow-900">log in</a> to post a reply.</p>
        </div>
    </div>
</template>

<script>
import ReplyForm from './ReplyForm.vue';
import {PhHeart, PhPencil, PhTrash, PhChatCircleDots} from "@phosphor-icons/vue";
import axios from 'axios';
import {toast} from 'vue3-toastify';
import moment from "moment";

export default {
    components: {
        PhPencil,
        PhTrash,
        PhHeart,
        PhChatCircleDots,
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
        submitEditUrl: {
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
            replyCount: this.initialReplyCount,
            isDeleting: null,
            isEditing: null,
            showDeleteReplyDialog: false,
            replyToDelete: null,
        };
    },
    methods: {
        getEditUrl(reply) {
            return this.submitEditUrl + '/' + reply.id;
        },
        showConfirm(reply) {
            this.replyToDelete = reply;
            this.showDeleteReplyDialog = true;
        },
        async confirmReplyDelete() {
            const reply = this.replyToDelete;
            this.showDeleteReplyDialog = false;
            this.isDeleting = reply.id;

            try {
                const response = await axios.delete(`/replies/${reply.id}`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                toast.success(response.data.message);
                this.replies = this.replies.filter(item => {
                    return item.id !== reply.id;
                })
                this.replyCount--;
                window.emitter.emit('reply-count-updated', this.replyCount);

            } catch (error) {
                console.error('Error deleting reply:', error);
                toast.error(error.response?.data?.message || 'Something went wrong.');
            } finally {
                this.isDeleting = null;
                this.replyToDelete = null;
            }
        },
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
        cancelEdit() {
            this.isEditing = null;
        },
        addReply(newReply) {
            this.replies.push(newReply);
            this.replyCount++;
            window.emitter.emit('reply-count-updated', this.replyCount);
        },
        editReply(updatedReply) {
            this.replies = this.replies.map(reply => {
                if (reply.id === updatedReply.id) {
                    return {
                        ...reply,
                        body: updatedReply.body,
                        updated_at: updatedReply.updated_at
                    };
                }
                return reply;
            });
            this.isEditing = null;
        },
        formatDate(dateString) {
            if (!dateString) return 'Unknown date';
            return moment(dateString).fromNow();
        }
    }
};
</script>
