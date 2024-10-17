import './bootstrap';
import { createApp } from 'vue';
import ExampleComponent from './components/ExampleComponent.vue';
import ReplyForm from './components/ReplyForm.vue';
import ThreadReplies from "./components/ThreadReplies.vue";
import CreateThread from "./components/CreateThread.vue";

import Alpine from 'alpinejs';

window.Alpine = Alpine;
const app = createApp({});
app.component('example-component', ExampleComponent);
app.component('reply-form', ReplyForm);
app.component('thread-replies', ThreadReplies);
app.component('create-thread', CreateThread);
app.mount('#app');

Alpine.start();
