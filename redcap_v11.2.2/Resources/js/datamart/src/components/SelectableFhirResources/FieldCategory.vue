<template>
  <div class="field-category m-0 p-0" v-if="totalVisible>0">
    <header  class="d-flex" v-if="container.id!==''" >
            <!-- <b-form-checkbox v-model="allSelected" :indeterminate="indeterminate" /> -->
            <section class="d-inline mr-2" @click="toggle">
                <font-awesome-icon :icon="['fas', 'chevron-circle-right']" fixed-width v-if="collapsed" />
                <font-awesome-icon :icon="['fas', 'chevron-circle-down']" fixed-width v-else/>
            </section>

            <section class="font-weight-bold" @click="toggle">
                <span>{{container.id}}</span>
                <span class="d-block small font-italic text-muted">
                    <template v-if="query">showing {{totalVisible}} of {{totalOverallFields}} | </template>
                    <template>{{totalOverallSelected}}/{{totalVisible}} field{{totalVisible===1 ? '' : 's'}} selected</template>
                    <template v-if="totalOverallDisabled>0"> ({{totalOverallDisabled}} field{{totalOverallDisabled===1 ? '' : 's'}} disabled)</template>
                </span>
            </section>

            <!-- <b-form-checkbox v-model="allSelected" :indeterminate="indeterminate" /> -->
            <!--
                IMPORTANT NOTE: this button needs to stay outside of the elements with the @click
                because of terrible performances.
                Also a button must be used instead of a b-checkbox for the same reasons
                -->
            <div class="ml-auto text-nowrap">
                <b-button  @click="toggleAll(!allSelected)" size="sm" :variant="allSelected ? 'success' : 'light'">
                    <span v-if="allSelected">deselect all</span>
                    <span v-else >select all</span>
                </b-button>
                <!-- <b-form-checkbox class="ml-auto" :id="`checkbox-${container.id}`" v-model="allSelected" :indeterminate="indeterminate" switch/> -->
            </div>

    </header>


    <div class="children" v-if="!collapsed || container.id===''" :class="{'ml-5': container.id!==''}">
        <template v-for="(child, key) in container.children">
            <section :key="key" v-if="!isFhirField(child) || (isFhirField(child) && !child.hidden)">
                <template v-if="isFhirField(child)">
                    <FieldNode class="py-1" :data="child" />
                </template>
                <template v-else>
                    <FieldCategory class="py-1" :container="child" />
                </template>
            </section>
        </template>
    </div>
  </div>
</template>

<script>
import { FhirField, MetadataContainer } from '@/libraries/metadata'
import FieldNode from './FieldNode'
import { difference, intersection, union, filter, reduce } from 'lodash'

export default {
    name: 'FieldCategory',
    data() {
        return {
            collapsed: true,
            count: 0,
        }
    },
    components: { FieldNode },
    props: {
        container: {
            type: MetadataContainer,
            default: null
        },
    },
    computed: {
        query() { return this.$store.state.metadata.query },
        fields() { return this.container.getFields() },
        overallFields() { return this.container.getFields(true) },
        visibleFields() { return this.fields.filter(({hidden}) => !hidden) },
        overallVisibleFields() { return this.overallFields.filter(({hidden}) => !hidden) },
        overallDisabledFields() { return this.overallFields.filter(({disabled}) => disabled) },
        selected() {
            const selectedFields = this.$store.state.revision.fields
            const fieldNames = this.visibleFields.map(({field})=>field)
            return intersection(fieldNames, selectedFields)
        },
        overallSelected() {
            const selectedFields = this.$store.state.revision.fields
            const fieldNames = this.overallVisibleFields.map(({field})=>field)
            return intersection(fieldNames, selectedFields)
        },

        totalFields() { return this.fields.length},
        totalOverallFields() { return this.overallFields.length},
        totalSelected() { return this.selected.length },
        totalOverallSelected() { return this.overallSelected.length },
        totalVisible() { return this.overallVisibleFields.length },
        totalOverallDisabled() { return this.overallDisabledFields.length },
        // manage group checkboxes
        allSelected: {
            get() {
                let allSelected = this.totalVisible==this.totalOverallSelected
                return allSelected
             },
            set(checked) {
                let selectedFields = this.$store.state.revision.fields
                const fieldNames = this.overallVisibleFields.map(({field})=>field)
                if(checked) selectedFields = union(fieldNames, selectedFields)
                else selectedFields = difference(selectedFields, fieldNames)
                this.$store.dispatch('revision/setFields', selectedFields)
            } // do nothing
        },
        indeterminate: {
            get() {
                if(this.totalSelected==0) return false
                if(this.allSelected) return false
                return true
            },
            set(value) {} // do nothing
        },
    },
    methods: {
        isFhirField(item) { return item instanceof FhirField },
        toggle() { this.collapsed = !this.collapsed },
        toggleAll(checked) {
            let selectedFields = this.$store.state.revision.fields
            const fieldNames = this.overallVisibleFields.map(({field})=>field)
            if(checked) selectedFields = union(fieldNames, selectedFields)
            else selectedFields = difference(selectedFields, fieldNames)
            this.$store.dispatch('revision/setFields', selectedFields)
        },
    },
    watch: {
        query: {
            immediate: false,
            handler(value) {
                if(value==='') this.collapsed = true
            }
        }
    }
}
</script>

<style scoped>
header > *{
    cursor: pointer;
}
.field-category > .children{
    
}
</style>