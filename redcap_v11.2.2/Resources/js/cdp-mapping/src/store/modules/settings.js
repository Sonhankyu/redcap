// import MappingRow from "../../models/MappingRow"


const initialState = {
    preview_fields: [], // mapping as stored in the database
    fhir_cdp_auto_adjudication_enabled: false,
    realtime_webservice_offset_days: 7,
    realtime_webservice_offset_plusminus: '+-',
}

const module = {
    namespaced: true,
    state: {...initialState},
    mutations: {
        SET_STATE: (state, payload) => {
            for(let [key, value] of Object.entries(payload)) state[key] = value
        },
        SET_PREVIEW_FIELDS: (state, payload) => state.preview_fields = payload,
        SET_OFFSET_DAYS: (state, payload) => state.realtime_webservice_offset_days = payload,
        SET_OFFSET_PLUS_MINUS: (state, payload) => state.realtime_webservice_offset_plusminus = payload,
        SET_AUTO_ADJUDICATION: (state, payload) => state.fhir_cdp_auto_adjudication_enabled = payload,
    },
    actions: {
        setState(context, params) { context.commit('SET_STATE', params) },
        /**
         * 
         * @param {object} context 
         * @param {object} app_settings the initial settings coming from the server
         */
        init(context, app_settings) {
            // const {app_settings} = context.rootState
            const {
                project: {
                    fhir_cdp_auto_adjudication_enabled,
                    realtime_webservice_offset_days,
                    realtime_webservice_offset_plusminus,
                }={},
                preview_fields=[]
            } = {...app_settings}
            const params = {
                fhir_cdp_auto_adjudication_enabled,
                realtime_webservice_offset_days,
                realtime_webservice_offset_plusminus,
                preview_fields,
            }
            context.commit('SET_STATE', params)

        },
        setPreviewField(context, {index, field}) {
            const preview_fields = [...context.state.preview_fields]
            if(preview_fields.length<=index) return
            preview_fields[index] = field
            context.commit('SET_PREVIEW_FIELDS', preview_fields)
        },
        deletePreviewField(context, index) {
            const preview_fields = [...context.state.preview_fields]
            if(preview_fields.length<=index) return
            preview_fields.splice(index,1)
            context.commit('SET_PREVIEW_FIELDS', preview_fields)
        },
        addPreviewField(context) {
            const preview_fields = [...context.state.preview_fields]
            preview_fields.push('')
            context.commit('SET_PREVIEW_FIELDS', preview_fields)
        },
        setOffsetDays(context, value) {
            context.commit('SET_OFFSET_DAYS', value)
        },
        setOffsetPlusminus(context, value) {
            context.commit('SET_OFFSET_PLUS_MINUS', value)
        },
        setAutoAdjudication(context, value) {
            value = Boolean(value)
            context.commit('SET_AUTO_ADJUDICATION', value)
        },
    },
}

export default module;