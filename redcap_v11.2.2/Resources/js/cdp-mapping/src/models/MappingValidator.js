import store from '@/store'

export default class MappingValidator {
  #errors = []

  constructor() {

  }

  addError(message) {
    this.#errors.push(message)
  }

  validate(mapping) {
    this.#errors = []
    for(let [_, validateRule] of Object.entries(this.rules)) {
      validateRule(mapping)
    }
    return [...this.#errors]
  }

  get errors() {
    return [...this.#errors]
  }

  get rules() {
    return {
      isMappingEventNull: (mapping) => {
        if(mapping.event_id) return true
        const error_message = 'No event was specified for this mapping'
        this.addError(error_message)
        return false
      },

      isExternalSourceFieldValid: (mapping) => {
        if(mapping.fhir_field_name) return true
        const error_message = 'A valid external source field must be selected'
        this.addError(error_message)
        return false
      },
    
      isMappingEventInProject: (mapping) => {
          const {events} = store.state.app_settings.project
          const event_ids = Object.keys(events)
          if(event_ids.indexOf(mapping.event_id)<0) {
              const error_message = `The event ID ${mapping.event_id} was not found in this project`
              this.addError(error_message)
              return false
          }
          return true
      },
    
      hasMappingEventValidData: (mapping) => {
        const event_data = store.getters['app_settings/getEventData'](mapping.event_id)
        if(typeof event_data!=='object') {
            const error_message = `No data has been found for the event ID ${mapping.event_id}; check if this event exists in the current project`
            this.addError(error_message)
            return false
        }
        return true
      },
    
      isMappingFieldFormatValid: (mapping) => {
        if(typeof mapping.field_name!=='string') {
            const error_message = `The field format is invalid; expected string`
            this.addError(error_message)
            return false
        }
        return true
      },
    
      isMappingFieldNonEmptyString: (mapping) => {
        if(typeof mapping.field_name==='string' && mapping.field_name.trim()==='') {
          const error_message = `The field value is not set`
          this.addError(error_message)
          return false
        }
        return true
      },
      
      mappingFieldExists: (mapping) => {
        const forms = store.getters['app_settings/getForms'](mapping.event_id)
        let all_fields = []
        for(let[_, fields] of Object.entries(forms))
        {
            all_fields = [...all_fields, ...fields]
        }
        const exists = all_fields.some(field => field.field_name==mapping.field_name)
        if(!exists) {
            const event_name = store.getters['app_settings/getEventName'](mapping.event_id)
            const error_message = `The field '${mapping.field_name}' does not exist in any form associated to the event '${event_name}' (ID ${this.event_id})`
            this.addError(error_message)
            return false
        }
        return true
      },
    }
  }
  
}