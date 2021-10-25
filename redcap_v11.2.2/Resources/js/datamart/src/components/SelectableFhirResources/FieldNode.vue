<template>
  <div class="ml-0 p-0 d-flex align-items-center fhir">
    <b-form-checkbox :id="`checkbox-${data.field}`" v-model="selected" :disabled="disabled" switch/>
    <!-- <b-button  :disabled="disabled" @click="selected=!selected" size="xs" variant="outline-light" class="mr-2" style="" >
        <font-awesome-icon class="text-success" :icon="['fas', 'check-circle']" fixed-width v-if="selected"/>
        <font-awesome-icon class="text-muted" :icon="['fas', 'circle']" fixed-width v-else/>
    </b-button> -->
    <!-- <b-form-checkbox :id="`checkbox-${data.field}`" switch :disabled="disabled" @change="onChange" :value="data.selected"/> -->
    <label :for="`checkbox-${data.field}`" class="m-0" :class="{'disabled-option': data.disabled}">
        <span>{{data.field}}</span>
        <span class="ml-2 font-italic">({{data.label}})</span>
    </label>
    <span class="ml-2 text-danger" v-if="data.disabled" v-b-tooltip.hover :title="`${data.disabled_reason}`">
        <font-awesome-icon :icon="['fas', 'exclamation-circle']" fixed-width/>
    </span>
  </div>
</template>

<script>
import { FhirField } from '@/libraries/metadata'

export default {
    data() {
        return {}
    },
    props: {
        data: {
            type: [FhirField],
            default: null
        },
    },
    created() {

    },
    computed: {
        mandatoryFields() { return this.$store.state.settings.mandatoryFields },
        disabled() {
            const {field} = this.data
            return this.mandatoryFields.indexOf(field)>=0
        },
        selected: {
            get() {
                if(this.mandatoryFields.indexOf(this.data.field)>=0) return true
                return this.data.selected
            },
            set(value) {
                const name = this.data.field
                this.$store.dispatch('revision/updateFields', {name: name, checked: value})
            }
        }

    },
    methods: {
        onClick(item) {
            let params = {field: item.field+='miao'}
            this.$store.dispatch('metadata/update', {item, params})
        },
        onChange(checked) {
            this.data.selected = checked
        }
    },
}
</script>

<style scoped>
.fhir > * {
    cursor: pointer;
}
.fhir label.disabled-option {
    text-decoration: line-through;
}
</style>