<template>
    <div>
        <template v-if="!depth">
            <Nested v-for="(item, index) in data" :key="index" :name="index" :depth="1" :data="item"/>
        </template>

        <div v-else :style="{'margin-left': `${(depth-1)*20}px`}">
            <template v-if="hasVisibleChildren">
                <!-- print header -->
                <header v-if="name.trim()!==''">{{name}}</header>
                
                <!-- loop children -->
                <div v-for="(item, index) in data" :key="index">
                    <!-- check if leaf -->
                    <h6 v-if="item.field" v-show="!item.hidden">{{item.field}}</h6>

                    <!-- recursively print children -->
                    <template v-else>
                        <Nested :data="item" :name="index" :depth="depth+1"/>
                    </template>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Nested',
    data() {
        return {metadata:{}}
    },
    props: {
        name: {},
        data: {},
        depth: {},
    },
    computed: {
        hasVisibleChildren() {
            const {fields, hidden_fields} = this.metadata
            return fields.length!==hidden_fields.length
        }
    },
    methods: {
        parseChildren(data, accumulator={fields:[],hidden_fields:[]}) {
            for(let[key, child] of Object.entries(data)) {
                if('field' in child) {
                    accumulator.fields.push(key)
                    if('hidden' in child && child.hidden==true) accumulator.hidden_fields.push(key)
                }
                else accumulator = this.parseChildren(child, accumulator)
            }
            return accumulator
        }
    },
    watch: {
        data: {
            immediate: true,
            deep: true,
            handler(value) {
                this.metadata = this.parseChildren(value)
            }
        }
    }
}
</script>

<style>

</style>