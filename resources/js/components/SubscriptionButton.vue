<template>
    <button
        @click="toggleSubscribe"
        class="mt-4 w-full flex items-center justify-center font-medium py-2 px-4 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
        :class="{
            'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500': !subscribedStatus,
            'bg-green-500 text-white hover:bg-green-600 focus:ring-green-400': subscribedStatus
        }"
        :disabled="isLoading"
        :aria-label="subscribedStatus ? 'Unsubscribe from thread' : 'Subscribe to thread'"
    >
        <ph-spinner v-if="isLoading" class="w-5 h-5 mr-2 animate-spin" />
        <ph-bell v-else-if="!subscribedStatus" class="w-5 h-5 mr-2" />
        <ph-check-circle v-else class="w-5 h-5 mr-2" />
        {{ isLoading ? 'Processing...' : subscribedStatus ? 'Subscribed' : 'Subscribe' }}
    </button>
</template>

<script>
import axios from "axios";
import { toast } from "vue3-toastify";
import { PhBell, PhCheckCircle, PhSpinner } from "@phosphor-icons/vue";

export default {
    name: "SubscriptionButton",
    components: {
        PhBell,
        PhCheckCircle,
        PhSpinner
    },
    props: {
        isSubscribed: {
            type: Boolean,
            required: true
        },
        threadId: {
            type: Number,
            required: true
        },
        isAuthenticated: {
            type: Boolean,
            required: true
        },
    },
    data() {
        return {
            subscribedStatus: this.isSubscribed,
            isLoading: false
        };
    },
    created() {
        console.log('isSubscribed (raw prop):', this.isSubscribed);
        console.log('subscribedStatus (data):', this.subscribedStatus);
    },
    methods: {
        toggleSubscribe() {
            if (this.isAuthenticated) {
                if (!this.subscribedStatus) {
                    this.subscribeThread();
                } else {
                    this.unsubscribeThread();
                }
            } else {
                toast.error('To subscribe to a thread, you must log in.');
                setTimeout(() => { window.location.href = '/login' }, 2000);
            }
        },
        async subscribeThread() {
            try {
                this.isLoading = true;
                await axios.post(`/threads/${this.threadId}/subscribe`, {});
                this.subscribedStatus = true;
                toast.success('Thread Subscribed.');
            } catch (error) {
                console.error('Error subscribing to thread:', error);
                toast.error('Something went wrong. Please try again.');
            } finally {
                this.isLoading = false;
            }
        },
        async unsubscribeThread() {
            try {
                this.isLoading = true;
                await axios.delete(`/threads/${this.threadId}/subscribe`);
                this.subscribedStatus = false;
                toast.success('Thread Subscription deleted successfully.');
            } catch (error) {
                console.error('Error unsubscribing from thread:', error);
                toast.error('Something went wrong. Please try again.');
            } finally {
                this.isLoading = false;
            }
        }
    }
};
</script>
