import {Mapping} from '@/models'
import {compareWithRules} from '@/libs/Utility'
/**
 * rules for sorting the table
 */
const sorting_rules = {
    fhir: ['category','subcategory','config.external_source_field_name','config.field_name','config.event_id'],
    event: ['config.event_id','config.field_name','category','subcategory','config.external_source_field_name'],
    field: ['config.field_name','category','subcategory','config.external_source_field_name'],
}

const initialState = {
    list: [], // list of mappings
    auto_id: 1, // auto incremental primary key for the table rows
}

const module = {
    namespaced: true,
    state: {
        ...initialState,
    },
    mutations: {
        SET_LIST: (state, payload) => state.list = payload,
        ADD_ITEM: (state, {item, index=null}) => {
            if(typeof index === 'number') {
                state.list.splice(index, 0, item) //insert
            }else {
                state.list.push(item) //add
            }
        },
        UPDATE_ITEM: (state, {item, params}) => {
            const list = [...state.list]
            let index = list.indexOf(item)
            if(index<0) {
                return
            }
            for(let [key, value] of Object.entries(params)) {
                item[key] = value
            }
            state.list.splice(index, 1, item)
        },
        DELETE_ITEM: (state, item) => {
            const list = [...state.list]
            let index = list.indexOf(item)
            if(index<0) {
                return
            }
            state.list.splice(index, 1)
        },
    },
    actions: {
        /**
         * helper to get auto incremented ID
         * @param {object} context 
         */
        getAutoId(context) {
            const id = context.state.auto_id++
            return id
        },
        /**
         * create mapping rows for the table
         * using mapping and fhir_fields from the backend
         * 
         * @param {object} context 
         * @param {object} param1 
         */
        async makeList(context, mapping) {
            const list = []

            for (let config of mapping) {
                let item = await context.dispatch('mappingFactory', config)
                list.push(item)
            }

            context.commit('SET_LIST', list)
        },
        setList(context, list) { context.commit('SET_LIST', list) },
        /**
         * restore a deleted row
         */
        restoreRow(context, item) {
            const params = {status: 'normal'}
            context.commit('UPDATE_ITEM', {item, params})
        },
        /**
         * delete the row if new
         * mark as deleted if existing
         */
        async deleteRow(context, item) {

            if(item.status=='new') {
                context.commit('DELETE_ITEM', item) // remove
            }else {
                const params = {status: 'deleted'}
                context.commit('UPDATE_ITEM', {item, params}) // remove
            }
        },
        /**
         * duplicate the row
         */
        async duplicateRow(context, item) {
            const config = {...item.config}
            config.map_id = null
            const new_item = await context.dispatch('mappingFactory', config)
            new_item.status = 'new'
            const list = [...context.state.list]
            const insert_position = list.indexOf(item)+1
            context.commit('ADD_ITEM', {item:new_item, index:insert_position}) // remove
        },
        async mappingFactory(context, config={}) {
            const id = await context.dispatch('getAutoId')
            const item = new Mapping(id, config)
            return item
        },
        /**
         * TODO: create a new row
         * @param {Object} cofig 
         */
        async addRow(context, config) {
            const item = await context.dispatch('mappingFactory', config)
            item.status = 'new'
            context.commit('ADD_ITEM', {item}) // remove
            return item
        },
        /**
         * set params for an item in the list
         * @param {object} context 
         * @param {object} {item, params} 
         */
        update(context, {item, params}) { context.commit('UPDATE_ITEM', {item, params}) },
        async sortBy(context, rule_name) {
            let list = [...context.state.list]
            if(rule_name in sorting_rules) {
                let sorting_rule = sorting_rules[rule_name]
                list.sort(compareWithRules(sorting_rule))
                await context.dispatch('setList', list)
                return rule_name
            }
            return
        },
    },
    getters: {
        /**
         * get the config property for an element
         * in the mapping list
         */
        configProperty: state => (index, property) => {
            try {
                const mapping = state.list[index]
                return mapping[property]
            } catch (error) {
                // do nothing on error
                return
                // console.error('error getting a configuration property', {index, property}, error)
            }
        },
        configurationList: state => {
            try {
                const list = [...state.list].map( item => item.config)
                return list
            } catch (error) {
                return []
            }
        },
        previewFields: state => {
            const list = [...state.list].filter( mapping => { return !(mapping.is_identifier || mapping.is_temporal) } )
            return list
        },
        /**
         * getter example
         */
        example: state => {
            return state
        },
        recordIdentifierExists: state => {
            const mapping_list = [...state.list]
            const exists = mapping_list.some(mapping => mapping.is_record_identifier===true)
            if(!exists) return false
            return true
        }
        
    },
}

export default module;