<template>
<div>
  <div v-if="!is_allowed" class="alert alert-warning">
    <span class="font-weight-bold d-block">Attention</span>
    <span>Instant adjudication is disabled in REDCap. Please enable it in the CDIS setting page if you want to use this feature.</span>
  </div>
  <div>
    <b-dropdown :variant="auto_adjudication_enabled ? 'success' : 'danger'"
      :disabled="!can_be_enabled" class="d-block" size="sm">
      <template #button-content>
        <span class="label" v-if="auto_adjudication_enabled">{{translations['instant_adjudication_enabled']}}</span>
        <span class="label" v-else>{{translations['instant_adjudication_disabled']}}</span>
      </template>
      <b-dropdown-item-button
        @click="setAutoAdjudication(false)" :active="auto_adjudication_enabled==false" >
        <span><font-awesome-icon icon="minus-circle" class="text-danger"/> {{translations['instant_adjudication_disabled_text']}}</span>
      </b-dropdown-item-button>
      <b-dropdown-item-button
        @click="setAutoAdjudication(true)" :active="auto_adjudication_enabled==true">
        <span><font-awesome-icon icon="check-circle" class="text-success"/> {{translations['instant_adjudication_enabled_text']}}</span>
      </b-dropdown-item-button>
    </b-dropdown>
    <span v-html="total_selected_fields_text"></span>
  </div>
  <span v-if="!can_be_enabled" class="small text-muted">{{translations['instant_adjudication_disabled_description']}}</span>
</div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
  data() {
    return {
    }
  },
  computed: {
    ...mapState({
      translations: state => state.app_settings.translations,
      temporal_mapping: state => {
        const mapping = [...state.mapping.list]
        return mapping.filter(item => item.is_temporal)
      },
      total_selected_fields_text() {
        let text = this.translations.instant_adjudication_total_selected_fields
        if(!text) return
        const replacements = {
                fields: this.enabled_temporal_mapping.length,
                total_fields: this.temporal_mapping.length,
            }
            for(let[key, value] of Object.entries(replacements)) {
                text = text.replace(`{{${key}}}`, `<strong>${value}</strong>`)
            }
            return text
      }
    }),
    enabled_temporal_mapping() {
      const temporal_mapping = this.temporal_mapping

      const enabled = temporal_mapping.filter(item => {
        const {temporal_field, preselect} = item

        if(Boolean(temporal_field)==false) return false
        if(Boolean(preselect)==false) return false
        return true
      })
      return enabled
    },
    can_be_enabled() {
      if(this.enabled_temporal_mapping.length<1) return false 
      const all_temporal_set = this.temporal_mapping.length === this.enabled_temporal_mapping.length
      return Boolean(this.is_allowed && all_temporal_set)
    },
    is_allowed() {
      const project_settings = this.$store.state.app_settings.project
      const {fhir_cdp_auto_adjudication_allowed, fhir_cdp_auto_adjudication_enabled} = project_settings
      return fhir_cdp_auto_adjudication_allowed
    },
    auto_adjudication_enabled: {
      get() { 
        return this.$store.state.settings.fhir_cdp_auto_adjudication_enabled==true
        },
      set(value) {
        this.setAutoAdjudication(value)
      }
    },
  },
  methods: {
    setAutoAdjudication(value) {
      this.$store.dispatch('settings/setAutoAdjudication', value)
    }
  },
  watch: {
    can_be_enabled: {
      immediate: true,
      handler(value) {
        if(value===false) {
          this.setAutoAdjudication(false)
        }
      }
    }
  }
}
</script>

<style>

</style>