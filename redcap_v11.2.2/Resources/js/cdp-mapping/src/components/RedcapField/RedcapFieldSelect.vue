<template>
  <div>
    <section class="wrapper">
      <slot name="event-header">
        <span class="small">Event</span>
      </slot>
      <Events :options="arms" v-model="selected_event_id"/>
    </section>
    <section class="wrapper">
      <slot name="field-header">
        <span class="small">Field</span>
      </slot>
      <Fields :options="forms" v-model="selected_field_key"/>
    </section>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import Events from './Events'
import Fields from './Fields'

import * as R from 'ramda'

export default {
  components: {Events, Fields},
  props: {
    event: {
      type: String,
      default: ''
    },
    field: {
      type: String,
      default: ''
    },
  },
  computed: {
    selected_event_id: {
      get() {return this.event},
      set(value) {
        // notice that field is reset when the event is updated
        const data = {
          event: value,
          field: null,
        }
        this.$emit('input', data)
      }
    },
    selected_field_key: {
      get() {return this.field},
      set(value) {
        const data = {
          event: this.event,
          field: value
        }
        this.$emit('input', data)
      }
    },
    ...mapState({
        project: state => state.app_settings.project,
    }),
    ...mapGetters({
        groupedFields: 'app_settings/groupedProjectFields',
    }),

    arms() {
        const {arms} = this.project
        return arms
    },
    /**
     * pivot
     */
    events_forms() {
        const {events_forms} = this.project
        return events_forms
    },
    forms() {
        const event_id = this.selected_event_id
        return this.$store.getters['app_settings/getForms'](event_id)
    },
  },
}
</script>

<style scoped>
section {
  display: contents;
}
</style>