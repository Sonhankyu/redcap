<template>
  <div>
    <p>this is the rooot</p>
      <!-- <pre>{{groups}}</pre> -->
      {{selected}}
      <input type="text" v-model="filter" />{{filter}}
      <FhirNode :children="groups" @onLeafClicked="onLeafClicked"/>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import FhirNode from './FhirNode'
import json from '@/assets/fhir_fields.json'

/**
 * list of FHIR fields keys that must be excluded
 */
const ignore_fields = ['id']

export default {
  data() {
    return {
      filter: '',
      filtered_fields: [],
      selected: null,
    }
  },
  components: { FhirNode },
  computed: {
    ...mapState({
        fields: state => state.fhir_fields_select.fields,
        mapping: state => state.mapping.list,
    }),
    groups() {
        const fields = [...this.filtered_fields]
        const filter = this.filter
        console.log(filter)
        let groupedFields = {}
        for(let field of fields) {
          let keep = this.applyFilter(field, filter)
          if(!keep) continue
          let category = field.category || ''
          let subcategory = field.subcategory || ''
          let field_name = field.field || ''

          if(typeof groupedFields[category]==='undefined') {
              groupedFields[category] = {}
          }
          if(subcategory.trim()!='') {
              if(typeof groupedFields[category][subcategory]==='undefined') groupedFields[category][subcategory] = {}
              groupedFields[category][subcategory][field_name] = field
          }else groupedFields[category][field_name] = field
        }
        return groupedFields
    },
  },
  methods: {
    onLeafClicked(data) {
      this.selected = data.field
      this.applyFilter()
      console.log(data)
    },
    applyFilter(field, filter) {
      let filter_regexp = new RegExp(`(${filter})`,'ig')
      
      if(ignore_fields.indexOf(field.field)>=0) return false
      field.selected = field.field==this.selected
      if(this.selected && field==this.selected) console.log(this.selected)

      // list of properties to check
      let properties_to_check = [
          field.field,
          field.category,
          field.subcategory,
          field.label,
          field.description,
      ]
      let matches = properties_to_check.some(property => property.match(filter_regexp))

      if(!matches) {
          field.hidden = true
      }else {
          field.hidden = false
      }

      return field
    },
  },
  watch: {
    fields: {
      immediate: true,
      handler(fields) {
        this.filtered_fields = [...fields]
      },
    }
  }
}
</script>

<style>

</style>