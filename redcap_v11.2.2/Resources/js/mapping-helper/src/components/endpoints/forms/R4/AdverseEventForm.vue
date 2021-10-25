<template>
  <div>
    <b-form-group label="Study" label-for="study-input" label-cols="2">
      <b-form-input v-model.trim="study"
        name="study"
        id="study-input"
        placeholder="Enter a study ID"/>
    </b-form-group>
  </div>
</template>

<script>
import BaseResource from '@/components/endpoints/forms/BaseResourceForm'
import {fhir_categories} from '@/variables'

export default {
  extends: BaseResource,
  data() {
    return {
      fhir_category: fhir_categories.ADVERSE_EVENT, //base URL for the FHIR resource
      study: '',
      options: {},
    }
  },
  watch: {
    study: {
      immediate: true,
      handler(study) {
        // NOTE: I use 
        if(study=='') delete this.options['study_identifier']
        else this.options['study_identifier'] = study
      }
    }
  }
}
</script>

<style>

</style>