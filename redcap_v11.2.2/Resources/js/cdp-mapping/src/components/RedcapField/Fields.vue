<template>
     <div> 
  <b-dropdown class="redcap-field-dropdown scrollable" size="sm" lazy block variant="outline-secondary" v-bind="$attrs" @shown="onDropdownShown">
    <template #button-content>
      <span class="label" v-if="!selected">Select...{{selected}}</span>
      <span class="label" v-else>{{selected}}</span>
    </template>
    <b-dropdown-text v-if="options_length<1">No data</b-dropdown-text>
    <template v-else>
      <b-dropdown-form>
        <b-form-group class="my-0" label="" label-for="filter-fields" @submit.stop.prevent>
          <b-form-input
            id="filter-fields"
            size="sm"
            placeholder="filter fields..."
            v-model="filter"
            @input="onFilterInput"
            ref="filter"
            autocomplete="off"
          ></b-form-input>
      </b-form-group>
      </b-dropdown-form>
      <section class="content">
        <b-dropdown-item-button :active="selected==null" @click="selected=null">-- none --</b-dropdown-item-button>
        <b-dropdown-group v-for="(fields, form_name) in filtered_options" :key="form_name">
          <b-dropdown-header class="bg-light"><span class="font-weight-bold">{{getFormData(form_name).menu}}</span></b-dropdown-header>
          <b-dropdown-item-button v-for="(field, key) in fields || []" :key="key" :title="field.element_label" :active="selected===field.field_name" @click="selected=field.field_name">
            <span>{{field.field_name}}</span>
            <span class="d-block muted small font-italic" v-html="field.element_label" />
          </b-dropdown-item-button>
          <b-dropdown-divider></b-dropdown-divider>
        </b-dropdown-group>
      </section>
    </template>
  </b-dropdown>
     </div>
</template>

<script>
import {debounce} from 'lodash'
import {mapGetters} from 'vuex'

export default {
  data() {
    return {
      filter: '',
      filtered_options: {},
    }
  },
  computed: {
    ...mapGetters({
      getFormData: 'app_settings/getFormData',
    }),
    selected: {
      get() { return this.value },
      set(value) { this.$emit('input', value) },
    },
    options_length() {
      const options = this.options
      return Object.values(options).length
    },
  },
  created() {
    /**
     * run only once when created
     * this is the best way to use debounce in vue
     * @see https://stackoverflow.com/a/49780382
     */
    const initInputDebounce = () => {
      this.onFilterInput = debounce((e) => {
        this.filterOptions()
      }, 400)
    }
    initInputDebounce()
  },
  props: {
    options: {
      type: [Object],
      default: () => []
    },
    /**
     * manipulated by v-model
     */
    value: {
        type: [Number,String],
        default: null
    }
  },
  methods: {
    /**
     * filter all options in all forms
     */
    filterOptions() {
      const text = this.filter
      if(text.trim()==='') {
        this.filtered_options = {...this.options}
        return
      }
      const filtered = {}
      for(let [form_name, options] of Object.entries(this.options)) {
        const form_filtered = this.filterFormOptions(text, options)
        if(form_filtered.length>0) filtered[form_name] = form_filtered
      }
      this.filtered_options = {...filtered}
    },
    /**
     * helper function to filter options at form level
     */
    filterFormOptions(text, options) {
      let filtered = []
      const regexp = new RegExp(text, 'i')
      const searchable_properties = ['element_label', 'field_name']
      options.forEach(option => {
        let match = searchable_properties.some(property => option[property].match(regexp) )
        if(match!==false) {
          filtered.push(option)
        }
      })
      return filtered
    },
    /**
     * check if the current value is in the options
     * if not, set value to empty string
     */
    isValueInOptions() {
      let all_fields = []
      for(let[key, fields] of Object.entries(this.options))
      {
        all_fields = [...all_fields, ...fields]
      }
      const exists = all_fields.some(field => field.field_name==this.value)
      if(!exists) this.selected = null
      return exists
    },
    onDropdownShown() {
      const filter_input = this.$refs.filter
      if(filter_input) filter_input.focus()
    }
  },
  watch: {
    options: {
      immediate: true,
      handler() {
        this.filterOptions()
        this.isValueInOptions()
      }
    }
  }
}
</script>

<style scoped>
#redcap-field-dropdown >>> button.dropdown-toggle {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.label {
    display: block;
    overflow: hidden;
    max-height: 70%;
    text-overflow: ellipsis;
}
.scrollable >>> ul[role="menu"] .content {
  max-height: 50vh;
  overflow: auto;
  max-width: 50vw;;
}
.scrollable >>> ul[role="menu"] button[role="menuitem"] span{
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
}
.scrollable >>> button.dropdown-toggle {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
}
</style>