<template>
  <div ref="wrapper">
      <b-button-group class="w-100">
        <b-dropdown :disabled="false"
        variant="primary"
        class="flex-fill group-dropdown"
        menu-class="scrollable-dropdown">
            <template #button-content>
                <span v-if="selected_event">
                    <span class="mr-1">({{selected_arm && selected_arm.name}})</span>
                    <span>{{selected_event.name}}</span>
                </span>
                <span v-else>Event...</span>
            </template>
            <b-dropdown-group v-for="(arm, arm_index) in arms" :key="`arm-${arm_index}`" :header="arm.name">
                <b-dropdown-item-button v-for="(event, event_index) in arm.events"
                    :key="`event-${event_index}`" @click="onEventSelected(event_index)"
                    :active="event_index==selected_event_id_proxy">
                    {{event.descrip}}
                </b-dropdown-item-button>
            </b-dropdown-group>
        </b-dropdown>
        
        <b-dropdown :disabled="!selected_event"
        variant="primary"
        class="flex-fill group-dropdown"
        menu-class="scrollable-dropdown">
            <template #button-content>
                <span v-if="selected_field">
                    <span>{{selected_field.field_name}}</span>
                    <span class="d-inline-block small ml-1">
                        (<span class="field_label">{{selected_field.element_label}}</span>)
                    </span>
                </span>
                <span v-else>Field...</span>
            </template>
            <template v-if="Object.keys(forms).length">
                <b-dropdown-group v-for="(form, form_key) in forms" :key="`form-${form_key}`" :header="form.menu">
                    <b-dropdown-item-button v-for="(field, field_key) in form.fields"
                        :key="`field-${field_key}`" @click="onFieldSelected(field_key)"
                        :active="field_key==selected_field_key_proxy">
                        <span>{{field_key}}</span>
                        <span class="small d-block">{{field}}</span>
                    </b-dropdown-item-button>
                </b-dropdown-group>
            </template>
            <template v-else>
                <b-dropdown-text>No Forms in this event</b-dropdown-text>
            </template>

        </b-dropdown>
      </b-button-group>

  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
    data() {
        return {
            selected_event_id_proxy: null,
            selected_field_key_proxy: null,
        }
    },
    props: {
        selected_event_id: {
            type: [String,Number],
            default: null
        },
        selected_field_key: {
            type: String,
            default: null
        },
    },
    computed: {
        ...mapState({
            project: state => state.app_settings.project,
        }),
        arms() {
            const {arms} = this.project
            const normalized_arms = {}
            for(let [key, data] of Object.entries(arms)) {
                let {id, name, events} = data
                normalized_arms[id] = {id, name, events}
            }
            return normalized_arms
        },
        events_forms() {
            const {events_forms} = this.project
            return events_forms
        },
        events() {
            const {events=[]} = this.project
            return events
        },
        forms() {
            const event_id = this.selected_event_id_proxy
            if(!event_id) return []
            const {forms=[], events_forms} = this.project
            const form_keys = this.events_forms[event_id] || []
            const filtered_forms = {}
            form_keys.forEach(form_key => {
                if(form_key in forms) filtered_forms[form_key] = forms[form_key]
            })
            return filtered_forms
        },
        fields() {
            const {metadata={}} = this.project
            return metadata
        },
        selected_arm() {
            if(!this.selected_event_id_proxy) return null
            for(let[key, arm] of Object.entries(this.arms)) {
                let {events={}} = arm
                if(Object.keys(events).indexOf(this.selected_event_id_proxy)>=0) return arm
            }
            return null
        },
        selected_event() {
            if(!this.selected_event_id_proxy) return null
            const event = this.events[this.selected_event_id_proxy] || null
            return event
        },
        selected_field() {
            if(!this.selected_field_key_proxy) return null
            const field = this.fields[this.selected_field_key_proxy] || null
            return field
        },
    },
    methods: {
        onEventSelected(id) {
            this.selected_event_id_proxy = id
        },
        onFieldSelected(field_key) {
            this.selected_field_key_proxy = field_key
        },
        onBlur(event) {
            console.log(event)
            // this.show = false
        }
    },
    watch: {
        /**
         * automaticaly select the event ID if
         * only one arm and one event are available
         */
        arms: {
            immediate: true,
            handler(arms) {
                if(Object.values(arms).length!==1) return
                const single_arm = Object.values(arms)[0]
                const {events={}} = single_arm
                if(Object.values(events).length!==1) return
                const single_event = Object.keys(events)[0]
                this.selected_event_id_proxy = single_event
            }
        },
        /**
         * reset the field_key whenever the event_id is changed
         */
        selected_event_id_proxy: {
            immediate: true,
            handler(id) {
                this.selected_field_key_proxy = null
            }
        },
        /**
         * emit data whenever the field key is changed
         */
        selected_field_key_proxy: {
            immediate: true,
            handler() {
                const data = {
                    event_id: this.selected_event_id_proxy,
                    field: this.selected_field_key_proxy,
                }
                this.$emit('input', data)
            }
        },
        // selected_event_id: {
        //     immediate: true,
        //     handler(id) {
        //         console.log(id)
        //     }
        // },
        // selected_field_key: {
        //     immediate: true,
        //     handler(key) {
        //         console.log(key)
        //     }
        // },
    }
}
</script>

<style>
.scrollable-dropdown {
    max-height: 80vh;
    overflow-y: auto;
}
</style>
<style scoped>
.group-dropdown {
    max-width: 50%;
    flex: 1;
}
.field_label {
    display: inline-block;
    max-width: 100px;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow-x: hidden;
    vertical-align: middle;
}
</style>