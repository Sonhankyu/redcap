<template>
<div>
  <b-modal ref="my-modal" :title="title" no-stacking size="lg"
    @ok="handleOk" @close="handleClose" @cancel="handleCancel" @hidden="handleHidden" >
      <div>
        <slot name="body"></slot>
      </div>
    <template #modal-footer="{ ok, cancel, hide  }">
      <b-button v-if="show_ok" size="sm" variant="primary" @click="ok()">
        OK
      </b-button>
      <b-button v-if="show_cancel" size="sm" variant="secondary" @click="cancel()">
        Cancel
      </b-button>
      <!-- Button with custom close trigger value -->
      <b-button v-if="show_custom" size="sm" variant="outline-secondary" @click="hide('forget')">
        Forget it
      </b-button>
    </template>
  </b-modal>
</div>
</template>

<script>
const initial_data = {
  title: 'Message',
  body: ``,
  show_ok: true,
  show_cancel: false,
  show_custom: false,
  resolve_promise: null,
  reject_promise: null,
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
    async show({title}) {
      this.resetData()
      let promise = new Promise((resolve, reject) => {
          this.resolve_promise = resolve
          this.reject_promise = reject
      })

      if(title) this.title = title
      const modal = this.$refs['my-modal']
      if(!modal) return
      const test = await modal.show()

      return promise
    },
    hide() {
      const modal = this.$refs['my-modal']
      if(!modal) return
      modal.hide()
    },
    /**
     * with these handle methods the modal can be awaited after is shown
     */
    handleOk() {
      if(this.resolve_promise) this.resolve_promise('ok')
    },
    handleClose() {
      if(this.resolve_promise) this.resolve_promise('close')
    },
    handleCancel() {
      if(this.resolve_promise) this.resolve_promise('cancel')
    },
    handleHidden() {
      if(this.resolve_promise) this.resolve_promise('hidden')
    },
  },
}
</script>

<style>

</style>