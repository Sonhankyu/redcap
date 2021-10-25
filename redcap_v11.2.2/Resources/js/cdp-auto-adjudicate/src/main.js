import Vue from 'vue'
import App from './App.vue'

Vue.config.productionTip = false

if(process.env.NODE_ENV!=='production') {
  new Vue({
    render: h => h(App),
  }).$mount('#app')
}

// window.cdp_preview_app = new Vue(App).$mount('#app')


