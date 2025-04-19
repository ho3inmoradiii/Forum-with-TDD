<template>
    <form @submit.prevent="submitThread" class="space-y-4">
        <div>
            <label for="channel" class="block text-sm font-medium text-gray-700">Select Channel</label>
            <select
                id="channel"
                v-model="channelId"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                :class="{ 'border-red-500': errors.channel_id }"
                required
            >
                <option value="">Select a channel</option>
                <option v-for="channel in channels" :key="channel.id" :value="channel.id">
                    {{ channel.name }}
                </option>
            </select>
            <p v-if="errors.channel_id" class="mt-1 text-sm text-red-600">{{ errors.channel_id[0] }}</p>
        </div>
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Thread Title</label>
            <input
                id="title"
                v-model="threadTitle"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                :class="{ 'border-red-500': errors.title }"
                placeholder="Enter thread title..."
                required
            >
            <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title[0] }}</p>
        </div>
        <div>
            <label for="body" class="block text-sm font-medium text-gray-700">Thread Body</label>
            <textarea
                id="body"
                v-model="threadBody"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                :class="{ 'border-red-500': errors.body }"
                placeholder="Type your thread content here..."
                required
            ></textarea>
            <p v-if="errors.body" class="mt-1 text-sm text-red-600">{{ errors.body[0] }}</p>
        </div>
        <div>
            <button
                type="submit"
                :disabled="!enableSubmitThreadButton"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400 disabled:hover:cursor-not-allowed"
            >
                Create Thread
            </button>
        </div>
        <div v-if="generalError" class="mt-2 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ generalError }}
        </div>
    </form>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';

export default {
    props: {
        channels: {
            type: Array,
            required: true
        }
    },
    computed: {
        enableSubmitThreadButton(){
            return this.threadTitle && this.threadBody && this.channelId;
        }
    },
    data() {
        return {
            threadTitle: '',
            threadBody: '',
            channelId: '',
            errors: {},
            generalError: ''
        };
    },
    methods: {
        async submitThread() {
            this.errors = {};
            this.generalError = '';
            try {
                const response = await axios.post('/threads', {
                    title: this.threadTitle,
                    body: this.threadBody,
                    channel_id: this.channelId
                });
                toast.success(response.data.message);

                const channel = this.channels.find(channel => channel.id === this.channelId);

                // Redirect to the new thread's page
                window.location.href = `/threads/${channel.slug}/${response.data.thread.id}`;
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    // Validation errors
                    this.errors = error.response.data.errors;
                } else {
                    console.error('Error creating thread:', error);
                    this.generalError = 'An error occurred while creating the thread. Please try again.';
                }
            }
        }
    }
};
</script>
