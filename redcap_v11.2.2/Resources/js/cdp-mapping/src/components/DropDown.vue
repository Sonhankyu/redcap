<template>
    <b-dropdown class="scrollable" size="sm" lazy block variant="outline-secondary" v-bind="$attrs">
        <template #button-content>
            <slot name="button-content">
                <span class="label" v-if="!selected">Select a field...</span>
                <span class="label" v-else>{{selected}}</span>
            </slot>
        </template>
        <b-dropdown-text v-if="options_length<1">No data</b-dropdown-text>
        <template v-else>
            <slot>
                <b-dropdown-item-button v-for="(option, key) in options || []" :key="key" :active="key===selected" @click="selected=key">
                    <span>{{option}}</span>
                </b-dropdown-item-button>
            </slot>
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
            if(Array.isArray(options)) return options.length
            else return Object.values(options).length
        },
    },
    props: {
        options: {
            type: [Object,Array],
            default: () => []
        },
        /**
         * manipulated by v-model
         */
        value: {
            type: [Number,String],
            default: ''
        }
    },
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