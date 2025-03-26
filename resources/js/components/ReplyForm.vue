<template>
    <form @submit.prevent="submitReply" class="space-y-6 bg-white shadow-lg rounded-xl p-6 border border-gray-100">
        <div>
            <label for="body" class="block text-sm font-medium text-gray-800">Your Reply</label>
            <textarea
                id="body"
                v-model="replyBody"
                rows="4"
                class="mt-2 block w-full rounded-lg border border-gray-200 bg-gray-50 p-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out"
                placeholder="Type your reply here..."
            ></textarea>
        </div>
        <div>
            <button
                type="submit"
                class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300 ease-in-out"
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
