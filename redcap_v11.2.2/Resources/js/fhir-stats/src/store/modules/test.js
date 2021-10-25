const module = {
    namespaced: true,
    state: {
        info: null,
    },
    mutations: {
        SET_INFO: function(state, payload) { state.info = payload },
    },
    actions: {
        setInfo(context, text) { context.commit('SET_INFO',text) },
    }
}

export default module;