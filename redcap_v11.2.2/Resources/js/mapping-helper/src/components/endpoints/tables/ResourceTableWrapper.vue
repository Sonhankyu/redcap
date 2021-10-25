<template>
  <div class="my-2">

      <div class="d-flex align-items-center">
        <div>
          <b-button @click="stacked=!stacked" :class="{stacked: stacked}" variant="outline-secondary" size="sm">
            <font-awesome-icon class="icon" :icon="['fas', 'sync-alt']" fixed-width/>
            <span class="ml-2">Rotate table</span>
          </b-button>
        </div>

        <b-form-group class="mb-0 ml-2" >
          <b-input-group size="sm">
            <b-form-input
              id="filter-input"
              v-model="filter"
              type="search"
              placeholder="search..."
              debounce="300"
            ></b-form-input>

            <b-input-group-append>
              <b-button :disabled="!filter" @click="filter = ''" :variant="filter ? 'warning' : 'secondary'">Clear</b-button>
            </b-input-group-append>
          </b-input-group>
        </b-form-group>

        <span class="ml-2">Total: <b>{{totalItems}}</b></span>

        <div class="d-flex align-items-center ml-auto" >
          <b-pagination class="my-auto ml-0" v-model="currentPage" :total-rows="totalItems" :per-page="perPage" size="sm" v-if="hasItems"/>
          <b-dropdown text="Results per page" class="ml-2" size="sm" variant="outline-primary">
              <template #button-content>
                  <span>Per page: <b>{{perPage}}</b></span>
              </template>
              <template v-for="(perPageOption, index) in resultsPerPageOptions">
                  <b-dropdown-item :key="`per-page-${index}-${perPageOption}`" :active="perPageOption==perPage" @click="perPage=perPageOption">{{perPageOption}}</b-dropdown-item>
              </template>
          </b-dropdown>
        </div>

      </div>

      <div class="resource-table my-2">
        <!-- provide a custom table in the `routes` -->
        <slot v-bind="tableProps" :markText="markText"></slot>
        <!-- <slot name="test">default for test slot in ResourceTableWrapper</slot> -->
      </div>

  </div>
</template>

<script>
const resultsPerPageOptions = [25, 50, 100, 500]

export default {
  components: {  },
  data() {
    return {
      filter: null,
      filterOn: [],
      stacked: false,
      currentPage: 1,
      // per page dropdown
      perPage: resultsPerPageOptions[0],
      resultsPerPageOptions,
    }
  },
  methods: {
    markText(text) {
      const filter = this.filter
      if(!filter || typeof text !== 'string') return text
      const regExp = new RegExp(`(${filter})`, 'ig')
      const marked = text.replace(regExp, `<mark class="highlight">$1</mark>`)
      return marked
    }
  },
  computed: {
    itemsProxy() {
      const items = [...this.items]
      const filtered = items.filter(item => {
        if(!this.filter) return true
        const values = Object.values(item)
        const stringified = JSON.stringify(values)
        const regexp = new RegExp(this.filter, 'ig')
        return stringified.match(regexp)
      })
      return filtered
    },
    hasItems() { return this.itemsProxy.length>0 },
    totalItems() { return this.itemsProxy.length },
    icon_rotation() {
      return this.stacked ? 90 : 0
    },
    /**
     * group all props used in the table
     */
    tableProps() {
      return {
        stacked:this.stacked,
        items: this.itemsProxy,
        "filter-included-fields":this.filterOn,
        // "sticky-header":"1000px",
        "show-empty": true,
        currentPage: this.currentPage,
        perPage: this.perPage,
        small: true,
        bordered: true,
        striped: true,
        hover: true,
      }
    }
  },
  props: {
    items: {
      type: Array,
      default: () => []
    },
    fields: {
      type: Array,
      default: () => []
    },
  }
}
</script>

<style scoped>
.resource-table {
  overflow-x: auto;
}
.resource-table >>> table thead th {
  vertical-align: middle;
}
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
.resource-table >>> mark.highlight {
  padding: 0;
  background-color: gold;
  font-weight: 600;
}
</style>