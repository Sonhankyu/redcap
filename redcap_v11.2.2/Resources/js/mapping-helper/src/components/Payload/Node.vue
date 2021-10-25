<template>
<span class="node">
  <span class="parenthesis open" v-if="isArray(payload)" v-text="`\[`" />
  <span class="parenthesis open" v-if="isObject(payload)" v-text="`\{`" />

  <span class="expander text-muted" @click.stop="onClick">
    <font-awesome-icon v-if="open" :icon="['fas', 'chevron-circle-down']" />
    <font-awesome-icon v-else :icon="['fas', 'chevron-circle-right']" />
  </span>

  <template v-if="open" >
    <span class="child d-block ml-2" v-for="(value, key) in payload" :key="key" >
      <!-- hide array numbers -->
      <span v-if="!isArray(payload)" class="mr-2 font-weight-bold">"{{key}}":</span>
      <span v-if="isLeaf(value)" v-text="print(value)" :class="getClass(value)"></span>
      <!-- <div v-else>{{value}}</div> -->
      <Node v-else :payload="value" />
      <span v-if="!isLast(key)">,</span>
    </span>
  </template>
  <span class="parenthesis closed" v-if="isArray(payload)" v-text="`\]`" />
  <span class="parenthesis closed" v-if="isObject(payload)" v-text="`\}`" />
</span>
</template>

<script>


export default {
  name: 'Node',
  data() {
    return {
      open: true,
    }
  },
  components: {Node},
  props: {
    payload: {
      type: [Object, Array],
      default: () => ({})
    }
  },
  methods: {
    isLeaf(item) {
      return !(this.isObject(item) || this.isArray(item))
    },
    isObject(item) {
      return typeof item === 'object' && !Array.isArray(item)
    },
    isArray(item) {
      return Array.isArray(item)
    },
    isLast(key) {
      if(this.isObject(this.payload)) {
        let keys = Object.keys(this.payload)
        return keys.slice(-1)[0] === key
      } else if(this.isArray(this.payload)) {
        return key === ([...this.payload].length)-1
      }
      return false
    },
    print(value) {
      if(typeof value==='string') return `"${value}"`
      else return value
    },
    getClass(value) {
      let type = typeof value
      return type
    },
    onClick() {
      this.open = !this.open
    }
  }
}
</script>

<style scoped>
.expander {
  cursor: pointer;
}


</style>