import './bootstrap';
//
//import Alpine from 'alpinejs';
//
//window.Alpine = Alpine;
//
//Alpine.start();

import Vue from 'vue';
import EventForm from './components/EventForm.vue';

Vue.component('event-form', EventForm);

new Vue({
    el: '#app',
});