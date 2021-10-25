<template>
<div class="table-wrapper">
  <b-table :fields="fields" v-bind="{...$attrs, ...$props}" >
    <template #head(select)="data">
      <div class="d-flex">
        <b-form-checkbox v-model="allSelected" :indeterminate="indeterminate" @change="toggleAll">
          <span >{{ data.label }}</span>
        </b-form-checkbox>
      </div>
      <b-button class="w-100 text-nowrap" size="sm" variant="outline-secondary" :disabled="!(allSelected||indeterminate)" @click="showExportPreview">
        <font-awesome-icon class="icon" :icon="['fas', 'file-download']" fixed-width/>
        <span>export</span>
      </b-button>
    </template>

    <template #cell(select)="data">
      <div class="select-wrapper" v-if="isLoincCode(data.item)">
        <b-form-checkbox
          v-if="!isMapped(data.item['coding_code'])"
          :disabled="!data.item['coding_code']"
          v-model="selected_codes"
          :value="data.item['coding_code']"
          switch
          size="lg"
        ></b-form-checkbox>
      </div>
    </template>

    <template #cell(coding_display)="data">
      <span v-html="$attrs.markText(data.value)"></span>
      <template v-if="data.item['coding_text']!=data.value">
        <span class="font-italic small d-block">({{data.item['coding_text']}})</span>
      </template>
    </template>

    <template #cell(coding_code)="data">
      <span v-html="$attrs.markText(data.value)"></span>
      <div v-if="isLoincCode(data.item)" class="small">
        <b-badge v-if="!isMapped(data.value)" variant="warning">not mapped</b-badge>
        <b-badge v-else variant="info">mapped</b-badge>
      </div>
    </template>

    <!-- generic cell template\ -->
    <template #cell()="data">
      <div v-if="isObject(data.value)">
        <Node :payload="data.value" />
      </div>
      <div v-else>
        <span v-html="$attrs.markText(data.value)"></span>
      </div>
    </template>
    
  </b-table>

  <b-alert :show="true">
    Please note that results with multiple coding systems are split in separated rows.
  </b-alert>

  <b-modal ref="export-modal" title="Export codes">
      <div>
        <p class="mb-2">Do you want to export the current selection?</p>
        <b-table class="mb-2" small :items="exportItems" striped bordered sticky-header="100">
          <template #cell(code)="data">
            <b class="text-nowrap">{{ data.value }}</b>
          </template>
        </b-table>
        <b-input v-model.trim="exportFileName" placeholder="enter a name for this selection..." />
      </div>
      <template #modal-footer="{ cancel }">
          <!-- Emulate built in modal footer ok and cancel button actions -->
          <b-button size="sm" variant="secondary" @click="cancel()">
            Cancel
          </b-button>
          <b-button size="sm" variant="success" @click="onExportClicked()" :disabled="!exportFileName">
            <font-awesome-icon :icon="['fas', 'file-export']" fixed-width/>
            <span class="ml-2">Export</span>
          </b-button>
        </template>
  </b-modal>
</div>
</template>

<script>
import Node from '@/components/Payload/Node'
import {downloadBlob, downloadDataURI} from '@/libraries/Utils'
import ResourceTable from './ResourceTable.vue'

/**
 * helper function that flattens an object.
 * specifically, this is being used to flattedn the coding
 * systems of each observation
 */
const flatten = (obj) => {
  const flattened = {}
  Object.keys(obj).forEach((key) => {
    if (typeof obj[key] === 'object' && obj[key] !== null) {
      Object.assign(flattened, flatten(obj[key]))
    } else {
      flattened[key] = obj[key]
    }
  })

  return flattened
}

