<template>
<div>
  <b-modal ref="my-modal" :title="title" ok-only no-stacking>
    <p class="my-4">{{body}}</p>
  </b-modal>
</div>
</template>

<script>
const initial_data = {
  title: 'Error',
  body: `There was an error`,
  ok_button: 'Ok',
}

export default {
  data() {
    return {...initial_data}
  },
  props: {
    preview: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    resetData() {
      for(let [key, value] of Object.entries(initial_data))
      {
        this[key] = value
      }
    },
    show({title,body}) {
      this.resetData()
      if(title) this.title = title
      if(body) this.body = body
      const modal = this.$refs['my-modal']
      if(!modal) return
      modal.show()
    },
    hide() {
      const modal = this.$refs['my-modal']
      if(!modal) return
      modal.hide()
    },
  },
  watch: {
    
  }
}
</script>

<style>

</style>