import Vue from 'vue'

const module = {
    namespaced: true,
    state: {
        overall_daily: {},
        overall_total: {},
        CDM_daily: {},
        CDM_total: {},
        CDP_daily: {},
        CDP_total: {},
        CDP_I_daily: {},
        CDP_I_total: {},
        cdm_users_count: 0,
        loaded: false,
        metadata: {}
    },
    mutations: {
        SET_DATE_FROM: function(state, payload) { state.date_from = payload },
        SET_DATE_TO: function(state, payload) { state.date_to = payload },
        SET_OVERALL_DAILY: function(state, payload) { state.overall_daily = payload },
        SET_OVERALL_TOTAL: function(state, payload) { state.overall_total = payload },
        SET_CDM_DAILY: function(state, payload) { state.CDM_daily = payload },
        SET_CDM_TOTAL: function(state, payload) { state.CDM_total = payload },
        SET_CDP_DAILY: function(state, payload) { state.CDP_daily = payload },
        SET_CDP_TOTAL: function(state, payload) { state.CDP_total = payload },
        SET_CDP_I_DAILY: function(state, payload) { state.CDP_I_daily = payload },
        SET_CDP_I_TOTAL: function(state, payload) { state.CDP_I_total = payload },
        SET_CDM_USERS: function(state, payload) { state.cdm_users_count = payload },
        SET_LOADED: function(state, payload) { state.loaded = Boolean(payload) },
        SET_METADATA: function(state, payload) { state.metadata = payload },
    },
    actions: {
        setStats(context, payload={}) {
            const stats = {...payload}
            context.commit('SET_OVERALL_DAILY', {...stats.overall.daily})
            context.commit('SET_OVERALL_TOTAL', {...stats.overall.total})
            context.commit('SET_CDM_DAILY', {...stats.CDM.daily})
            context.commit('SET_CDM_TOTAL', {...stats.CDM.total})
            context.commit('SET_CDP_DAILY', {...stats.CDP.daily})
            context.commit('SET_CDP_TOTAL', {...stats.CDP.total})
            context.commit('SET_CDP_I_DAILY', {...stats.CDP_I.daily})
            context.commit('SET_CDP_I_TOTAL', {...stats.CDP_I.total})
            context.commit('SET_CDM_USERS', parseInt(stats.cdm_users_count))
            context.commit('SET_LOADED', true)
        },
        setMetadata(context, payload={}) {context.commit('SET_METADATA', payload) },
        async getStats(context, {from, to}) {
            context.commit('SET_DATE_FROM', from)
            context.commit('SET_DATE_TO', to)
            const response = await Vue.$API.dispatch('stats/getStats', from, to)
            const {data} = response
            const {data:stats, metadata} = data || {}
            context.dispatch('setStats', stats)
            context.dispatch('setMetadata', metadata)
        }
    }
}

export default module;