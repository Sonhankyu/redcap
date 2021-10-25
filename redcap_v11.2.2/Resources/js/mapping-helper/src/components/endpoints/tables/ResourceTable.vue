<template>
<div>
    <b-table class="my-2" :fields="fields" v-bind="{...$attrs, ...$props}">
      <!-- Optional default data cell scoped slot -->
      <template #cell()="data">
        <span v-html="$attrs.markText(data.value)"></span>
      </template>
    </b-table>
</div>
</template>

<script>
export default {
  data() {
    return {
      
    }
  },
  computed: {
    fields() {
      const fields = this.setFields(this.items)
      return fields
    },
  },
  methods: {
    /**
     * build a list of fields based on the
     * keys of the items.
     * set the `sortable` property for all
     */
    setFields(items) {
      const duplicateChecker = [] //store keys here to check for duplicates
        const fields = [...items].reduce((accumulator, item) => {
          Object.keys(item).forEach(key => {
            if(duplicateChecker.indexOf(key)<0) {
              let field = {key, sortable:true}
              accumulator.push(field)
              duplicateChecker.push(key)
            }
          })
          return accumulator
        }, [])
        return fields
    }
  },
  props: {
    stacked: {
      type: Boolean,
      defaults: false,
    },
    items: {
      type: Array,
      default: () => []
    },
  },
}
</script>

<style scoped>

.icon {
  transition-delay: 0s;
  transition-duration: 100ms;
  transition-timing-function: ease-in-out;
  transition-property: transform;
  transform: rotate(0);
}
.stacked .icon {
  transform: rotate(-90deg);
}
</style>