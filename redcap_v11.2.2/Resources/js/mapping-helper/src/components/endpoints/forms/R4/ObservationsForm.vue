<template>
  <div class="observations-options">
    <b-form-group label="Category" label-for="category-input" label-cols="2">
      <b-form-select id="category-input" v-model="category" :options="categories"></b-form-select>
    </b-form-group>
  </div>
</template>

<script>
import { required } from 'vuelidate/lib/validators'
import BaseResource from '@/components/endpoints/forms/BaseResourceForm'
import {fhir_categories} from '@/variables'

const categories = [
  { value: null, text: 'Please select an option', disabled: true },
  { value: 'laboratory', text: 'Laboratory' },
  { value: 'vital-signs', text: 'Vital Signs' },
  { value: 'social-history', text: 'Social History' },
  { value: 'core-characteristics', text: 'Core Characteristics' },
]

export default {
  extends: BaseResource,
  data() {
    return {
      fhir_category: null, //base URL for the FHIR resource
      options: {},
      category: '',
      categories: categories,
    }
  },
  methods: {
    setFhirCategory(category) {
      switch (category) {
        case 'laboratory':
          this.fhir_category = fhir_categories.LABORATORY
          break;
        case 'vital-signs':
          this.fhir_category = fhir_categories.VITAL_SIGNS
          break;
        case 'social-history':
          this.fhir_category = fhir_categories.SMOKING_HISTORY
          break;
        case 'core-characteristics':
          this.fhir_category = fhir_categories.CORE_CHARACTERISTICS
          break;
        default:
          this.fhir_category = null
          break;
      }
    },
  },
  validations() {
    return {
      category: {
        required,
      },
    }
  },
  watch: {
    category: {
      immediate: true,
      /**
       * update the FHIR category and the category option
       * whenever the category selection changes
       */
      handler() {
        const category = this.category
        this.options.category = category
        this.setFhirCategory(category)
      }
    }
  }
}
</script>

<style scoped>
.observations-options >>> .custom-select {
  /* width: auto; */
}
</style>