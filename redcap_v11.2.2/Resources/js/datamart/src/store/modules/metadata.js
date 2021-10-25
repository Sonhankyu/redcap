import { FhirField, MetadataContainer } from '@/libraries/metadata/'
import store from '@/store'


/* const createWebSocketPlugin = (socket) => {
    return store => {
      socket.on('data', data => {
        store.commit('receiveData', data)
      })
      store.subscribe(mutation => {
        if (mutation.type === 'UPDATE_DATA') {
          socket.emit('update', mutation.payload)
        }
      })
    } */

/* const makeProxy = (field, context) => {
    const handler = {
        get(target, property, receiver) {
            return Reflect.get(...arguments)
        },
        set(obj, prop, value) {
            return Reflect.set(...arguments)
        }
    }
    
    const proxy = new Proxy(field, handler)
    return proxy
} */

/**
 * create a list of grouped objects
 * that will be confused whenever
 * FHIR metadata fhields must be used.
 * The filter 
 * 
 * @param {FhirFields[]} items 
 * @param {callable} filter function that accepts a FhirField
 * @returns 
 */
const groupFields = (items, filter=null) => {
    let container = new MetadataContainer('')
    mainLoop: for(let fhirField of items) {
        let {category, subcategory, field} = fhirField
        /* let categories = ['Demographics', 'Vital Signs']
        if(categories.indexOf(category)<0) continue */
        if(typeof filter === 'function') {
            // apply a filter and skip if returns false
            let shouldContinue = filter(fhirField)
            if(!shouldContinue) continue mainLoop
        }
        let categoryContainer = container.hasChild(category)
        if(!categoryContainer) {
            categoryContainer = new MetadataContainer(category)
            container.addChild(categoryContainer)
        }
        if(categoryContainer && subcategory!='') {
            let subCategoryContainer = categoryContainer.hasChild(subcategory)
            if(!subCategoryContainer) {
                subCategoryContainer = new MetadataContainer(subcategory)
                categoryContainer.addChild(subCategoryContainer)
            }
            subCategoryContainer.addChild(fhirField)
        }else {
            categoryContainer.addChild(fhirField)
        }
    }
    return container
}

const initialState = {
    list: [],
    fieldNames: [],
    hiddenList: [],
    query: '',
    groups: null,
}

const module = {
    namespaced: true,
    state: {...initialState},
    mutations: {
        SET_LIST: (state, payload) => state.list = payload,
        SET_FIELDNAMES: (state, payload) => state.fieldNames = payload,
        SET_HIDDEN_LIST: (state, payload) => state.hiddenList = payload,
        SET_QUERY: (state, payload) => state.query = payload,
        SET_GROUPS: (state, payload) => state.groups = payload,
    },
    actions: {
        setList(context, list) {
            const items = []
            const fieldNames = []
            for(let data of Object.values(list)) {
                let fhirField = new FhirField(data, context)
                fieldNames.push(fhirField.field)
                items.push(fhirField)
            }
            context.commit('SET_LIST', items) // list of objects
            context.commit('SET_FIELDNAMES', fieldNames) // used for fast reference in revision module
        },
        setQuery(context, query) {
            context.commit('SET_QUERY', query)
        },
        setHiddenList(context, list) { context.commit('SET_HIDDEN_LIST', list) },
    },
    getters: {
        /**
         * group the FHIR metadata fields and filter them if a filtering function is provided
         * @param {object} state 
         * @returns 
         */
        groups: state => (filter=null) => groupFields(state.list, filter),
        /**
         * Applies a query to fields
         * and accumulates fields that need to be hidden.
         * NOTE: This function return aways true so that, when is used
         * in the 'groupFields' function, the cycle is not stopped.
         */
        filteredGroups: state => {
            const fieldsFilter = (text, accumulator=[]) => (item) => {
                const {category, subcategory, field, label} = item
                if(text.trim()=='') {
                    item.hidden = false
                    return true
                }
                // serach inside all field properties specified below
                const filterableProperties = [category, subcategory, field, label]
                const anyMatch = filterableProperties.some(propertyText => {
                    let regExp = new RegExp(`${text}`, 'ig')
                    let match = propertyText.match(regExp)
                    return match
                })
                // if(!anyMatch) accumulator.push(item.field) // store the hidden fields
                item.hidden = !anyMatch // store the hidden fields
                return true
            }
            let hiddenFieldsAccumulator = []
            const groups = groupFields(state.list, fieldsFilter(state.query, hiddenFieldsAccumulator))
            store.dispatch('metadata/setHiddenList', hiddenFieldsAccumulator)
            return groups
        },
    }
}

export {module as default, groupFields};