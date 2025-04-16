<template>
    <div v-if="activities.length > 0">
        <div v-for="activity in activities" :key="activity.id">
            <article class="p-4 bg-gray-50 rounded-lg mb-4">
                <div class="flex flex-col gap-3">
                    <div class="flex flex-row justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">
                            <template v-if="activity.activity_type === 'thread_created' && activity.target">
                                <span>{{ userWithActivities.name }} created a new thread</span>
                                <a :href="threadsShow(activity.target)" class="hover:text-blue-600 transition duration-300 ease-in-out ml-2">
                                    {{ activity.target.title }}
                                </a>
                            </template>
                            <template v-else-if="activity.activity_type === 'reply_added' && activity.target">
                                <span>{{ userWithActivities.name }} replied to </span>
                                <a :href="threadsShow(activity.target.thread)" class="hover:text-blue-600 transition duration-300 ease-in-out ml-2">
                                    {{ activity.target.thread.title }}
                                </a>
                            </template>
                            <template v-else>
                                <span>Unknown activity</span>
                            </template>
                        </h3>
                        <span class="text-sm text-gray-600">{{ formatDate(activity.created_at) }}</span>
                    </div>
                    <p v-if="activity.target" class="text-gray-700 leading-relaxed line-clamp-3">
                        {{ activity.target.body }}
                    </p>
                    <button
                        v-if="userWithActivities.id === userId && activity.activity_type === 'thread_created'"
                        @click="showConfirm(activity.target)"
                        :disabled="isDeleting === activity.target.id"
                        class="w-40 justify-center inline-flex items-center px-5 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-300 ease-in-out"
                    >
                        {{ isDeleting === activity.target.id ? 'Deleting...' : 'Delete Thread' }}
                    </button>
                </div>
            </article>
            <confirm-dialog
                :is-open="showDialog"
                title="Delete Thread"
                message="Are you sure you want to delete this thread? This action cannot be undone."
                confirm-text="Delete"
                @confirm="confirmDelete"
                @cancel="showDialog = false"
            />
        </div>
    </div>
    <div v-else class="text-center text-gray-600 text-lg font-medium py-8 bg-gray-100 rounded-lg">
        No activities yet.
    </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';
import ConfirmDialog from './ConfirmDialog.vue';

export default {
    name: "ProfileThreads",
    components: {
        ConfirmDialog
    },
    props: {
        userWithActivities: {
            type: Object,
            required: true
        },
        userId: {
            type: Number,
            required: true
        },
    },
    data() {
        return {
            activities: this.userWithActivities.activities,
            isDeleting: null,
            showDialog: false,
            threadToDelete: null
        };
    },
    methods: {
        threadsShow(thread) {
            return `/threads/${thread.channel.slug}/${thread.id}`;
        },
        showConfirm(thread) {
            this.threadToDelete = thread;
            this.showDialog = true;
        },
        async confirmDelete() {
            const thread = this.threadToDelete;
            this.showDialog = false;
            this.isDeleting = thread.id;

            try {
                const response = await axios.delete(`/threads/${thread.id}`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                toast.success(response.data.message);
                this.activities = this.activities.filter(item => {
                    if (item.target_type === 'App\\Models\\Thread') {
                        return item.target.id !== thread.id;
                    } else if (item.target_type === 'App\\Models\\Reply') {
                        return item.target.thread.id !== thread.id;
                    }
                    return true;
                });
            } catch (error) {
                console.error('Error deleting thread:', error);
                toast.error(error.response?.data?.message || 'Something went wrong.');
            } finally {
                this.isDeleting = null;
                this.threadToDelete = null;
            }
        },
        formatDate(dateString) {
            if (!dateString) return 'Unknown date';
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            const diffInMinutes = Math.floor(diffInSeconds / 60);
            const diffInHours = Math.floor(diffInMinutes / 60);
            const diffInDays = Math.floor(diffInHours / 24);

            if (diffInSeconds < 60) return 'Just now';
            else if (diffInMinutes < 60) return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
            else if (diffInHours < 24) return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
            else if (diffInDays < 7) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
            else return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }
    }
};
</script>