export default {
  extends: ResourceTable,
  components: {Node},
  data() {
    return {
      exportFileName: 'codes.txt',
      allSelected: false,
      indeterminate: false,
      selected_codes: [],
      custom_fields: [
        {key: 'select', sortable: false,},
        {key: 'coding_display', sortable: true,},
        // {key: 'coding_text', sortable: true,},
        {key: 'coding_code', sortable: true,},
        {key: 'value', sortable: true,},
        {key: 'valueUnit', sortable: true,},
        {key: 'timestamp', sortable: true,},
        {key: 'coding_system', sortable: true,},
        // {key: 'loinc', sortable: true,},
        // {key: 'loinc-codes', sortable: true,},
        // {key: 'valueQuantity', sortable: true,},
        // {key: 'valueBoolean', sortable: true,},
        // {key: 'valueCodeableConcept', sortable: true,},
        // {key: 'valueDateTime', sortable: true,},
        // {key: 'valueInteger', sortable: true,},
        // {key: 'valuePeriod', sortable: true,},
        // {key: 'valueRange', sortable: true,},
        // {key: 'valueRatio', sortable: true,},
        // {key: 'valueSampledData', sortable: true,},
        // {key: 'valueString', sortable: true,},
        // {key: 'valueTime', sortable: true,},
        /* {
          key: 'last_name',
          sortable: true
        },
        {
          key: 'first_name',
          sortable: false
        },
        {
          key: 'age',
          label: 'Person age',
          sortable: true,
          // Variant applies to the whole column, including the header and footer
          variant: 'danger'
        } */
      ],
    }
  },
  methods: {
    isObject(value) {
      return typeof value == 'object'
    },
    isNonArrayObject(value) {
      if(Array.isArray(value)) return false
      return this.isObject(value)
    },
    shouldDisplayCell(value) {
      if(Array.isArray(value)&& value.length<1) return false;
      return true;
    },
    isLoincCode(data) {
      const {coding_system:system} = data
      if(!system) return
      return system.match(/loinc/i)
    },
    isMapped(code) {
      return this.cdpMappingNames.indexOf(code)>0
    },
    toggleAll(checked) {
      this.selected_codes = checked ? Object.keys(this.selectable).slice() : []
    },
    showExportPreview() {
      const modal = this.$refs['export-modal']
      if(modal) modal.show()
    },
    onExportClicked() {
      let text = ''
      for(let {code, label} of this.exportItems) {
        text += `${code}, ${label}\n`
      }
      downloadBlob(text, this.exportFileName)
      const modal = this.$refs['export-modal']
      if(modal) modal.hide()
    },
  },
  computed: {
    /**
     * transform the observation so that the code is flat
     * instead of being a nested object like this {coding: {code,system,display}, text}
     */
    itemsProxy() {
      const items = [...this.items] // ilst of items coming from $attrs, specifically from ResourceTableWrapper
      const flatCodeItems = items.map(item => {
        const {code={}} = item
        for(let [key, value] of Object.entries(flatten(code))) {
          item[`coding_${key}`] = value
        }
        return item
      })
      return flatCodeItems
    },
    allChecked: {
      get() {
        return this.selected_codes.length == Object.keys(this.selectable).length
      },
      set(value) {console.log('allChecked',value)},
    },
    intermediateChecked: {
      get() {
        if(this.selected_codes.length == 0) return false
        if(this.selected_codes.length == Object.keys(this.selectable).length) return false
        return true
      },
      set(value) {console.log('intermediate',value)},
    },
    /**
     * list of LOINC codes that can be selected (along with label)
     */
    selectable() {
      const codes = {}
      this.itemsProxy.forEach(item => {
        if(!this.isLoincCode(item)) return
        const {coding_code:code, coding_display: display} = item
        if(!code || Object.keys(codes).indexOf(code)>=0) return
        const mapping = this.cdpMappingNames
        if(mapping.indexOf(code)>=0) return
        codes[code] = display
      })
      return codes
    },
    /**
     * used to display the selected codes in the export table
     */
    exportItems() {
      const items = []
      const selected_codes = [...this.selected_codes]
      for(let[code, label] of Object.entries(this.selectable)) {
        let entry = {code, label}
        if(selected_codes.indexOf(code)>=0 && items.indexOf(entry)<0) items.push(entry)
      }
      return items
    },
    fields() {
      return this.custom_fields
    },
    cdpMappingNames() {
      const names = this.$store.getters['project/mappingSourceNames']
      return names
    },
  },
  watch: {
    items: {
      immediate: true,
      /**
       * reset the selected codes when the items are updated
       */
      handler() {
        this.selected_codes = []
      }
    },
    selected_codes(newValue) {
      // Handle changes in individual flavour checkboxes
      if (newValue.length === 0) {
        this.indeterminate = false
        this.allSelected = false
      } else if (newValue.length === Object.keys(this.selectable).length) {
        this.indeterminate = false
        this.allSelected = true
      } else {
        this.indeterminate = true
        this.allSelected = false
      }
    }
  }
}
</script>

<style scoped>
.table-wrapper >>> .select-wrapper{
  text-align: center;
  margin: 50% auto;
}
.table-wrapper >>> .b-table-stacked .select-wrapper{
  text-align: left;
  margin: auto;
}
</style>