/**
 * all libraries are loaded here
 */
import Vue from 'vue'
// style animations
import 'animate.css'

// import alert system
import SwalVue from 'swal-vue'
// import SwalVue from '@/assets/swal-vue/src/index.js'
Vue.use(SwalVue)

// set the global API object
import API_Plugin from '@/plugins/API'
Vue.use(API_Plugin)
 
 // FontAwesome
 import { library } from '@fortawesome/fontawesome-svg-core'
 import { fas } from '@fortawesome/free-solid-svg-icons'
 library.add(fas)
 
 import { FontAwesomeIcon, FontAwesomeLayers, FontAwesomeLayersText } from '@fortawesome/vue-fontawesome'
 Vue.component('font-awesome-layers', FontAwesomeLayers)
 Vue.component('font-awesome-layers-text', FontAwesomeLayersText)
 Vue.component('font-awesome-icon', FontAwesomeIcon)
 
 
 import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
 Vue.use(BootstrapVue)
 // Vue.use(IconsPlugin)
 // import 'bootstrap/dist/css/bootstrap.css'
 import 'bootstrap-vue/dist/bootstrap-vue.css'

 // globally register the async version of b-modal
import AsyncModal from '@/components/AsyncModal'
Vue.component('b-modal-async', AsyncModal)
