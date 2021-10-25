<template>
  <span v-text="label"/>
</template>

<script>
export default {
  computed: {
    options() {
      return this.$store.getters['app_settings/getPreselectOptions']
    },
    label() {
      let option = this.options.find(option => option.value===this.value)
      if(!option) return ''
      return option.label
    }
  },
  props: {
    value: {
      type: String,
      default: ''
    }
  }
}
</script>

<style>

</style>