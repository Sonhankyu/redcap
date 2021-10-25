<template>
    <div class="main">
        <b-dropdown class="fhir-dropdown" :text="dropDownText" ref="dropdown"
        block lazy
        variant="outline-secondary" size="sm"
        menu-class="w-100"
        @shown="onDropdownShown">
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
            <b-dropdown-text >
                <SelectNode :data="groups" id="nodes" />
            </b-dropdown-text>
        </b-dropdown>
    </div>
</template>

<script>
import {debounce} from 'lodash'
import { mapState } from 'vuex'
import SelectNode from './SelectNode'
import EventBus from './EventBus'

export default {
    components: {SelectNode,},
    data() {
        return {
            filter: '',
            filtered_fields: [],
            groups: {},
        }
    },
    props: {
        value: {
            type: String,
            default: null,
        }
    },
    /**
     * listen for filter updates
     */
    created() {
        EventBus.$on('fieldSelected', this.onFieldSelected)
        /**
         * run only once when created
         * this is the best way to use debounce in vue
         * @see https://stackoverflow.com/a/49780382
         */
        const initInputDebounce = () => {
            this.onFilterInput = debounce((e) => {
                this.setGroups()
            }, 400)
        }
        initInputDebounce()
    },
    destroyed() { EventBus.$off('fieldSelected', this.onFieldSelected) },
    computed: {
        ...mapState({
            fields: state => state.fhir_fields_select.fields,
            mapping: state => state.mapping.list,
        }),
        dropDownText() {
            if(!this.fhir_field) return 'Select...'
            const {field, description} = this.fhir_field
            return `${field} (${description})`
        },
        fhir_field() {
            if(!this.selected) return
            const fhir_field = this.$store.getters['app_settings/getFhirField'](this.selected)
            return fhir_field
        },
        selected: {
            get() { return this.value },
            set(value) { this.$emit('input', value) },
        },
    },
    methods: {
        onDropdownShown() {
            const filter_input = this.$refs.filter
            if(filter_input) filter_input.focus()
        },
        onFieldSelected(child) {
            this.selected = child.field
            this.$refs.dropdown.hide()
        },
        getSkipFields() {
            const fhir_id_key = 'id'
            const mapping = [...this.mapping]
            const skip_fields = []
            const fhir_id_is_set = mapping.some(item => item.fhir_field_name===fhir_id_key)
            if(fhir_id_is_set) skip_fields.push(fhir_id_key)
            return skip_fields
        },
        setGroups() {
            const fields = [...this.fields]
            let groupedFields = {}
            for(let field of fields) {
                field = this.applyFilter(this.filter, field)
                if(!field) continue
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
            this.groups = {...groupedFields}
        },
        applyFilter(filter, field) {
            let filter_regexp = new RegExp(`(${filter})`,'ig')

            // list of properties to check
            let properties_to_check = [
                field.field,
                field.category,
                field.subcategory,
                field.label,
                field.description,
            ]
            let matches = properties_to_check.some(property => {
                if(typeof property!=='string') return false
                return property.match(filter_regexp)
            })

            if(!matches) {
                field.hidden = true
            }else {
                field.hidden = false
            }

            return field
        },
        // async applyFilter() { await this.$store.dispatch('fhir_fields_select/applyFilter') },
    },
    watch: {
        fields: {
            immediate: true,
            handler() {
                this.setGroups()
            }
        }
    }
}
</script>

<style scoped>
.fhir-dropdown >>> button.dropdown-toggle {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#nodes {
    max-height: 300px;
    font-size: 14px;
    overflow-y: scroll;
    text-align: left;
}
.dropdown-menu-ref {
    max-height: 300px;
    overflow-y: auto;
}
</style>