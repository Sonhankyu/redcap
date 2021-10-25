<template>
    <b-dropdown class="scrollable" size="sm" lazy block variant="outline-secondary" v-bind="$attrs">
        <template #button-content>
            <span class="label" v-if="!label" >Select...</span>
            <span class="label" v-else>{{label}}</span>
        </template>
        <b-dropdown-text v-if="options_length<1">No data</b-dropdown-text>
        <template v-else>
            <b-dropdown-group v-for="(arm, arm_index) in options" :key="arm_index">
                <b-dropdown-header><span >{{getArmLabel(arm_index, arm.name)}}</span></b-dropdown-header>
                <b-dropdown-item-button v-for="(event, event_id) in arm.events || []" :key="event_id"
                    :active="event_id===selected"
                    v-show="eventHasForms(event_id)" @click="onClick(event_id, arm, arm_index)">
                    <span>{{event.descrip}} ({{getArmLabel(arm_index, arm.name)}})</span>
                </b-dropdown-item-button>
                <b-dropdown-divider></b-dropdown-divider>
            </b-dropdown-group>
        </template>
    </b-dropdown>
</template>

<script>

export default {
    computed: {
        selected: {
            get() { return this.value },
            set(value) { this.$emit('input', value) },
        },
        options_length() {
            const options = this.options
            return Object.values(options).length
        },
        events_forms() {
            const {project:{events_forms={}}={}} = this.$store.state.app_settings
            return events_forms
        },
        events() { return this.$store.state.app_settings.project.events },
        label() {
            if(!this.events) return
            const event = this.events[this.selected]
            if(!event) return
            const {arm_num='', arm_name='', name=''} = event
            return `${name} (Arm ${arm_num}: ${arm_name})`
        },
    },
    props: {
        options: {
            type: [Object],
            default: ()=>({})
        },
        /**
         * manipulated by v-model
         */
        value: {
            type: [Number,String],
            default: ''
        }
    },
    methods: {
        onClick(event_id, arm, arm_index) {
            this.selected = event_id
            this.arm_index = arm_index
            this.arm_name = arm.name || ''
        },
        /**
         * option will not show if event has no forms
         */
        eventHasForms(event) {
            try {
                const forms = this.events_forms[event] || []
                return forms.length>0
            } catch (error) {
                return false
            }
        },
        /**
         * calculate the label for the arm
         */
        getArmLabel(index, name) {return `Arm ${index}: ${name}`},
    }
}
</script>

<style scoped>
.label {
    display: block;
    overflow: hidden;
    max-height: 70%;
    text-overflow: ellipsis;
}
.scrollable >>> ul[role="menu"] {
  max-height: 50vh;
  overflow: auto;
}
.scrollable >>> button.dropdown-toggle {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
}
</style>