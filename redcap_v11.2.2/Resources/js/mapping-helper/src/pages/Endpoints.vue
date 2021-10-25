<template>
<div>
  <b-card>
      <div>
      <div>
        <b-form-group label="MRN" label-for="mrn-input" label-cols="2">
          <b-form-input v-model.trim="mrn"
            name="mrn"
            id="mrn-input"
            placeholder="Enter a medical record number"
            @keypress.enter="fetch" />
        </b-form-group>
      </div>

      <div class="mt-2">
        <router-view name="form" ref="fhir-resource" @validation_changed="onValidationChanged" />
      </div>
    </div>

    <router-view class="mt-2" name="dateRange" :from.sync="options.dateStart" :to.sync="options.dateEnd" />

    <div class="mt-2">
      <b-button variant="outline-primary" size="sm" :disabled="isButtonDisabled" @click="fetch">
        <font-awesome-icon v-if="loading" :icon="['fas', 'spinner']" spin fixed-width />
        <font-awesome-icon v-else  :icon="['fas', 'cloud-download-alt']" fixed-width />
        <span class="ml-2">Fetch</span>
      </b-button>
    </div>

    <ResourceInfo class="mt-2" :description="page_description" />

  </b-card>

  <ResourceTableWrapper :items="fhir_resources" >
    <template v-slot:default="rtScope">
      <!-- using rtScope I can use properties from ResourceTableWrapper in the slot scope -->
      <router-view name="table" v-bind="rtScope"></router-view>
    </template>

  </ResourceTableWrapper>

  <PayloadPanel :payload="payload" />


</div>
</template>

<script>
/**
 * The FHIR resource depends on the current route
 * and is accessible via $refs['fhir-resource].
 * All resources are of type /components/endpoints/BaseResource
 * and have the method getParams
 * 
 * named views are provided by the router
 */
import Seatbelt from '@/libraries/Seatbelt'
import ResourceInfo from '@/components/endpoints/ResourceInfo'
import PayloadPanel from '@/components/Payload/PayloadPanel'
import ResourceTableWrapper from '@/components/endpoints/tables/ResourceTableWrapper'
import moment from 'moment'

/**
 * check if a date is empty or has a valid format
 */
const isValidDate = (value) => {
  if(value=='' || value==null) return true
  const validformat = 'YYYY-MM-DD'
  return moment(value, validformat).format(validformat) === value
}

export default {
  components: {ResourceInfo, PayloadPanel, ResourceTableWrapper,},
  data() {
    const dateStart = moment().subtract(1, 'years').format('YYYY-MM-DD')
    const dateEnd = moment().format('YYYY-MM-DD')

    return {
      test: null,
      mrn: '',
      // mrn: '207023', // immunization
      // mrn: '202434', // adverse events
      //mrn: '2000789', // POC vandy
      options: {
        dateStart: dateStart,
        dateEnd: dateEnd,
      },
      loading: false,
      payload: null,
      fhir_component_validation: {},
      fhir_resources: [],
    }
  },
  computed: {
    isButtonDisabled() {
      const mrn_length = this.mrn.length || 0
      const is_loading = this.loading == true
      const thisInvalid = this.$v.$invalid
      const {$invalid: fhir_resource_invalid=false} = this.fhir_component_validation
      return is_loading || mrn_length<1 || thisInvalid || fhir_resource_invalid
    },
    page_description() {
      const {meta: {description=''}} = this.$route
      return description
    }
  },
  methods: {
    fetch() {
      const resource_component = this.$refs['fhir-resource']
      if(resource_component) {
        if(typeof resource_component.getParams == 'function') {
          const {fhir_category, options} = resource_component.getParams() || {}
          let async_callable = () => this.sendFhirRequest(fhir_category, {...options})
          if(async_callable) this.wrapRequest(async_callable)
        }
      }
    },
    /**
     * HOC function for the async requests
     */
    async wrapRequest(callable) {
      try {
        this.loading = true
        this.error = null
        this.payload = null
        this.fhir_resources =[]
        const data = await callable()

        const {data: entries} = data
        this.fhir_resources = entries
        this.payload = {...data.metadata.payload}

      } catch (error) {
        let error_message = ''
        const { response: {data} } = Seatbelt(error)
        const {is_error, message, code} = data
        if(message) error_message = message
        else error_message = error
        this.$bvModal.msgBoxOk(error_message, {
          bodyClass: 'text-break',
          title: 'Error',
        })
      }finally {
        this.loading = false
      }
    },
    async sendFhirRequest(fhir_category, options={}) {
      const dateRange = []
      if(this.options.dateStart) dateRange.push(`ge${this.options.dateStart}`)
      if(this.options.dateEnd) dateRange.push(`le${this.options.dateEnd}`)
      if(dateRange.length>0) options.date = [...dateRange]

      const response = await this.$API.dispatch('fhir/fetchResource', fhir_category, this.mrn, options)
      const {data} = response
      return data
    },
    /**
     * listen for updates form the BaseResource children and
     * update the local fhir_component_validation state
     */
    onValidationChanged(validation) {
      this.fhir_component_validation = validation
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
  validations() {
    return {
      dateStart: {
        isValidDate
      },
      dateEnd: {
        isValidDate
      }
    }
  },
  watch: {
    $route: {
      immediate: true,
      handler(value={}, previous={}) {
        this.updateTitle(value, previous)
        this.fhir_resources = []
        this.options = {}
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