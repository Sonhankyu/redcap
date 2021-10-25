<template>
  <div>
      <!-- <b-form-checkbox-group
        class="mt-2"
        id="checkbox-group-1"
        v-model="selected"
        :options="status_options"
        :aria-describedby="ariaDescribedby"
        name="status"
      ></b-form-checkbox-group> -->
  </div>
</template>

<script>
import BaseResource from '@/components/endpoints/forms/BaseResourceForm'
import {fhir_categories} from '@/variables'

const status_options= [
  { text: 'active', value: 'active' },
  { text: 'inactive', value: 'inactive' },
  { text: 'resolved', value: 'resolved' },
]

export default {
  extends: BaseResource,
  data() {
    return {
      fhir_category: fhir_categories.ALLERGY_INTOLERANCE, //base URL for the FHIR resource
      options: {},
      selected: [], // Must be an array reference!
      status_options: [...status_options]
    }
  },
  watch: {
    selected: {
      immediate: true,
      /**
       * update the status whenever the
       * status_options are changed
       */
      handler() {
        const selected = [...this.selected]
        if(selected.length<1) {
          delete this.options['clinical-status']
        }else {
          this.options['clinical-status'] = selected.join(',')
        }
      }
    }
  }
}
</script>

<style>

</style>