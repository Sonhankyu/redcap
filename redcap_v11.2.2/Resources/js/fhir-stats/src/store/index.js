import Vuex from 'vuex';
import Vue from 'vue';
Vue.use(Vuex);

import statsModule from '@/store/modules/stats'


/**
 * state management
 */
var initialState = {}

const store = new Vuex.Store({
    state: {...initialState},
    modules: {
        stats: statsModule,
    },
    mutations: {},
    actions: {},
    getters: {},
})

export default store