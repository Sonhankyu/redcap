<template>
    <b-dropdown class="scrollable" size="sm" lazy block variant="outline-secondary" v-bind="$attrs">
        <template #button-content>
            <span class="label" v-if="!label">Select...</span>
            <span class="label" v-else>{{label}}</span>
        </template>

        <b-dropdown-item-button v-for="(option, index) in options || []" :key="index" :active="option.value===selected" @click="selected=option.value">
            <span>{{option.label}}</span>
        </b-dropdown-item-button>

  </b-dropdown>
</template>



<script>
export default {
    computed: {
        /**
         * get v-model to work with props
         */
        selected: {
            get() { return this.value },
            set(value) { this.$emit('input', value) },
        },
        label() {
            for(let {value, label} of this.options) {
                if(value==this.value) return label
            }
            return false
        },
        options() {
            const options = this.$store.getters['app_settings/getPreselectOptions']
            return options
        },
    },
    props: {
        /* options: {
            type: [Array,Object],
            default: ()=>[]
        }, */
        /**
         * manipulated by v-model
         */
        value: {
            type: [Number,String],
            default: null
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