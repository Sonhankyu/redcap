<template>
    <div v-if="hasVisibleChildren" class="main" :style="{'marginLeft': (depth-1)*20+'px'}">        
        <!-- header of the group -->
        <header v-if="name" class="group-head">
            <!-- group title and metadata -->
            <div class="group-name" @click="toggleCollapse">
                <font-awesome-icon icon="angle-right" class="mr-1" :class="{'fa-rotate-90': !collapsed}"/>
                <strong v-if="name">{{name}}</strong>
                <div class="small text-muted">
                    <span v-if="hidden_fields.length>0"> (showing {{visible_fields.length}} of {{fields.length}} field{{fields.length==1 ? '' : 's'}})</span>
                </div>
            </div>

        </header>

        <!-- loop children  -->
        <template v-if="!collapsed">
            <template  v-for="(child, child_key) in data" class="ml-2">
                <Leaf v-if="'field' in child && !child.hidden" :data="child" :key="`cat_${child_key}`" @click.native="onLeafClicked(child)"/>

                <SelectNode v-else-if="!('field' in child)" :name="child_key" :data="child" :depth="depth+1" :key="`cat_${child_key}`"/>
            </template>
        </template>

    </div>
</template>

<script>
import EventBus from './EventBus'
import Leaf from './Leaf'

export default {
    name: 'SelectNode',
    components: {Node,Leaf,},
    data() {
        return {
            is_collapsed: true,
            metadata: {},
            selected: [],
        }
    },
    props: {
        data: {
            type: Object,
            default: ()=>({})
        },
        depth: {
            type: Number,
            default: 0,
        },
        name: {
            type: String,
            default: ''
        },
        value: {
            type: Object,
            default: null,
        },
    },
    computed: {
        /**
         * compute fields from metadata
         */
        fields() {
            const {fields=[]} = this.metadata
            return fields
        },
        /**
         * compute hidden fields form metadata
         */
        hidden_fields() {
            const {hidden_fields=[]} = this.metadata
            return hidden_fields
        },
        /**
         * compute visible fields from metadata
         */
        visible_fields() {
            const {fields=[], hidden_fields=[]} = this.metadata
            const visible_fields = fields.filter( field => hidden_fields.indexOf(field)<0)
            return visible_fields
        },
        hasVisibleChildren() {
            return this.visible_fields.length>0
        },
        collapsed: {
            get() {
                if(this.name.trim()==='') return false
                else return this.is_collapsed
            },
            set(value) {
                this.is_collapsed = value
            }
        },
    },
    methods: {
        onLeafClicked(child) { EventBus.$emit('fieldSelected', child) },
        toggleCollapse() { this.collapsed = !this.collapsed },
        /**
         * parse the data of the node and collect all leaves.
         * leaves have the 'field' property
         */
        parseChildren(data, accumulator={fields:[], hidden_fields:[]}) {
            for(let[key, child] of Object.entries(data)) {
                if(typeof child=='object' && 'field' in child) {
                    accumulator.fields.push(key)
                    if('hidden' in child && child.hidden==true) accumulator.hidden_fields.push(key)
                }else accumulator = this.parseChildren(child, accumulator)
            }
            return accumulator
        },
    },
    watch: {
        /**
         * collect children when the data is updated (i.e. filtered)
         */
        data: {
            immediate: true,
            deep: true,
            handler(value) {
                this.metadata = this.parseChildren(value)
            }
        },
        /**
         * watch the visible fields and manage collapsing based on the filter state
         */
        visible_fields: {
            immediate: true,
            deep: true,
            handler(value, old_value) {
                const filter = this.$store.state.fhir_fields_select.filter
                if(filter.trim()=='') this.is_collapsed = true
                else if(value.length>0) this.is_collapsed = false
            }
        },
    }
}
</script>

<style scoped>
    .wrapper {
        display: flex;
        flex-direction: row;
    }
    .group-head {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
    .main + .main{
        padding-top: 10px;
    }
    .main:not(:last-of-type) {
        border-bottom: solid 1px #cacaca;
    }
    .group-name {
        padding: 10px 0;
        cursor: pointer;
    }
</style>