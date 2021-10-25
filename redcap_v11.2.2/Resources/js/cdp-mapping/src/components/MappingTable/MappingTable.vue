<template>
    <div>

        <table ref="table" class="table table-bordered table-hover">
            <thead class="header thead-light">
                <tr>
                    <th colspan="6">
                        <section class="d-flex flex-row justify-content-space-between align-items-center">
                            <span class="mr-auto">{{translations['mapping_table_title']}}</span>
                            <slot name="head"></slot>
                        </section>
                    </th>
                </tr>

                <tr class="col-names" v-show="mapping.length>0">
                    <th>
                        <span :title="fhirSourceName">{{translations['mapping_table_header_external_source']}}</span>
                        <!-- <span class="small">({{fhirSourceName}})</span> -->
                    </th>
                    <th>
                        <span :title="translations['mapping_table_header_event']">{{translations['mapping_table_header_event']}}</span>
                    </th>
                    <th>
                        <span :title="translations['mapping_table_header_field']">{{translations['mapping_table_header_field']}}</span>
                    </th>
                    <th>
                        <span :title="translations['mapping_table_header_date']">{{translations['mapping_table_header_date']}}</span>
                    </th>
                    <th>
                        <span :title="translations['mapping_table_header_preselect_strategy']">{{translations['mapping_table_header_preselect_strategy']}}</span>
                    </th>
                    <th>
                        <span :title="translations['mapping_table_header_actions']">{{translations['mapping_table_header_actions']}}</span>
                    </th>
                </tr>
            </thead>

            <transition-group tag="tbody" name="list" >
                <template v-for="(item, index) in mapping">

                    <!-- list of mapping items -->
                    <tr :key="item.id"
                        :class="[getStatusClass(item), (item===selected_mapping)?'selected':'']"
                        :id="`row-${item.id}`"
                        v-show="item.status!=='deleted'"
                        > <!-- do not show if the category is not available (i.e. email fields disabled) -->

                        <!-- FHIR fields -->
                        <td>
                            <span v-if="item.disabled" class="text-danger" v-b-tooltip.hover :title="`${item.disabled_reason}`">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" fixed-width/>
                            </span>
                            <FhirFieldLabel
                                :class="{disabled: item.disabled}"
                                :identifier="item.is_record_identifier" :name="item.fhir_field_name"
                                :label="item.fhir_label" :category="item.category" />
                        </td>

                        <!-- REDCap fields -->
                        <td>
                            <span :title="`Event ID: ${item.event_id}`">{{getEventName(item.event_id)}}</span>
                        </td>
                        <td>
                            <FieldLabel :title="`Field Name: ${item.field_name}`" :field_name="item.field_name" />
                        </td>

                        <!-- temporal fields -->
                        <td>
                            <FieldLabel :title="`Temporal Field Name: ${item.temporal_field}`" :field_name="item.temporal_field" />
                        </td>
                        <td>
                            <template v-if="item.is_temporal">
                                <PreselectLabel :value="item.preselect" />
                            </template>
                        </td>

                        <!-- buttons -->
                        <td class="actions">
                            
                            <div class=" w-100 d-inline-flex flex-row align-items-center justify-content-end">
                                <b-button :id="`button-edit-${item.id}`" variant="outline-secondary" size="sm" @click="onEditClicked(item, index)" ><font-awesome-icon  :icon="['fas', 'edit']" class="text-primary"/></b-button>
                                <b-tooltip :target="`button-edit-${item.id}`" placement="bottom">Edit</b-tooltip>

                                <template v-if="!item.is_record_identifier">
                                    <b-button :id="`button-copy-${item.id}`" :title="translations['ws_112']" variant="outline-secondary" size="sm" @click="onCopyClicked(item)"><font-awesome-icon :icon="['fas', 'copy']"/></b-button>
                                    <b-tooltip :target="`button-copy-${item.id}`" placement="bottom">Copy</b-tooltip>
                                </template>
                                <template>
                                    <b-button :id="`button-delete-${item.id}`" :title="translations['ws_115']" variant="outline-secondary" size="sm" @click="onDeleteClicked(item)"><font-awesome-icon :icon="['fas', 'times']" class="text-danger"/></b-button>
                                    <b-tooltip :target="`button-delete-${item.id}`" placement="bottom">Delete</b-tooltip>
                                </template>

                            </div>
                            <!-- mapping errors -->
                            <div v-if="item.has_errors" class="alert alert-warning mt-2">
                                <span class="small"><font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="text-warning"/> Revision needed</span>
                                <b-button size="sm" v-b-modal="`validation-errors-modal-${item.id}`">details...</b-button>
                                <b-modal :id="`validation-errors-modal-${item.id}`" title="Validation errors" ok-only>
                                    <ul>
                                        <li v-for="(error, error_index) in item.validation_errors" :key="error_index"><span>{{error}}</span></li>
                                    </ul>
                                </b-modal>
                            </div>
                            <!-- duplicate warning -->
                            <div class="duplicate-warning" v-if="mapping_duplicates.indexOf(item.id)>-1">
                                <span class="small text-muted"><font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="text-warning"/> duplicate</span>
                            </div>
                            <!-- extra identifier record -->
                            <div class="duplicate-warning" v-if="mapping_record_identifiers.indexOf(item.id)>0">
                                <span class="small text-muted"><font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="text-warning"/> record identifier already set</span>
                            </div>
                        </td>
                    </tr>
                </template>
            </transition-group>
            
            <!-- footer -->
            <tfoot class="thead-light">

                <tr>
                    <th scope="row" colspan="6">
                        <slot name="foot"></slot>
                        <button type="button" class="btn btn-success btn-sm" @click="onNewItemClicked" >
                            <font-awesome-icon icon="plus-circle"/><span> {{translations['find_more_sources_fields_to_map']}}</span>
                        </button>
                    </th>
                </tr>
            </tfoot>

        </table>

        <b-modal title="Edit" v-model="show_edit" hide-footer>
            <MappingForm :mapping="getMappingConfig(selected_mapping)">
                <template v-slot:footer="{config, validation}" >
                    <div class="d-flex justify-content-end">
                        <b-button class="mr-2" size="sm" variant="secondary" @click="show_edit=false">Cancel</b-button>
                        <b-button size="sm" variant="success" @click="update(selected_mapping, config)" :disabled="validation.$invalid">OK</b-button>
                    </div>
                </template>
            </MappingForm>
        </b-modal>

        <b-modal title="Create" v-model="show_create" hide-footer>
            <MappingForm :mapping="new_mapping">
                <template v-slot:footer="{config, validation}" >
                    <div class="d-flex justify-content-end">
                        <b-button class="mr-2" size="sm" variant="secondary" @click="show_create=false">Cancel</b-button>
                        <b-button size="sm" variant="success" @click="create(config)" :disabled="validation.$invalid">OK</b-button>
                    </div>
                </template>
            </MappingForm>
        </b-modal>

    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import FieldLabel from '@/components/MappingTable/FieldLabel'
