<template>
<div :class="{root: depth===0}" class="node">

  <div @click="open=!open" v-if="name">
    <font-awesome-icon v-if="open" class="icon" :icon="['fas', 'chevron-down']" />
    <font-awesome-icon v-else class="icon" :icon="['fas', 'chevron-right']" />
    <span class="ml-2 text-success font-weight-bold">{{name}}</span>
  </div>

  <div class="content" v-if="depth===0 || open">
    <template v-for="(data, name) in children">
      <template v-if="'field' in data && !data.hidden">
        <!-- the native click is received by the root element -->
        <FhirLeaf :class="{selected: selected_leaf==data}" class="leaf" :data="data" :key="`leaf_${name}`" @click.native="onLeafClicked(data)"/>
      </template>
      <!-- onLeafClicked is bubbled to the top of the hierarchy -->
      <FhirNode v-else :children="data" :depth="depth+1" :key="`node_${name}`" :name="name" @onLeafClicked="onLeafClicked">

      </FhirNode>


    </template>
  </div>
    <!-- <FhirLeaf v-if="'field' in data" :data="data"/>
    <FhirNode v-else :depth="depth+1" :class="{root: depth===0}" :children="data">
      <p>this has content</p>
    </FhirNode> -->
</div>
</template>

<script>
import FhirLeaf from './FhirLeaf'

export default {
  components: { FhirLeaf },
  name: 'FhirNode',
  data() {
    return {
      open: false,
      selected_leaf: null,
    }
  },
  props: {
    depth: {
      type: Number,
      default: 0
    },
    name: {
      type: String,
      default: ''
    },
    children: {
      type: Object,
      default: null,
    },
  },
  methods: {
    onLeafClicked(data) {
      this.selected_leaf = data
      this.$emit('onLeafClicked', data)
    }
  }
}
</script>

<style scoped>
.node:not(.root) {
  margin-left: 20px;
}
.node.root > .content > .node {
  margin-left: 0;
}
.node:not(.root) > .content {
  padding: 0 10px;
}
.leaf.selected {
  font-weight: bold;
}
</style>