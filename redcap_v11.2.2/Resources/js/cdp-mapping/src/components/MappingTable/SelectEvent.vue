<template>
  <select class="form-control-sm" v-model="selected">
    <optgroup v-for="(arm, arm_index) in options" :key="arm_index" :label="arm_label(arm_index, arm.name)">
        <option v-for="(field, event) in arm.events || []" :key="event" :value="event">{{field.descrip}} ({{arm_label(arm_index, arm.name)}})</option>
    </optgroup>
  </select>
</template>

<script>
export default {
    computed: {
        /**
         * calculate the label for the arm
         */
        arm_label() {return (index, name) => `Arm ${index}: ${name}`},
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