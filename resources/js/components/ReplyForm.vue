<template>
    <form @submit.prevent="submitReply" class="space-y-4">
        <div>
            <label for="body" class="block text-sm font-medium text-gray-700">Your Reply</label>
            <textarea
                id="body"
                v-model="replyBody"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Type your reply here..."
            ></textarea>
        </div>
        <div>
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Post Reply
            </button>
        </div>
    </form>
</template>

<script>
import axios from 'axios';

export default {
    props: {
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
        }
    },
    data() {
        return {
            replyBody: ''
        };
    },
    methods: {
        async submitReply() {
            try {
                const response = await axios.post(this.submitUrl, {
                    body: this.replyBody,
                    thread_id: this.threadId,
                    user_id: this.userId
                });

                // Clear the form
                this.replyBody = '';

                // Emit an event with the new reply data
                this.$emit('reply-posted', response.data);

            } catch (error) {
                console.error('Error posting reply:', error);
                // Handle error (e.g., show error message to user)
            }
        }
    }
};
</script>
