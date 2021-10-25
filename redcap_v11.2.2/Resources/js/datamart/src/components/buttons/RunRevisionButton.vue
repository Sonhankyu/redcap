<template>

<div>
  <b-dropdown size="" class="m-0" right variant="success" :disabled="disabled">
    <template #button-content>
      <font-awesome-icon :icon="['fas', 'cloud-download-alt']"/><span class="ml-1">Fetch all records <span class="badge badge-light">{{totalMrns}}</span></span>
    </template>

    <b-dropdown-text id="fetch-form">
      <b-form-checkbox class="small" v-model="background"><font-awesome-icon :icon="['fas', 'cog']" /> Fetch in a background process</b-form-checkbox>
      <b-form-checkbox class="small" v-model="send_feedback" :disabled="!background"><font-awesome-icon :icon="['fas', 'envelope']"/> Send me a message when completed</b-form-checkbox>
      <div  class="text-right mt-2">
        <b-button variant="primary" size="sm" @click="onClick">
          <font-awesome-icon :icon="['fas', 'check-circle']"/>
          <span> Confirm</span>
        </b-button>
      </div>
    </b-dropdown-text>
  </b-dropdown>



  <b-modal ref="modal-fetch" id="modal-fetch" title="Fetching Data" size="md"
  no-stacking no-close-on-esc no-close-on-backdrop hide-header-close>
    <RevisionFetchProgress ref="fetch-progress" :progress_total="totalMrns" :progress_value="totalProcessed" :mrn="currentMrn" :mrnsData="results"/>
    <template #modal-footer="{ok}">
      <div class="ml-auto">
        <b-button variant="secondary" size="sm" @click="cancelFetch" v-if="loading">Cancel</b-button>
        <b-button variant="primary" size="sm" @click="ok" v-else>Ok</b-button>
      </div>
    </template>
  </b-modal>




</div>



</template>

<script>
import RevisionFetchProgress from '@/components/RevisionFetchProgress'

const initial_data = {
  promise: null,
  abort: false,
  send_feedback: false, // send feedback to user when process is completed
  background: false, // start process in the background
  totalProcessed: 0,
  results: [], // collect responses from fetched data
}

export default {
  components: { RevisionFetchProgress },
  data: () => ({
    ...initial_data,
    loading: false,
    next_mrn: null,
    currentMrn: null,
    abort: false, // stops the fetch execution
  }),
  computed: {
    title() {
      const totalMrns = this.totalMrns
      const cardinality = (totalMrns===1) ? '' : 's'
      if(totalMrns<1) return 'Data can not be fetched'
      return `Fetch data for ${totalMrns} record${cardinality}`
    },
    revision() {
      return this.$store.getters['revisions/selected']
    },
    totalMrns() {
      return this.revision.getTotalFetchableMrns()
    },
    disabled() {
      try {
        if(!this.revision) return true
        const active = this.$store.getters['revisions/isActive'](this.revision)
        const user = this.$store.state.user.info
        return !active || !user.hasValidToken() || this.totalMrns<1
      } catch (error) {
        return true
      }
    }
  },
  methods: {
    showProgress() {
      const modal = this.$refs['modal-fetch']
      return modal.show()

    },
    resetData() {
      for(let [key, value] of Object.entries(initial_data)) {
        this[key] = value
      }
    },
    /**
     * helper function used for debug
     */
    wait(milliseconds) {
      return new Promise(resolve => setTimeout(resolve, milliseconds));
    },
    cancelFetch() {
      this.abort = true // set the flag that stops the loop
      // the promise returned from runRevision is altered to include a cancel token
      const cancelToken = this.promise.cancelToken
      if(cancelToken) cancelToken.cancel('operation canceled by user')
      this.$refs['modal-fetch'].hide()
    },
    async onClick(event) {
      if(this.background) {
        try {
          let mrn = null
          let options = {background: this.background, send_feedback: this.send_feedback}
          const { metadata: { id: revision_id } } = this.revision
          const response = await this.$API.runRevision(revision_id, mrn, options)
          const {data} = response
          const {message} = data
          this.$bvModal.msgBoxOk(message, {
            title: 'Success',
            size: 'md',
            buttonSize: 'sm',
            headerClass: 'font-weight-bold',
            bodyClass: 'text-break',
            okVariant: 'secondary',
          })
        } catch (error) {
          this.showErrorModal(error)
        }

      }else {
        this.fetchData()
      }
    },
    async fetchData() {
      try {
        this.resetData()
        this.loading = true
        const { metadata: { id: revision_id } } = this.revision
        this.next_mrn = null //reset

        this.showProgress()

        // revision_fetch_progress.progress_total = this.totalMrns // set the total in the progress bar component

        do {
          // debugger
          if(this.abort) break; //break the loop on abort
          let mrn = this.next_mrn
          this.currentMrn = mrn
          let options = {background: this.background, send_feedback: this.send_feedback}
          this.promise = this.$API.runRevision(revision_id, mrn, options)
          const response = await this.promise
          const {data={}} = response
          if(typeof data !== 'object') throw new Error(`Malformed data received: '${String(data)}'`)
          const {metadata={}} = data
          this.next_mrn = metadata.next_mrn
          /**
           * if no MRN is provided we will get no stats,
           * just the next MRN to process (if available).
           * The loop will keep on until a next_mrn is provided
           * by the backend
           */
          if(mrn) {
            this.results.push({mrn, data})
            this.totalProcessed++
          }

        } while (this.next_mrn && !this.abort)


      } catch (error) {
        console.log(error)
        this.cancelFetch()
        this.showErrorModal(error)
        
      }finally {
        this.reloadRevisions()
        this.loading = false
      }
    },
    showErrorModal(error) {
      let defaultErrorMessage = 'Unexpected error'
      let errorMessage = defaultErrorMessage
      if('response' in error) {
        let {data} = error.response
        if('message' in data) errorMessage = data.message
      }else if('toJSON' in error) {
        let {message} = error.toJSON()
        if(message) errorMessage = message
      }
      errorMessage = errorMessage ?? defaultErrorMessage
      this.$bvModal.msgBoxOk(errorMessage, {
        title: 'Error',
        size: 'md',
        buttonSize: 'sm',
        headerClass: 'font-weight-bold',
        bodyClass: 'text-break',
        okVariant: 'secondary',
      })
    },
    /**
     * reload the revisions
     */
    async reloadRevisions() {
      try {
        const response = await this.$API.getRevisions()
        const {data: revisions} = response
        this.$store.dispatch('revisions/setList', revisions)
        this.$store.dispatch('revisions/selectMostRecentRevision')
      } catch (error) {
        this.$swal.fire({
          icon: 'error',
          title: 'error reloading the revisions',
          html: error,
          //timer: 1500
        })
      }
    },
    /**
     * display a prompt before the process is started
     */
    async prompt(callback) {
      const options = {title: 'Please Confirm'}
      const message = `You are about to start the adjudication process.
      Please be aware that your existing data could be overwritten in the process according to your mapping 'preselect' configuration.`
      const response = await this.$bvModal.msgBoxConfirm(message, options)
      if(response && typeof callback==='function') callback()
    },
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  /* button:disabled {
    cursor: help;
  } */
  button {
    transition-property: opacity;
    transition-duration: 150ms;
    transition-timing-function: ease-in-out;
    opacity: 1.0;
  }
  #fetch-form {
    max-width: 80vw;
  }
  #fetch-form >>> label {
    white-space: nowrap;
  }
</style>
