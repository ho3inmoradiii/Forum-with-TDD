<template>
    <div class="space-y-4">
        <div v-if="activities.length > 0">
            <div v-for="activity in activities" :key="activity.id">
                <article
                    class="bg-gray-50 border-l-4 border-gray-300 p-4 rounded-lg mb-4 shadow-sm hover:shadow-md transition-shadow duration-300"
                >
                    <div class="flex flex-col gap-2">
                        <!-- Header with icon and activity -->
                        <div class="flex flex-row justify-between items-start gap-3">
                            <div class="flex items-center gap-2">
                                <!-- Activity Icon -->
                                <i
                                    :class="{
                                        'fas fa-plus-circle text-gray-600': activity.activity_type === 'thread_created',
                                        'fas fa-comment-dots text-gray-600': activity.activity_type === 'reply_added',
                                        'fas fa-heart text-gray-600': activity.activity_type === 'reply_favorited',
                                        'fas fa-question-circle text-gray-600': !['thread_created', 'reply_added', 'reply_favorited'].includes(activity.activity_type)
                                    }"
                                ></i>
                                <!-- Activity Text -->
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <template v-if="activity.activity_type === 'thread_created' && activity.target">
                                        <span>{{ userWithActivities.name }} created</span>
                                        <a
                                            :href="threadsShow(activity.target)"
                                            class="text-blue-600 hover:text-blue-700 transition duration-300 ease-in-out ml-1 underline"
                                        >
                                            {{ activity.target.title }}
                                        </a>
                                    </template>
                                    <template v-else-if="activity.activity_type === 'reply_added' && activity.target">
                                        <span>{{ userWithActivities.name }} replied to</span>
                                        <a
                                            :href="threadsShow(activity.target.thread)"
                                            class="text-blue-600 hover:text-blue-700 transition duration-300 ease-in-out ml-1 underline"
                                        >
                                            {{ activity.target.thread.title }}
                                        </a>
                                    </template>
                                    <template v-else-if="activity.activity_type === 'reply_favorited' && activity.target">
                                        <span>{{ userWithActivities.name }} favorited</span>
                                        <a
                                            :href="threadsShow(activity.target.thread)"
                                            class="text-blue-600 hover:text-blue-700 transition duration-300 ease-in-out ml-1 underline"
                                        >
                                            {{ activity.target.body }}
                                        </a>
                                    </template>
                                    <template v-else>
                                        <span class="text-gray-600 italic">Unknown activity</span>
                                    </template>
                                </h3>
                            </div>
                            <!-- Timestamp -->
                            <span class="text-xs text-gray-500 font-medium">{{ formatDate(activity.created_at) }}</span>
                        </div>
                        <!-- Activity Body -->
                        <p
                            v-if="activity.target"
                            class="text-gray-600 leading-relaxed line-clamp-2 text-sm mt-1 pl-6"
                        >
                            {{ activity.target.body }}
                        </p>
                        <!-- Delete Buttons -->
                        <div class="flex gap-2 mt-2 pl-6">
                            <button
                                v-if="userWithActivities.id === userId && activity.activity_type === 'thread_created'"
                                @click="showConfirm(activity.target, activity.activity_type)"
                                :disabled="isDeleting === activity.target.id"
                                class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 hover:shadow-md disabled:bg-gray-400 disabled:hover:cursor-not-allowed disabled:opacity-60"
                            >
                                <PhTrash :size="16" color="white" weight="bold" />
                                <span>{{ isDeleting === activity.target.id ? 'Deleting...' : 'Delete' }}</span>
                            </button>
                            <button
                                v-if="userWithActivities.id === userId && activity.activity_type === 'reply_added'"
                                @click="showConfirm(activity.target, activity.activity_type)"
                                :disabled="isDeleting === activity.target.id"
                                class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 hover:shadow-md disabled:bg-gray-400 disabled:hover:cursor-not-allowed disabled:opacity-60"
                            >
                                <PhTrash :size="16" color="white" weight="bold" />
                                <span>{{ isDeleting === activity.target.id ? 'Deleting...' : 'Delete' }}</span>
                            </button>
                        </div>
                    </div>
                </article>
                <!-- Confirm Dialogs -->
                <confirm-dialog
                    :is-open="showDeleteThreadDialog"
                    title="Delete Thread"
                    message="Are you sure you want to delete this thread? This action cannot be undone."
                    confirm-text="Delete"
                    @confirm="confirmThreadDelete"
                    @cancel="showDeleteThreadDialog = false"
                />
                <confirm-dialog
                    :is-open="showDeleteReplyDialog"
                    title="Delete Reply"
                    message="Are you sure you want to delete this reply? This action cannot be undone."
                    confirm-text="Delete"
                    @confirm="confirmReplyDelete"
                    @cancel="showDeleteReplyDialog = false"
                />
            </div>
        </div>
        <div v-else class="text-center text-gray-500 text-base font-medium py-6 bg-gray-100 rounded-lg shadow-sm">
            No activities yet.
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';
import ConfirmDialog from './ConfirmDialog.vue';
import { PhTrash } from "@phosphor-icons/vue";
import moment from "moment";

export default {
    name: "ProfileThreads",
    components: {
        PhTrash,
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
            showDeleteThreadDialog: false,
            showDeleteReplyDialog: false,
            threadToDelete: null,
            replyToDelete: null,
        };
    },
    methods: {
        threadsShow(thread) {
            return `/threads/${thread.channel.slug}/${thread.id}`;
        },
        showConfirm(item, activityType) {
            if (activityType === 'thread_created') {
                this.threadToDelete = item;
                this.showDeleteThreadDialog = true;
            } else {
                this.replyToDelete = item;
                this.showDeleteReplyDialog = true;
            }
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
                this.activities = this.activities.filter(item => {
                    if (item.target_type === 'App\\Models\\Thread') {
                        return item.target.id !== reply.id;
                    } else if (item.target_type === 'App\\Models\\Reply') {
                        return item.target.id !== reply.id;
                    }
                    return true;
                });
            } catch (error) {
                console.error('Error deleting reply:', error);
                toast.error(error.response?.data?.message || 'Something went wrong.');
            } finally {
                this.isDeleting = null;
                this.replyToDelete = null;
            }
        },
        async confirmThreadDelete() {
            const thread = this.threadToDelete;
            this.showDeleteThreadDialog = false;
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
            return moment(dateString).fromNow();
        }
    }
};
</script>

<style scoped>
/* Optional hover effect for article */
article:hover {
    transform: translateY(-2px);
}

/* Smooth transitions for buttons */
button {
    transition: all 0.3s ease-in-out;
}
</style>
