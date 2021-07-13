require('./bootstrap');
import Vue from 'vue'

/*import App from "./App.vue";
import router from "./router";*/

Vue.component('app', require('./pages/App.vue').default);

console.log('[i] Starting client app')

Vue.config.productionTip = false

const app = new Vue({
    el: '#laravel-installer',
});
