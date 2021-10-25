<template>
<div>
  <b-alert :show="payload!==null" variant="light">
    <h6>Raw response</h6>
    <div>
        <b-button @click="show=!show" size="sm" variant="secondary">
          <font-awesome-icon v-if="show" class="icon" :icon="['fas', 'chevron-down']" fixed-width/>
          <font-awesome-icon v-else class="icon" :icon="['fas', 'chevron-right']" fixed-width/>
          <span class="ml-2">Show</span>
        </b-button>
        <b-button class="ml-2" @click="onDownloadClicked" size="sm" variant="primary">
          <font-awesome-icon class="icon" :icon="['fas', 'download']" fixed-width/>
          <span class="ml-2">Download</span>
        </b-button>
    </div>
    <Node class="raw-response" :payload="payload" v-show="show"/>
  </b-alert>

  <b-modal ref="save-modal" title="Download response">
    <div>
      <b-input v-model.trim="filename" placeholder="enter a name..." autofocus @keyup.enter="onSaveOkClicked"/>
    </div>
    <template #modal-footer="{ cancel }">
      <!-- Emulate built in modal footer ok and cancel button actions -->
      <b-button size="sm" variant="secondary" @click="cancel()">
        Cancel
      </b-button>
      <b-button size="sm" variant="success" @click="onSaveOkClicked" :disabled="!filename">
        <font-awesome-icon :icon="['fas', 'save']" fixed-width/>
        <span class="ml-2">Save</span>
      </b-button>
    </template>
  </b-modal>
</div>
</template>

<script>
import Node from '@/components/Payload/Node'

export default {
  components: { Node },
  data() {
    return {
      filename: '',
      show: false,
    }
  },
  props: {
    payload: {
      type: [Object, Array],
      default: () => ({})
    }
  },
  methods: {
    onDownloadClicked() {
      const save_modal = this.$refs['save-modal']
      if(!save_modal) return
      this.filename = ''
      save_modal.show()
    },
    onSaveOkClicked() {
      if(this.filename=='') return
      this.download(JSON.stringify(this.payload, null, 2), this.filename)
      const save_modal = this.$refs['save-modal']
      if(!save_modal) return
      save_modal.hide()
    },
    download(text, filename='export.txt') {
      var element = document.createElement('a');
      element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
      element.setAttribute('download', filename);

      element.style.display = 'none';
      document.body.appendChild(element);

      element.click();

      document.body.removeChild(element);
    },
  }
}
</script>

<style scoped>
.raw-response {
  word-break: break-word;
}

</style>