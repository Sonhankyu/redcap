const mandatoryFields = ['id']

const initialState = {
    settings: {},
    mandatoryFields,
}

const module = {
    namespaced: true,
    state: {...initialState},
    mutations: {
        SET_SETTINGS: function(state, payload) {
            state.settings = {...payload}
        },
    },
    actions: {
        set(context, settings) {
            context.commit('SET_SETTINGS',settings)
        },
    }
}

export {module as default, mandatoryFields}