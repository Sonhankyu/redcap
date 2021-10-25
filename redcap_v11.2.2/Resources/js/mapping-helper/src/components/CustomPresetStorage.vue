<template>
  <div>
    <StorageInput @restore="onRestore" @save="startSave" :save_disabled="!preset" save_text="Save preset" >
      <template v-slot="{addItem}">
        <b-modal ref="save-modal" title="Save configuration">
            <div>
              <p class="my-4">Do you want to save the current configuration?</p>
              <b-input v-model.trim="preset_name" placeholder="enter a name for this preset..." />
            </div>
            <template #modal-footer="{ cancel }">
                <!-- Emulate built in modal footer ok and cancel button actions -->
                <b-button size="sm" variant="secondary" @click="cancel()">
                  Cancel
                </b-button>
                <b-button size="sm" variant="success" @click="onSaveOkClicked(addItem)" :disabled="!preset_name">
                  <font-awesome-icon :icon="['fas', 'save']" fixed-width/>
                  <span class="ml-2">Save</span>
                </b-button>
              </template>
        </b-modal>
      </template>
    </StorageInput>
  </div>
</template>

<script>
import StorageInput from '@/components/StorageInput'

export default {
  components: {StorageInput},
  data() {
    return {
      preset_name: '',
    }
  },
  props: {
    preset: {
      type: Object,
      default: null
    },
    save_disabled: {
      type: Boolean,
      default: false,
    }
  },
  methods: {
    startSave() {
      this.preset_name = '' // reset the input field
      const save_modal = this.$refs['save-modal']
      if(!save_modal) return
      save_modal.show()
    },
    onRestore(item) {
      this.$emit('restore', item)
    },
    onSaveOkClicked(save_callable) {
      if(typeof save_callable ==='function') save_callable(this.preset_name, this.preset)
      const save_modal = this.$refs['save-modal']
      if(!save_modal) return
      save_modal.hide()
    },
  }
}
</script>

<style>

</style>