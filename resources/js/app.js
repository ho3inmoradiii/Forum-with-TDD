import './bootstrap';
import { createApp } from 'vue';
import mitt from 'mitt';
import ExampleComponent from './components/ExampleComponent.vue';
import ReplyForm from './components/ReplyForm.vue';
import ThreadReplies from './components/ThreadReplies.vue';
import CreateThread from './components/CreateThread.vue';
import ProfileActivities from './components/ProfileActivities.vue';
import ConfirmDialog from './components/ConfirmDialog.vue';
import Vue3Toastify from 'vue3-toastify';
import SubscriptionButton from "./components/SubscriptionButton.vue";
import 'vue3-toastify/dist/index.css';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Create event bus
const emitter = mitt();
window.emitter = emitter;

const app = createApp({
    data() {
        return {
            replyCount: 0 // Will be set by thread-replies
        };
    },
    created() {
        // Listen for reply count updates
        window.emitter.on('reply-count-updated', (count) => {
            this.replyCount = count;
        });
    }
});

app.use(Vue3Toastify, {
    autoClose: 3000,
    pauseOnHover: false,
});

app.component('example-component', ExampleComponent);
app.component('reply-form', ReplyForm);
app.component('thread-replies', ThreadReplies);
app.component('create-thread', CreateThread);
app.component('profile-activities', ProfileActivities);
app.component('confirm-dialog', ConfirmDialog);
app.component('subscription-button', SubscriptionButton);
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

app.mount('#app');

Alpine.start();
