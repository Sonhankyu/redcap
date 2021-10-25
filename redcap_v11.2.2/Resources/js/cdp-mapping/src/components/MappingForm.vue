<template>
    <div class="wrapper">
        <div class="form-group">
            <label for="fhir-field">{{translations['form_label_fhir_field']}}</label>
            <FhirFieldsSelect v-model="external_source_field_name" />
        </div>
        <div class="form-group mt-2 d-flex redcap-event-field">
          <div>
            <label class="">{{translations['form_label_redcap_event']}}</label>
            <Events class="w-100" :options="arms" v-model="event_id" :disabled="events_disabled"/>
          </div>
          <div class="ml-2">
            <label class="">{{translations['form_label_redcap_field']}}</label>
            <Fields :options="getForms(event_id)" v-model="redcap_field"/>
          </div>
        </div>
        <div class="form-group mt-2 d-flex">
            <div class="w-100">
                <label for="datetime-field">{{translations['form_label_date_time']}}</label>
                <Fields :options="groupedTemporalFields" v-model="temporal_field" :disabled="disabledTemporal"/>
            </div>
            <div class="w-100 ml-2">
                <label for="preselect-field">{{translations['form_label_preselect_strategy']}}</label>
                <SelectPreselectedValue v-model="preselect_value"  :disabled="disabledTemporal"/>
            </div>
        </div>

        <footer>
          <slot name="footer" :config="config" :validation="$v"></slot>
        </footer>
    </div>
</template>

<script>
import { required } from 'vuelidate/lib/validators'

import { mapState, mapGetters } from 'vuex'
import SelectProjectField from '@/components/MappingTable/SelectProjectField'
import SelectPreselectedValue from '@/components/MappingTable/SelectPreselectedValue'
import FhirFieldsSelect from '@/components/FhirFieldsDropDown/Select'
import EventFieldsDropDown from '@/components/EventFieldsDropDown'
import RedcapFieldSelect from '@/components/RedcapField/RedcapFieldSelect'
import Fields from '@/components/RedcapField/Fields'
import Events from '@/components/RedcapField/Events'
import DropDown from '@/components/DropDown'

export default {
    components: { SelectProjectField, SelectPreselectedValue, FhirFieldsSelect, EventFieldsDropDown, RedcapFieldSelect, Fields, Events,},
    data() {
        return {
          temporal_field: null,
          preselect_value: null,
          event_id: '',
          redcap_field: '',
          external_source_field_name: '',
        }
    },
    props: {
      mapping: {
        type: Object,
        default: null
      }
    },
    computed: {
        ...mapState({
          translations: state => state.app_settings.translations,
          project: state => state.app_settings.project,
          project_id: state => state.app_settings.project_id,
        }),
        ...mapGetters({
            groupedTemporalFields: 'app_settings/groupedProjectTemporalFields',
        }),
        events_disabled() {
          const event_id = parseInt(this.event_id)
          if(isNaN(event_id)) return false
          const event_ids = Object.keys(this.events)
          const unique_event = event_ids.length===1
          if(!unique_event) return false
          const found = event_ids.some(id => event_id==id)
          if(found) return true
          return false
        },
        disabledTemporal() {
          if(!this.fhir_field) return true
          else return this.fhir_field.temporal!=1
        },
        fhir_field() {
          if(!this.external_source_field_name) return {}
          const fhir_field = this.$store.getters['app_settings/getFhirField'](this.external_source_field_name)
          return fhir_field
        },
        arms() { return this.project.arms },
        events() { return this.project.events },
        /**
         * expose the configuration.
         * will be available in the slot scope
         */
        config() {
          if(!this.fhir_field) return
          const { field:fhir_field_name, identifier } = this.fhir_field

          const config = {

            // map_id: null,
            event_id: this.event_id,
            // project_id: this.project_id,
            external_source_field_name: fhir_field_name,
            field_name: this.redcap_field,
            identifier: identifier,
            preselect: this.preselect_value,
            temporal_field: this.temporal_field,
          }
          return config
        }
    },
    methods: {
        getForms(event_id) {
            return this.$store.getters['app_settings/getForms'](event_id)
        },
    },
    watch: {
      /* event_id: {
        immediate: false,
        handler() {
          this.redcap_field = ''
        }
      }, */
      mapping: {
        immediate: true,
        handler(mapping) {
          if(!mapping) return
          this.event_id = mapping.event_id
          this.temporal_field = mapping.temporal_field
          this.preselect_value = mapping.preselect
          this.redcap_field = mapping.field_name
          this.external_source_field_name = mapping.external_source_field_name
        }
      },
      disabledTemporal: {
        immediate: true,
        handler(disabled) {
          if(disabled) {
            this.temporal_field = null
            this.preselect_value = null
          }
        }
      }
    },
    validations() {
      return {
        fhir_field: {
          required,
        },
        redcap_field: {
          required,
        }
      }
    }
}
</script>

<style scoped>
.redcap-event-field > div {
  flex: 1;
}
.redcap-field {
  display: flex;
  flex-direction: row;
}
label {
  font-weight: bold;
  font-size: 80%;
}
/* using deep selector >>>
 * could also prepend with /deep/ instead (more compatible with SASS)
 */
</style>