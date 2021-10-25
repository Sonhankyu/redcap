/**
 * in this module are save the app settings
 * as received from the server and not touched
 */
// import MappingRow from "../../models/MappingRow"
import {compareWithRules} from '@/libs/Utility'

/**
 * group fields in a project by form name
 * @param {object} fields 
 */
const groupFieldsByFormName = (fields) => {
    const group = {}
    Object.values(fields).forEach(field => {
        const form_name = field.form_name
        if(!Array.isArray(group[form_name])) group[form_name] = []
        group[form_name].push(field)
    })
    return group
}

const initialState = {
    translations: {}, // translations
    project: {}, // project data
    project_fields: {}, // list of fields available in the project
    project_temporal_fields: {}, // list of temporal fields available in the project
    fhir_fields: {}, // FHIR source fields as stored in the CSV file
    mapping: [], // mapping as stored in the database
    fhir_source_name: '', // name of the FHIR provider
    day_offset_min: 0,
    day_offset_max: 0,
    mapping_helper_url: '',
}

const module = {
    namespaced: true,
    state: {...initialState},
    mutations: {
        SET_STATE: (state, payload) => {
            for(let [key, value] of Object.entries(payload)) state[key] = value
        },
    },
    actions: {
        setState(context, params) { context.commit('SET_STATE', params) },
    },
    getters: {
        /**
         * get the fields that can be used in the preview.
         * must not be identifiers or temporal fields
         */
        previewFields: state => {
            const fhir_fields = Object.values(state.fhir_fields)
            let list = [...fhir_fields].filter( field => { return !(field.identifier || field.temporal) } )
            list = list.sort(compareWithRules(['category', 'subcategory', 'field']))
            const reducer = (accumulator, item) => {
                const category = item.category
                if(!Array.isArray(accumulator[category])) accumulator[category] = []
                accumulator[category].push(item)
                return accumulator
            }
            const groups = list.reduce(reducer, {})
            return groups
        },
        groupedProjectTemporalFields: state => {
            const {project_temporal_fields:fields={}} = state
            return groupFieldsByFormName({...fields})

        },
        groupedProjectFields: state => {
            const {project_fields:fields={}} = state
            return groupFieldsByFormName({...fields})
        },
        /**
         * get a list of forms in a specific event
         * @param {object} state 
         */
        getForms: state => event_id => {
            if(!event_id) return {}
            const {project_fields:fields={}} = state
            const grouped_fields = groupFieldsByFormName({...fields})
            const {events_forms} = state.project
            const form_keys = events_forms[event_id] || []
            const filtered_forms = {}
            form_keys.forEach(form_key => {
                if(form_key in grouped_fields) filtered_forms[form_key] = grouped_fields[form_key]
            })
            return filtered_forms
        },
        getEventData: state => event_id => {
            if(!event_id) return {}
            const {events} = {...state.project}
            return events[event_id]
        },
        getEventName: (_, getters) => event_id => {
            const event_data = getters['getEventData'](event_id)
            if(typeof event_data==='object' && 'name' in event_data) return event_data.name
            return ''
        },
        getFieldData: state => field_name => {
            const {metadata={}} = state.project
            return metadata[field_name]
        },
        getPreselectOptions: state => {
            const translations = state.translations
            const none = translations['preselect_none'] || 'none'
            const options = [
                { value:null, label: `-- ${none} --`},
                { value:'MIN', label: translations['preselect_lowest'] || "Lowest numerical value"},
                { value:'MAX', label: translations['preselect_highest'] || "Highest numerical value"},
                { value:'FIRST', label: translations['preselect_earliest'] || "Earliest value (based on timestamp)"},
                { value:'LAST', label: translations['preselect_latest'] || "Latest value (based on timestamp)"},
                { value:'NEAR', label: translations['preselect_nearest'] || "Nearest value (based on timestamp)"},
            ]
            return options
        },
        /**
         * get data of a specific form
         * @param {object} state 
         */
        getFormData: state => form_name => {
            const {forms={}} = state.project
            if(form_name in forms) return forms[form_name]
            return
        },
        /**
         * get FHIR data associated to a specific field
         * @param {object} state 
         */
        getFhirField: state => field_name => {
            return state.fhir_fields[field_name]
        }
    },
}

export default module;