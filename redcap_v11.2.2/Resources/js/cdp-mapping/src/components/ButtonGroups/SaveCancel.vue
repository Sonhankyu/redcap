<template>
    <div>
        <!-- warnings -->
        <div v-if="mapping_has_errors" class="alert alert-warning d-block">
            <span>Attention: the current configuration needs to be revisioned. Please check the table for more information.</span>
        </div>
        <div v-if="recordIdentifiersInMapping.length!==1" class="alert alert-warning d-block">
             <span v-if="recordIdentifiersInMapping.length<1">Attention: one field must be mapped to the 'source identifier field'.</span>
             <span v-else>Attention: only one field can be mapped to the 'source identifier field', but {{recordIdentifiersInMapping.length}} have been set.</span>
        </div>
        <div v-if="duplicates_detected" class="alert alert-warning d-block">
            Attention: duplicates detected. Please check the table for more information.
        </div>
        <div class="d-flex flex-row justify-content-center align-items-center text-center">
            <button type="button" class="btn btn-outline-success" @click="onClickSave" :disabled="save_disabled"><font-awesome-icon icon="save"/> Save</button>
            <button type="button" class="btn btn-outline-secondary m-2" @click="onClickCancel"><font-awesome-icon icon="times-circle"/> Cancel</button>
        </div>
        <b-modal id="save-modal" title="Success" ok-only @hidden="onSaveModalHidden">
            <p class="my-4">Settings saved!</p>
        </b-modal>
        <b-modal id="error-modal" title="Error" ok-only @hidden="onErrorModalHidden">
            <p class="my-4">There was an error saving the settings!</p>
            <ul>
                <li v-for="(item, index) in error" :key="index">{{index}}: {{item}}</li>
            </ul>
        </b-modal>
    </div>
</template>

<script>
import EventBus from '@/EventBus'
import {mapState, mapGetters} from 'vuex'

const initial_data = {error: null}

export default {
    data() {
        return {...initial_data}
    },
    created() {
        window.addEventListener('beforeunload', this.saveBeforeLeaving)
    },
    destroyed() {
        window.removeEventListener('beforeunload', this.saveBeforeLeaving)
    },
    computed:{
        ...mapState({
            project: state => state.app_settings.project,
            mapping: state => state.mapping.list,
            preview_fields: state => state.settings.preview_fields,
            fhir_cdp_auto_adjudication_enabled: state => state.settings.fhir_cdp_auto_adjudication_enabled,
            realtime_webservice_offset_days: state => state.settings.realtime_webservice_offset_days,
            realtime_webservice_offset_plusminus: state => state.settings.realtime_webservice_offset_plusminus,
        }),
        ...mapGetters({
            recordIdentifiersInMapping: 'mapping/recordIdentifiers',
            mappingConfigurationErrors: 'mapping/getConfigurationErrors',
            mapping_duplicates: 'mapping/duplicates',
            mapping_is_dirty: 'mapping/is_dirty',
        }),
        duplicates_detected() {
            return this.mapping_duplicates.length>0
        },
        mapping_has_errors() {
            let errors = this.mappingConfigurationErrors
            return errors.length>0
        },
        save_disabled() {
            const total_record_identifiers = this.recordIdentifiersInMapping.length
            return Boolean(this.mapping_has_errors || total_record_identifiers!==1 || this.duplicates_detected)
        }
    },
    methods: {
        async saveBeforeLeaving(event) {
            if(!this.mapping_is_dirty) return true
            event.preventDefault()
            const message = 'You have unsaved mapping; please confirm that you want to leave this page without saving.'
            event.returnValue = message
        },
        reset() {
            for(let [key, value] of Object.entries(initial_data)) {
                this[key] = value
            }
        },
        showSuccessModal() {
            this.$bvModal.show('save-modal')
        },
        onSaveModalHidden() {
            EventBus.$emit('settings:saved')
        },
        onErrorModalHidden() {
            EventBus.$emit('settings:error')
        },
        async onClickSave() {
            try {
                this.reset()
                const settings = {
                    preview_fields: this.preview_fields,
                    fhir_cdp_auto_adjudication_enabled: this.fhir_cdp_auto_adjudication_enabled,
                    realtime_webservice_offset_days: this.realtime_webservice_offset_days,
                    realtime_webservice_offset_plusminus: this.realtime_webservice_offset_plusminus,
                }
                const mapping = [...this.mapping]
                const response = await this.$API.dispatch('settings/set',{settings, mapping})
                
                this.$bvModal.show('save-modal')
            } catch (error) {
                const {response:{data={}}={}} = error
                this.error = data
                this.$bvModal.show('error-modal')
                console.error(error.toJSON())
                console.error(error)
            }
        },
        onClickCancel() {
            EventBus.$emit('settings:canceled')
        }
    }
}
</script>

<style scoped>
/* .alert.alert-warning {
    border-color: #ffeeba !important;
} */
</style>