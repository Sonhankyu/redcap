<template>
  <select class="form-control-sm" v-model="selected">
    <option value="">-- none --</option>
    <optgroup v-for="(category, category_name) in options" :key="category_name" :label="category_name">
        <option v-for="(label, key) in category || []" :key="key" :value="key">{{label}}</option>
    </optgroup>
  </select>
</template>

<script>
export default {
    computed: {
        /**
         * calculate the label for the arm
         */
        // arm_label() {return (index, name) => `Arm ${index}: ${name}`},
        /**
         * get v-model to work with props
         */
        selected: {
            get() { return this.value },
            set(value) { this.$emit('input', value) },
        }
    },
    props: {
        options: {
            type: [Array,Object],
            default: ()=>[]
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

<style>

</style>