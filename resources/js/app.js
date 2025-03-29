import './bootstrap';
import { createApp } from 'vue';
import ExampleComponent from './components/ExampleComponent.vue';
import ReplyForm from './components/ReplyForm.vue';
import ThreadReplies from "./components/ThreadReplies.vue";
import CreateThread from "./components/CreateThread.vue";
import ProfileThreads from "./components/ProfileThreads.vue";
import ConfirmDialog from "./components/ConfirmDialog.vue";
import Vue3Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
const app = createApp({});
app.use(Vue3Toastify, {
    autoClose: 3000,
    pauseOnHover: false,
});
app.component('example-component', ExampleComponent);
app.component('reply-form', ReplyForm);
app.component('thread-replies', ThreadReplies);
app.component('create-thread', CreateThread);
app.component('profile-threads', ProfileThreads);
app.component('confirm-dialog', ConfirmDialog);
app.mount('#app');

Alpine.start();