import FhirFieldLabel from '@/components/MappingTable/FhirFieldLabel'
import PreselectLabel from '@/components/MappingTable/PreselectLabel'
import MappingForm from '@/components/MappingForm'

import {Mapping} from '@/models'

export default {
    components: {
        FieldLabel,
        FhirFieldLabel,
        PreselectLabel,
        MappingForm,
    },
    data() {
        return {
            sort_by: null,
            modal_component: null,
            mapping_proxy: [],
            selected_mapping: null,
            show_edit: false,
            show_create: false,
            new_mapping: null,
        }
    },
    computed: {
        ...mapState({
            translations: state => state.app_settings.translations,
            fhirSourceName: state => state.app_settings.fhir_source_name,
            project: state => state.app_settings.project,
            project_temporal_fields: state => state.app_settings.project_temporal_fields,
            fhir_fields: state => state.app_settings.fhir_fields,
            mapping: state => state.mapping.list,
        }),
        ...mapGetters({
            groupedTemporalFields: 'app_settings/groupedProjectTemporalFields',
            mapping_duplicates: 'mapping/duplicates',
            mapping_record_identifiers: 'mapping/recordIdentifiers',
        }),
        arms() { return this.project.arms },
        events() { return this.project.events },
    },
    methods: {
        getEventName(event_id) {
            const event_data = this.$store.getters['app_settings/getEventData'](event_id)
            if(typeof event_data==='object' && 'name_ext' in event_data) return event_data.name_ext
            return ''
        },
        onNewItemClicked() {
            const new_mapping = {}
            const event_ids = Object.keys(this.events)
            // set automatically the event id only one is available
            if(event_ids.length===1) {
                new_mapping.event_id = event_ids[0]
            }
            this.new_mapping = {...new_mapping}
            this.show_create = true
        },
        /**
         * set a config property for a specific row
         */
        async updateMapping(item, params) {
            await this.$store.dispatch('mapping/update', {item, params})
            // let element = document.querySelector(`#row-${item.id}`)
            // wait for animation
            // setTimeout(() => this.scrollToElement(element), 1200 )
        },
        scrollToElement(element) {
            if(!element) return
            const rect = element.getBoundingClientRect()
            let scroll_position = window.scrollY
            window.scrollTo({top:scroll_position+rect.top, behavior: 'smooth'})
        },
        /**
         * delete the row if new
         * mark as deleted if existing
         */
        onDeleteClicked(item) { this.$store.dispatch('mapping/deleteRow', item) },
        /**
         * duplicate the row
         */
        onCopyClicked(item) { this.$store.dispatch('mapping/duplicateRow', item) },
        onEditClicked(item, index) {
            this.selected_mapping = item
            this.show_edit = true
        },
        update(item, config) {
            this.show_edit = false
            this.updateMapping(item, config)
        },
        async create(config) {
            const mapping = await this.$store.dispatch('mapping/addRow', config)
            this.show_create = false
        },
        /**
         * get the status class for the row
         */
        getStatusClass(mapping) {
            const {status=''} = mapping
            let class_name = 'status-'
            switch (status) {
                case 'new':
                    class_name += 'new'
                    break
                default:
                    class_name += 'normal'
                    break
            }
            if(mapping.dirty) class_name += ' dirty'
            return class_name
        },
        async sortBy(rule_name) {
            this.sort_by = await this.$store.dispatch('mapping/sortBy', rule_name)
        },
        getMappingConfig(mapping) {
            if(mapping instanceof Mapping) return mapping.config
            return {}
        }
    },
}
</script>

