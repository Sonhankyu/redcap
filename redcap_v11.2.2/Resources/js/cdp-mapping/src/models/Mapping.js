import store from '@/store'
import MappingValidator from './MappingValidator'

const defaultFhirData = Object.freeze({
    category: null,
    description: null,
    field: null,
    label: null,
    subcategory: null,
    temporal: null,
    disabled: null,
    disabled_reason: null,
})


export default class Mapping {
    #id
    #original_config
    #config
    #status = 'normal'
    #mappingValidator
    #validation_errors = []

    /**
     * 
     * @param {*} config {
     *                      map_id,
     *                      event_id,
     *                      project_id,
     *                      external_source_field_name,
     *                      field_name,is_record_identifier,
     *                      preselect,
     *                      temporal_field
     *                  }
     */
    constructor(id, config={}) {
        this.#id = id
        this.#original_config = {...config}
        this.initConfigurationValidationProxy(config)
    }

    /**
     * register a proxy to the mapping configuration
     * that runs a validator every time the settings are changed
     * 
     * @param {object} config 
     */
    initConfigurationValidationProxy(config) {
        this.#mappingValidator = new MappingValidator()
        const configHandler = {
            set: (target, prop, value) => {
                target[prop] = value
                this.#mappingValidator.validate(this)
                this.#validation_errors = this.#mappingValidator.errors
                return true
            }
        }
        this.#config = new Proxy({...config}, configHandler)
        this.#validation_errors = this.#mappingValidator.validate(this)
    }

    get map_id() {
        const map_id = this.#config.map_id || null
        return map_id
    }

    get project_id() {
        const project_id = this.#config.project_id || null
        return project_id
    }

    get category() {
        return this.getFhirData('category') || ''
    }

    get subcategory() {
        return this.getFhirData('subcategory') || ''
    }
    
    get fhir_label() {
        return this.getFhirData('label') || ''
    }


    get is_record_identifier() {
        const is_identifier = this.getFhirData('identifier')
        return Boolean(is_identifier)
    }

    get disabled() {
        const disabled = this.getFhirData('disabled')
        return Boolean(disabled)
    }

    get disabled_reason() {
        const disabled_reason = this.getFhirData('disabled_reason')
        return new String(disabled_reason) ?? ''
    }



    get is_temporal() {
        const is_temporal = this.getFhirData('temporal')
        return Boolean(is_temporal)
    }

    get event_id() { return this.#config.event_id }
    set event_id(value) { this.#config.event_id = value }

    get field_name() { return this.#config.field_name }
    set field_name(value) { this.#config.field_name = value }

    get temporal_field() { return this.#config.temporal_field }
    set temporal_field(value) { this.#config.temporal_field = value }

    get preselect() { return this.#config.preselect }
    set preselect(value) { this.#config.preselect = value}

    get external_source_field_name() { return this.#config.external_source_field_name }
    set external_source_field_name(value) { this.#config.external_source_field_name = value}

    get fhir_data() {
        let fhir_field_key = this.#config.external_source_field_name || ''
        if(fhir_field_key.trim()==='') return {}
        return store.getters['app_settings/getFhirField'](fhir_field_key)
    }

    get fhir_field_name() {
        return this.getFhirData('field')
    }

    /**
     * get the unique ID of the underling config
     */
    get id() { return this.#id }

    /**
     * status getter
     */
    get status() { return this.#status || 'normal' }
    set status(value) {
        const status_list = ['normal', 'new', 'deleted']
        if(status_list.indexOf(value)<0) value = 'normal'
        return this.#status = value
    }

    /**
     * config getter
     */
    get config() { return {...this.#config} || {} }

    /**
     * get a configuration value
     * @param {*} name 
     */
    getConfig(name) {
        return this.#config[name]
    }

    /**
     * set a configuration value
     * @param {*} name 
     * @param {*} value 
     */
    setConfig(name, value) {
        this.#config[name] = value
    }

    /**
     * get a FHIR value from the data associated to the mapping
     * @param {*} name 
     */
    getFhirData(name) {
        if(!this.fhir_data) return
        return this.fhir_data[name]
    }

    getFhirFields() {
        const {fhir_fields=[]} = store.state.app_settings
        return fhir_fields
    }

    get validation_errors() {
        return [...this.#validation_errors]
    }

    get has_errors() {
        return this.validation_errors.length>0
    }

    get dirty() {
        for(let [key, value] of Object.entries(this.#original_config)) {
            if(this.#config[key]!=value) return true
        }
        return false
    }

    /**
     * get a string version of the configuration to use as a signature
     * for comparison purposes (i.e. to find duplicates)
     */
    getSignature() {
        const config = {...this.#config}
        const unique_keys = ['event_id','field_name','external_source_field_name']
        let string = ''
        for(let key of unique_keys) string += `${key}:${config[key]}`
        return string
    }

    toJSON() {
        return {
            id: this.id, // ID for internal use
            map_id: this.map_id, // ID stored on DB
            event_id: this.event_id,
            project_id: this.project_id,
            is_record_identifier: this.is_record_identifier,
            field_name: this.field_name,
            external_source_field_name: this.fhir_field_name,
            temporal_field: this.temporal_field,
            preselect: this.preselect,
            status: this.status,
            is_temporal: this.is_temporal,
            category: this.category,
            subcategory: this.subcategory,
        }
    }

}