<template>
  <div>
  </div>
</template>

<script>
import BaseResource from '@/components/endpoints/forms/BaseResourceForm'

import {fhir_categories} from '@/variables'

export default {
  extends: BaseResource,
  data() {
    return {
      fhir_category: fhir_categories.DEMOGRAPHICS, //base URL for the FHIR resource
    }
  },
}
</script>

<style>

</style>