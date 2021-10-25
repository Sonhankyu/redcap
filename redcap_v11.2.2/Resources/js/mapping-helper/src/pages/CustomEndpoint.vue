<template>
<div>
  <b-card>

    <CustomForm @data-received="onDataReceived" @error="onError"/>
    <ResourceInfo class="my-2" :description="page_description" />

  </b-card>

  <PayloadPanel class="mt-2" :payload="payload" />


</div>
</template>

<script>
/**
 * The FHIR resource depends on the current route
 * and is accessible via $refs['fhir-resource].
 * All resources are of type /components/endpoints/BaseResource
 * and have the method getParams
 */
import Seatbelt from '@/libraries/Seatbelt'
import ResourceInfo from '@/components/endpoints/ResourceInfo'
import CustomForm from '@/components/endpoints/forms/CustomForm'
import PayloadPanel from '@/components/Payload/PayloadPanel'

export default {
  components: {ResourceInfo, PayloadPanel, CustomForm,},
  data() {
    return {
      filter: null,
      filterOn: [],
      stacked: false,
      payload: null,
    }
  },
  computed: {

    page_description() {
      const {meta: {description=''}} = this.$route
      return description
    },
  },
  methods: {
    onDataReceived(payload) {
      const {data, metadata} = payload
      this.data = data
      this.metadata = Seatbelt(metadata)
      this.payload = metadata.payload
    },
    onError() {
      this.payload = null
    },
    /**
     * update the title of the page
     * using the name of the resource.
     * the information is defined in the route
     */
    updateTitle(value, previous) {
      const new_name = Seatbelt(value).name
      const previous_name = Seatbelt(previous).name
      if(new_name!==previous_name) this.payload = null
    }
  },
  watch: {
    $route: {
      immediate: true,
      handler(value={}, previous={}) {
        this.updateTitle(value, previous)
        this.fhir_resources = []
      }
    },
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
* >>> .modal-body {
  word-break: break-word;
}
</style>