import Vue from 'vue'
import Vuex from 'vuex'


import app_settings from '@/store/modules/app_settings'
import user from '@/store/modules/user'
import project from '@/store/modules/project'

Vue.use(Vuex)

var initialState = {}

const store = new Vuex.Store({
    state: Object.assign({}, initialState),
    modules: {
        app_settings,
        user,
        project,
    }
})

export default store