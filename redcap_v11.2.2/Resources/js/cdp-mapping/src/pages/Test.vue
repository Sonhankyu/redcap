<template>
    <div>

        {{mapping}}
        
        {{mappingObjects}}

        <draggable v-model="draggable_items" @input="onInput" v-bind="dragOptions" @start="drag=true" @end="drag=false">
            <transition-group type="transition" :name="!drag ? 'flip-list' : null">
                <div class="draggable-item" v-for="(item) in draggable_items" :key="item.id">{{item.id}} {{item.name}}</div>
            </transition-group>
        </draggable>

        <!-- {{draggable_items}} -->


        <!-- <button class="btn btn-outline-primary" @click="add">add</button>
        <button class="btn btn-outline-primary" @click="remove">remove</button>
        <ul>
            <transition-group name="list" tag="li">
            <span v-for="(index) in Object.keys(items)" :key="index" :set="item=items[index]" class="list-item" :class="`tem-${index}`">
                <span colspan="3">{{item}}{{index}}</span>
                <span></span>
            </span>
            </transition-group>
        </ul>

        <div id="list-demo">
        <button v-on:click="add">Add</button>
        <button v-on:click="remove">Remove</button>
        <transition-group name="list" tag="p">
            <span v-for="item in items" v-bind:key="item" class="list-item">
            {{ item }}
            </span>
        </transition-group>
        </div> -->


  </div>
</template>

<script>

import draggable from 'vuedraggable'
import Vue from 'vue'

Vue.directive('demo', {
  bind: function (el, binding, vnode) {
      el.addEventListener('input', (event) => {
          console.log(event)
    })
    console.log(el, binding, vnode)
    vnode.elm.value='FIRST'
    const component = vnode.context.getConfigProperty(1, 'preselect')
  },

})

export default {
    components: { draggable },
    async created() {
        const initSelect = async () => {
            const {fhir_fields} = this.$store.state.settings
            const cloned_fields = JSON.parse(JSON.stringify(fhir_fields))
            let fields_list = Object.values(cloned_fields) // just get the values as array
            await this.$store.dispatch('fhir_fields_select/setFields', fields_list)
            this.ready = true
        }
    },
    data() {
        return {
            drag: false,
            ready: false,
            items: [1,2,3],
            draggable_items: [
                {id: 1, name:'Francesco'},
                {id: 2, name:'Penny'},
                {id: 3, name:'Betsy'},
            ],
        }
    },
    computed: {
        fhir_field_tree() { return this.$store.state.fhir_fields_tree.fields[0] },
        fhir_field_select() { return this.$store.state.fhir_fields_select.fields[0] },
        fhir_field() { return this.$store.state.settings.fhir_fields },
        mapping() { return this.$store.state.mapping.list },
        mappingObjects() { return this.$store.getters['mapping/models'] },
        dragOptions() {
            return {
                animation: 500,
                group: "description",
                disabled: false,
                ghostClass: "ghost"
            }
        },
    },
    methods: {
        onInput(event) {
            console.log(event)
        },
        randomIndex: function () {
            return Math.floor(Math.random() * this.items.length)
        },
        add: function () {
            let random = this.randomIndex()
            console.log(random)
            this.items.splice(random, 0, Math.max(...this.items)+1)
        },
        remove: function () {
            let random = this.randomIndex()
            console.log(random)
            this.items.splice(random, 1)
        },
    },
}
</script>

<style scope>
.list-item {
  display: inline-block;
  margin-right: 10px;
}
.list-enter-active, .list-leave-active {
  transition: all 1s;
}
.list-enter, .list-leave-to /* .list-leave-active below version 2.1.8 */ {
  opacity: 0;
  transform: translateY(30px);
}

.draggable-item {
    padding: 10px;
    border: 1px solid black;
}



.flip-list-move {
  transition: transform 0.5s;
}
.no-move {
  transition: transform 0s;
}
.ghost {
  opacity: 0.5;
  background: #c8ebfb;
}
.list-group {
  min-height: 20px;
}
.list-group-item {
  cursor: move;
}
.list-group-item i {
  cursor: pointer;
}

</style>