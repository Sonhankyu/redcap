import Vue from 'vue'

const initialState = {
    project_id: null,
    lang: null,
    standalone_authentication_flow: null,
    standalone_launch_enabled: null,
    standalone_launch_url: null,
    ehr_system_name: null,
    fhir_code: null,
    fhir_version: null,
    fhir_base_url: null,
}

const module = {
    namespaced: true,
    state: {...initialState},
    mutations: {
        SET: function(state, payload) {
            for(let [key, value] of Object.entries(payload)) state[key] = value
        },
    },
    actions: {
        set(context, settings) {
            context.commit('SET', settings)
        },
    }
}

export default module;