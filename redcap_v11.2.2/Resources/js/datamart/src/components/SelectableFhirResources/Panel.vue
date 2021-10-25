<template>
<div>
    <b-card header-tag="header" header-bg-variant="info" header-text-variant="white" no-body>
        <template #header>
            <div class="d-flex align-items-center">
                <h4 class="mb-0  mr-5 font-weight-bold text-nowrap">Source Fields List</h4>
                <!-- <b-form-input type="search" class="ml-auto" v-model="textProxy" placeholder="Filter fields" debounce="300"></b-form-input> -->
                <b-form-group
                    label-for="filter-input"
                    label-cols-sm="0"
                    label-align-sm="left"
                    label-size="sm"
                    class="mb-0 ml-auto">
                    <b-input-group size="sm">
                        <b-form-input
                            id="filter-input"
                            v-model="textProxy"
                            type="search"
                            placeholder="Type to Search"
                            debounce="300"
                        ></b-form-input>

                        <b-input-group-append>
                        <b-button :disabled="!textProxy" @click="resetQuery">Clear</b-button>
                        </b-input-group-append>
                    </b-input-group>
                </b-form-group>
            </div>
        </template>
        <div id="selected-list" class="p-2">
            <FieldCategory :container="container" />
        </div>
    </b-card>
</div>
</template>

<script>
import FieldCategory from './FieldCategory'

export default {
    components: { FieldCategory },
    data() {
        return {
            text: ''
        }
    },
    created() {
        this.resetQuery()
    },
    computed: {
        hiddenFields() { return this.$store.state.metadata.hiddenList },
        container() {
            let container = this.$store.getters['metadata/filteredGroups']
            return container
        },
        textProxy: {
            get() { return this.text },
            set(value) {
                this.text = value
                this.applyFilter()
            }
        }
    },
    methods: {
        resetQuery() { this.textProxy = '' },
        applyFilter() { this.$store.dispatch('metadata/setQuery', this.text) }
    },
}
</script>

<style scoped>
#selected-list {
    min-height: 300px;
    max-height: 500px;
    overflow-y: scroll;
}

</style>