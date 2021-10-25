<template>
  <div>
    <b-card>
      <div class="d-flex align-items-center mb-2">
        <span class="fhir_base_url d-block font-italic small mr-2">{{fhir_base_url}}</span>

          <b-form-input v-model.trim="relative_url" size="sm"
            name="relative_url"
            placeholder="enter URL..."/>
          <b-form-select v-model="method" :options="methods" size="sm" class="ml-2"></b-form-select>

      </div>

      <div class="d-flex justify-content-between">
        <b-button @click="onSendRequestClicked()" size="sm" variant="outline-primary" :disabled="isSendDisabled">
          <font-awesome-icon v-if="loading" :icon="['fas', 'spinner']" spin fixed-width />
          <font-awesome-icon v-else :icon="['fas', 'cloud-download-alt']" fixed-width />
          <span class="ml-2">Send request</span>
        </b-button>

        <div class="d-flex">
          <CustomPresetStorage class="mr-2" :preset="preset" @restore="onRestore"/>

          <b-button @click="onAddParameterClicked" size="sm" variant="outline-success">
            <font-awesome-icon :icon="['fas', 'plus-circle']" fixed-width/>
            <span class="ml-2">Add a new parameter</span>
          </b-button>
        </div>

      </div>

      <div class="my-2">
        <div class="param-row" v-for="(param, index) in params" :key="param.id">
          <!-- <span class="small font-italic text-muted">{{param.id}}</span> -->
          <div class="d-flex align-items-center">
            <b-form-input :name="`key-${index}`" v-model="param.key" placeholder="key"/>
            <b-form-input class="mx-2" :name="`value-${index}`" v-model="param.value" placeholder="value"/>
            <div class="d-flex align-items-center">
              <b-form-checkbox class="mr-2" v-model="param.enabled" switch variant="success"/>
              <b-button @click="onDeleteParameterClicked(index)" size="sm" variant="outline-light">
                <font-awesome-icon :icon="['fas', 'trash']" class="text-danger" fixed-width/>
              </b-button>
            </div>
          </div>
        </div>
      </div>
    </b-card>

  </div>
</template>

<script>
import { v4 as uuidv4 } from 'uuid'
import Seatbelt from '@/libraries/Seatbelt'
import {methods} from '@/models/Request'
import BaseResource from '@/components/endpoints/forms/BaseResourceForm'
import CustomPresetStorage from '@/components/CustomPresetStorage'

/**
 * class to create a Param
 */
class Param {
  constructor(key='', value='', enabled=true) {
      this.id = uuidv4()
      this.key = key
      this.value = value
      this.enabled = enabled
  }
}

export default {
  extends: BaseResource,
  components: {CustomPresetStorage},
  data() {
    return {
      loading: false,
      method: methods.GET,
      methods: Object.values(methods),
      params: [],
      relative_url: '',
    }
  },
  computed: {
    fhir_base_url() {
      const {fhir_base_url} = this.$store.state.app_settings
      return fhir_base_url
    },
    // get payload that needs to be persisted in localStorage
    preset() {
      if(this.relative_url=='') return
      const preset = {
        relative_url: this.relative_url,
        params: this.params,
        method: this.method,
      }
      return preset
    },
    isSendDisabled() {
      return this.loading || this.relative_url==''
    }
  },
  methods: {
    onAddParameterClicked() {
      const param = new Param()
      this.params.splice(this.params.length, 0, param)
    },
    onDeleteParameterClicked(index) {
      this.params.splice(index, 1)
    },
    onRestore(preset) {
      const {params=[], relative_url='', method} = preset
      this.method = method
      this.relative_url = relative_url
      const restored_params = []
      params.forEach(item => {
        let param = new Param(item.key, item.value, item.enabled)
        restored_params.push(param)
      })
      this.params = restored_params
    },
    async onSendRequestClicked() {
      this.loading = true
      const options = {}
      const params = this.params.filter(param => param.enabled)

      params.forEach(param => {
        if(param.key in options && !Array.isArray(options[param.key])) {
          // transform to array if there is a duplicate key (only do this once)
          options[param.key] = [options[param.key]]
        }
        // push or set
        if(Array.isArray(options[param.key])) {
          options[param.key].push(param.value)
        }else {
          options[param.key] = param.value
        }
      })
      try {
        const response = await this.$API.dispatch('fhir/customRequest', this.relative_url, options)
        const {data} = response
        this.$emit('data-received', data)
      } catch (error) {
        let error_message = ''
        const { response: {data} } = Seatbelt(error)
        const {is_error, message, code} = data
        if(message) error_message = message
        else error_message = error
        this.$bvModal.msgBoxOk(error_message, {
          title: 'Error',
          bodyClass: 'text-break',
        })
        this.$emit('error', error_message)
      } finally {
        this.loading = false
      }
    },

  }
}
</script>

<style scoped>
.fhir_base_url {
  white-space: nowrap;
}
.param-row + .param-row {
  margin-top: 5px;
}
</style>