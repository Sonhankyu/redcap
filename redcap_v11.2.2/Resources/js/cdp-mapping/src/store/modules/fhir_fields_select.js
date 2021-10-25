import Vue from 'vue'
import {compareWithRules} from '@/libs/Utility'

const initialState = {
    selected: [], // fields always selected
    filter: '', // filter for fields
    fields: [],
}

const module = {
    namespaced: true,
    state: {
        ...initialState,
    },
    mutations: {
        SET_SELECTED: (state, payload) => state.selected = payload,
        SET_FILTER: (state, payload) => state.filter = payload,
        SET_FIELDS: (state, payload) => state.fields = payload,
    },
    actions: {
        setSelected(context, params) {
            const fields = [...params, ...context.state.mandatory_fields]
            context.commit('SET_SELECTED', fields)
        },
        setFilter(context, params) { context.commit('SET_FILTER', params) },
        /**
         * apply the filter (a regular expression)
         * and mark the fields as hidden
         * 
         * @param {object} context 
         */
        applyFilter(context) {
            const filter = context.state.filter
            let filter_regexp = new RegExp(`(${filter})`,'ig')
            
            const fields = [...context.state.fields]

            fields.forEach((field, index) => {
                // list of properties to check
                let properties_to_check = [
                    field.field,
                    field.category,
                    field.subcategory,
                    field.label,
                    field.description,
                ]
                let matches = properties_to_check.some(property => property.match(filter_regexp))

                if(!matches) {
                    /* field.marked = {
                        field: field.field,
                        label: field.label,
                        description: field.description,
                    } */
                    field.hidden = true
                }else {
                    /* if(filter.trim()=='') {
                        field.marked = {
                            field: field.field,
                            label: field.label,
                            description: field.description,
                        }
                    }else {
                        field.marked = {
                            field: field.field.replace(filter_regexp, `<mark>$1</mark>`),
                            label: field.label.replace(filter_regexp, `<mark>$1</mark>`),
                            description: field.description.replace(filter_regexp, `<mark>$1</mark>`),
                        }
                    } */
                    field.hidden = false
                }

            })
            context.dispatch('setFields', fields)
        },
        setFields(context, params) { context.commit('SET_FIELDS', params) },
        orderFields(context, rules) {
            const fields = [...context.state.fields]
            const sorted = fields.sort(compareWithRules(rules))
            context.dispatch('setFields', sorted)
        },
    },
    getters: {
        /**
         * create a nested tree using category and subcategory
         * of each FHIR fields
         */
        groups: state => {
            const fields = [...state.fields]
            let groupedFields = {}
            for(let field of fields) {
                let category = field.category || ''
                let subcategory = field.subcategory || ''
                let field_name = field.field || ''

                if(typeof groupedFields[category]==='undefined') {
                    groupedFields[category] = {}
                }
                if(subcategory.trim()!='') {
                    if(typeof groupedFields[category][subcategory]==='undefined') groupedFields[category][subcategory] = {}
                    groupedFields[category][subcategory][field_name] = field
                }else groupedFields[category][field_name] = field
            }
            return groupedFields
        },
        /**
         * getter example
         */
        example: state => {
            return state
        },
    },
}

export default module;