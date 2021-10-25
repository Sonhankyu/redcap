<template>
  <div>
    <slot name="aside"></slot>
  </div>
</template>

<script>
import { interactions } from '@/variables'

export default {
  data() {
    return {
      fhir_category: null, //base URL for the FHIR resource
      options: {},
      /* fields: [
        {
          key: 'last_name',
          sortable: true
        },
        {
          key: 'first_name',
          sortable: false
        },
        {
          key: 'age',
          label: 'Person age',
          sortable: true,
          // Variant applies to the whole column, including the header and footer
          variant: 'danger'
        }
      ], */
    }
  },
  methods: {
    getParams() {
      const fhir_category = this.fhir_category
      const options = this.options
      return {
        fhir_category,
        options,
      }
    },
    isValid() {

    }
  },
  watch: {
    $v: {
      immediate: true,
      handler(value) {
        this.$emit('validation_changed', value)
      }
    }
  },
  validations() {
    return {}
  }
}
</script>

<style>

</style>