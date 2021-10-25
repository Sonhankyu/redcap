<template>
  <div>
    <b-form-group label="Status" label-for="checkbox-group-1" label-cols="2">
      <b-form-checkbox-group
        class="mt-2"
        id="checkbox-group-1"
        v-model="selected"
        :options="status_options"
        name="status"
      ></b-form-checkbox-group>
    </b-form-group>
  </div>
</template>

<script>
import BaseResource from '@/components/endpoints/forms/BaseResourceForm'
import {fhir_categories} from '@/variables'

const status_options= [
  { text: 'Active', value: 'active' },
  { text: 'Completed', value: 'completed' },
  { text: 'On-hold', value: 'on-hold' },
  { text: 'Stopped', value: 'stopped' }
]

export default {
  extends: BaseResource,
  data() {
    return {
      fhir_category: fhir_categories.MEDICATIONS, //base URL for the FHIR resource
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
          delete this.options.status
        }else {
          this.options.status = selected.join(',')
        }
      }
    }
  }
}
</script>

<style>

</style>