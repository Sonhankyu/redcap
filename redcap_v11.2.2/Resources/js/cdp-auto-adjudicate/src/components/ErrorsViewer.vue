<template>
<div>
  <b-table class="small" striped hover :items="errors_proxy" bordered small :perPage="perPage" :currentPage="currentPage">
    <template #cell(record_id)="data">
      <span v-if="data.item.record_id===empty_value"></span>
      <a v-else :href="getRecordLink(data.item.record_id)" target="_blank">{{data.item.record_id}}</a>
    </template>
  </b-table>
  <div class="d-flex justify-content-between">
    <b-pagination
        v-model="currentPage"
        :total-rows="errors.length"
        :per-page="perPage"
        aria-controls="my-table"
        size="sm"
        :disabled="errors.length<=perPage"
      ></b-pagination>
      <slot name="footer"></slot>
  </div>
</div>
</template>

<script>

const empty_value = "\u001E"

export default {
  data() {
    return {
      empty_value: empty_value,
      currentPage: 1,
    }
  },
  computed: {
    /**
     * proxy to always display the same amount of rows on each page
     */
    errors_proxy() {
      const errors = [...this.errors]
      const remainder = this.perPage-(errors.length%this.perPage)

      let placeholder = {'no errors': empty_value}
      if(errors.length>0) {
        placeholder = {}
        let first_error = errors[0]
        for(let key of Object.keys(first_error)) {
          placeholder[key] = empty_value
        }
      }

      for(let i=0; i<remainder; i++) errors.push(placeholder)
      return errors
    },
  },
  props: {
    errors: {
      type: Array,
      default: () => []
    },
    perPage: {
      type: Number,
      default: 5
    }
  },
  methods: {
    getRecordLink(record_id) {
      if(record_id===empty_value || typeof record_id==='undefined') return '#'
      const base_url = window.app_path_webroot || '/'
      const pid = window.pid || 0
      const url = `${base_url}DataEntry/record_home.php?pid=${pid}&id=${record_id}`
      return url
    },
  }
}
</script>

<style>

</style>