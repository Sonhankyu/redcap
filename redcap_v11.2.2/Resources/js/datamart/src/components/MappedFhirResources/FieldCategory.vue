<template>
  <div class="field-category m-0 p-0">

      <header @click="toggle" class="d-flex" v-if="container.id!==''" >
        <section class="d-inline mr-2" >
            <font-awesome-icon :icon="['fas', 'chevron-circle-right']" fixed-width v-if="collapsed" />
            <font-awesome-icon :icon="['fas', 'chevron-circle-down']" fixed-width v-else/>
        </section>
        <section>
            <span class="font-weight-bold">{{container.id}}</span>
            <span class="d-block small font-italic text-muted" v-if="container.id">
                <span>{{total}} field{{total===1 ? '' : 's'}}</span>
                <span v-if="totalDisabled>0"> ({{totalDisabled}} field{{totalDisabled===1 ? '' : 's'}} disabled)</span>
            </span>
        </section>
    </header>

    <div class="children" v-if="!collapsed || container.id===''" :class="{'ml-5': container.id!==''}">
        <template v-for="(child, key) in container.children">
            <section :key="key" >
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

export default {
    name: 'FieldCategory',
    data() {
        return {
            collapsed: true,
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
        total() {      
            let fields = this.container.getFields(true)
            return fields.length
        },
        totalDisabled() {      
            let fields = this.container.getFields(true)
            return fields.filter(({disabled}) => disabled).length
        }
    },
    methods: {
        isFhirField(item) { return item instanceof FhirField },
        toggle() { this.collapsed = !this.collapsed },
        /**
         * testing if a class can be updated
         */
        onClick(item) {
            /* let params = {field: item.field+='miao'}
            this.$store.dispatch('metadata/update', {item, params}) */
        },
    },
}
</script>

<style scoped>
header {
    cursor: pointer;
}
</style>