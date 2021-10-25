<template>
  <span :title="title" v-if="field_data">
      <span class="d-block small font-weight-bold">{{field_data.field_name}}</span>
      <span class="d-block" v-html="field_data.element_label"></span>
      <span class="d-block small muted font-italic">({{form_data.menu}})</span>
  </span>
</template>

<script>
export default {
  data() {
    return {
      field_data: {},
      form_data: {},
    }
  },
  computed: {
  },
  props: {
    field_name: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      default: ''
    },
  },
  methods: {
    getFieldData(field_name) {
      return this.$store.getters['app_settings/getFieldData'](field_name)
    },
    getFormData(form_name) {
      return this.$store.getters['app_settings/getFormData'](form_name)
    },
  },
  watch: {
    field_name: {
      immediate: true,
      handler(field_name) {
        this.field_data = this.getFieldData(field_name)
        const {form_name} = {...this.field_data}
        this.form_data = this.getFormData(form_name)
      }
    }
  }
}
</script>

<style>

</style>