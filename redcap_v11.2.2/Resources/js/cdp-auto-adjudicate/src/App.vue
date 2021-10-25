<template>
  <div id="cdp-auto-adjudication-app">
    <!-- <b-overlay :show="loading" rounded="sm" :opacity=".85" blur="5px">
    </b-overlay> -->
      <div v-if="error"><b-alert variant="danger">{{error}}</b-alert></div>
      <AutoAdjudicationPanel v-else :loading="loading" :records="records" @adjudication-completed="onAdjudicationCompleted"/>
  </div>
</template>

<script>
import '@/init.js'

import AutoAdjudicationPanel from '@/components/AutoAdjudicationPanel.vue'

export default {
  name: 'App',
  data() {
    return {
      loading: false,
      error: null,
      records: [],
    }
  },
  components: {
    AutoAdjudicationPanel,
  },
  created() {
    // load stats on when created
    this.fetchDdpRecordsDataStats()
  },
  methods: {
    async loadDdpRecordsDataStats(offset=0) {
        const response = await this.$API.dispatch('ddp_records/getDdpRecordsDataStats', offset)
        const {data:{record_data=[], metadata=[]}} = response
        return response
    },
    /**
     * HOC function that wraps async functions
     */
    async fetchDdpRecordsDataStats() {
      try {
        this.loading = true
        this.error = null
        this.records = []
        let load_more = false
        let next_offset = 0
        do {
          const response = await this.loadDdpRecordsDataStats(next_offset)
          const {data:{record_data=[], metadata={}}} = response
          load_more = metadata['load_more']
          next_offset = metadata['next_offset']
          const records_data_sum = [...this.records, ...record_data]
          this.records = records_data_sum
        } while (load_more==true)


      } catch (error) {
        const {response:{data}={}} = error // try to extract data from the error (if XHR)
        this.error = error
      }finally {
        this.loading = false
      }
    },
    onAdjudicationCompleted(results) {
      const adjudication_done_event = new CustomEvent('AutoAdjudicationDone', {detail: {results}})
      this.$el.dispatchEvent(adjudication_done_event)
      this.fetchDdpRecordsDataStats()
    },
  }
}
</script>

<style scoped>
#cdp-auto-adjudication-app {
  max-width: 750px;
}
.source_value {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
  max-width: 40px;
}
/* #app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
} */
</style>