<style scoped>
/* .table tbody {
    display: block;
    max-height: 400px;
    overflow: auto;
}
.table thead,
.table tfoot,
.table tbody > tr {
    display: table;
    table-layout: fixed;
    width: 100%;
} */
.actions {
    vertical-align: middle;
}
.actions button + button {
    margin-left: 4px;
}

.events-fields {
    display: flex;
    justify-content: center;
    flex-direction: column;
}


span[title] {
    cursor: help;
}


.category {
    background-color: rgba(0,0,0,.05);
    font-weight: bold;
    color: darkred;
}
.selected {
    background-color: rgba(0, 123, 252, 0.2);
}
tr td {
    position: relative;
}
tr.status-new {
    background-color: rgba(200,255,200,0.5);
}
tr.status-deleted td:not(.actions) {
    pointer-events: none;
    opacity: 0.5;
    text-decoration: line-through;
}
/* dirty rows: change background and add a graphical sign */
tr.dirty {
    background-color: rgba(255,255,200,0.5);
}
/* add visual indicator based on status */
tr.dirty > td:first-child::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 2px;
    background-color: red;
}
tr.status-new > td:first-child::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 2px;
    background-color: green;
}

/* show menu on mouseover */
/* tbody tr td.actions {
    display: none;
}
tbody tr:hover td.actions {
    display: table-cell;
}
tbody tr td:nth-last-child(2) {
    display: table-cell;
    min-width: 140px;
}
tbody tr:hover td:nth-last-child(2) {
    display: none;
} */

/* animations */
.list-enter-active, .list-leave-active {
  transition: all 500ms;
}
.list-move {
  transition: transform 1s;
}
.list-enter-active {
  background-color: rgba(200,255,200,0.5);
}
.list-enter-to {
    background-color: rgba(255,255,255,0.0);
}
.list-leave-active {
    background-color: rgba(255,200,200,1);
}

.list-enter, .list-leave-to /* .list-leave-active below version 2.1.8 */ {
  opacity: 0;
  /* transform: translateY(30px); */
}
</style>