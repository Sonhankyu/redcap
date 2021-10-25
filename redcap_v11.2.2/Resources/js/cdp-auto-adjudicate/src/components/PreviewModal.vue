<template>
<div>
  <b-modal ref="my-modal" :title="title" @ok="$emit('ok')">
    <p class="my-4">{{body}}</p>
    <div v-if="preview">
      <ul>
        <li v-for="(property, index) in preview" :key="index">
          <span><b>{{property.field}}</b>: {{property.value}}</span>
        </li>
      </ul>
    </div>
  </b-modal>
</div>
</template>

<script>
export default {
  data() {
    return {
      title: 'Fetching data from EHR system',
      body: `REDCap has fetched peview data for this record.
            Review the preview data below to ensure that the values match the record you wish to pull from the external source system.
            If they are correct, click the "Fetch" button to save this value and automatically begin fetching data from the source system.
            If the data displayed below is not correct for the value you entered, click "Cancel" to change the value.`,
      ok_button: 'Fetch',
      cancel_button: 'Cancel'
    }
  },
  props: {
    preview: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    show() {
      const modal = this.$refs['my-modal']
      if(!modal) return
      modal.show()
    },
    hide() {
      const modal = this.$refs['my-modal']
      if(!modal) return
      modal.hide()
    },
    test() {
      console.log(this.$refs)
    }
  },
  watch: {
    
  }
}
</script>

<style>

</style>