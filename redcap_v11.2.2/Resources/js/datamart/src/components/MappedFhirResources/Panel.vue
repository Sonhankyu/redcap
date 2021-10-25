<template>
    <b-card header-tag="header" header-bg-variant="info" header-text-variant="white" no-body>
        <template #header>
            <h4 class="mb-0 font-weight-bold">Source Fields List</h4>
        </template>
        <div id="selected-list" class="p-2">
            <FieldCategory :container="container" />
        </div>
    </b-card>
</template>

<script>
import FieldCategory from './FieldCategory'

export default {
    components: { FieldCategory },
    computed: {
        container() {
            const selectedRevision = this.$store.getters['revisions/selected']
            let container = this.$store.getters['revisions/metadata'](selectedRevision)
            return container
        },
    }
}
</script>

<style scoped>
#selected-list {
    min-height: 300px;
    max-height: 500px;
    overflow-y: scroll;
}

</style>