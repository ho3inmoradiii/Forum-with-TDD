<template>
    <div class="space-y-6 bg-white shadow-lg rounded-xl p-6 border border-gray-100 w-full">
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
        <div class="flex flex-row gap-3">
            <button
                @click="submitReply"
                :disabled="!enableSubmitReplyButton"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 hover:shadow-md disabled:bg-gray-400 disabled:hover:cursor-not-allowed disabled:opacity-60"
            >
                <PhPaperPlaneTilt v-if="!reply" :size="16" color="white" weight="bold" />
                <PhCheckCircle v-else :size="16" color="white" weight="bold" />
                <span>{{ reply ? 'Update' : 'Post' }}</span>
            </button>
            <button
                v-if="reply"
                @click="cancelUpdate"
                class="flex items-center gap-2 px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg shadow hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 hover:shadow-md"
            >
                <PhXCircle :size="16" color="white" weight="bold" />
                <span>Cancel</span>
            </button>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import {toast} from "vue3-toastify";
import {PhCheckCircle, PhPaperPlaneTilt, PhXCircle} from "@phosphor-icons/vue";

export default {
    components: {PhXCircle, PhCheckCircle, PhPaperPlaneTilt},
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
        },
        reply: {
            type: Object,
            default: null,
        }
    },
    computed: {
        enableSubmitReplyButton(){
            return this.replyBody;
        }
    },
    data() {
        return {
            replyBody: this.reply && this.reply.body ? this.reply.body : ''
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
                toast.success(this.reply ? 'Reply updated successfully.' : 'Reply added successfully.');

                // Clear the form
                this.replyBody = '';

                // Emit an event with the new reply data
                this.reply ? this.$emit('reply-edited', response.data) : this.$emit('reply-posted', response.data);
            } catch (error) {
                console.error('Error posting reply:', error);
                // Handle error (e.g., show error message to user)
            }
        },
        cancelUpdate() {
            this.$emit('cancel-edit')
        }
    }
};
</script>
