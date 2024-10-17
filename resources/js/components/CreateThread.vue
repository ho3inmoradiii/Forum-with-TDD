<template>
    <form @submit.prevent="submitThread" class="space-y-4">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Thread Title</label>
            <input
                id="title"
                v-model="threadTitle"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Enter thread title..."
                required
            >
        </div>
        <div>
            <label for="body" class="block text-sm font-medium text-gray-700">Thread Body</label>
            <textarea
                id="body"
                v-model="threadBody"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                placeholder="Type your thread content here..."
                required
            ></textarea>
        </div>
        <div>
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Create Thread
            </button>
        </div>
    </form>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            threadTitle: '',
            threadBody: ''
        };
    },
    methods: {
        async submitThread() {
            try {
                const response = await axios.post('/threads', {
                    title: this.threadTitle,
                    body: this.threadBody
                });

                // Redirect to the new thread's page
                window.location.href = `/threads/${response.data.id}`;

            } catch (error) {
                console.error('Error creating thread:', error);
                // Handle error (e.g., show error message to user)
            }
        }
    }
};
</script>
