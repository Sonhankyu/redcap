const initialState = {
    cdp_mapping: [],
    datamart_revision: false,
    info: {}
}

/**
 * extract mapping names from the state
 * 
 * @param {array} cdp_mapping 
 * @returns {array}
 */
const getCdpMappingSourceNames = (cdp_mapping=[]) => {
    const names = []
    cdp_mapping.forEach(mapping => {
        let {external_source_field_name} = mapping
        if(!external_source_field_name) return
        if(names.indexOf(external_source_field_name)<0) {
            names.push(external_source_field_name)
        }
    });
    return names
}

const module = {
    namespaced: true,
    state: {...initialState},
    mutations: {
        SET_CDP_MAPPING: (state, payload) =>  state.cdp_mapping = {...payload},
        SET_DATAMART_REVISION: (state, payload) =>  state.datamart_revision = {...payload},
        SET_INFO: (state, payload) => state.info = {...payload},
        SET: (state, payload) => {
            for(let [key, value] of Object.entries(payload)) state[key] = value
        },
    },
    actions: {
        setCdpMapping(context, mapping) { context.commit('SET_CDP_MAPPING', mapping) },
        setDatamartRevision(context, revision) { context.commit('SET_DATAMART_REVISION', revision) },
        setInfo(context, info) { context.commit('SET_INFO', info) },
        set(context, {cdp_mapping, datamart_revision, info}) {
            context.commit('SET', {cdp_mapping, datamart_revision, info})
        },
    },
    getters: {
        mappingSourceNames: state => {
            try {
                const {cdp_mapping, datamart_revision} = state
                if(datamart_revision) {
                    const {data:{fields=[]}} = datamart_revision
                    return fields
                }
                else {
                    return getCdpMappingSourceNames(cdp_mapping)
                }
            } catch (error) {
                console.log('error extracting the mapped fields', error)
                return []
            }
        }
    }
}

export default module;