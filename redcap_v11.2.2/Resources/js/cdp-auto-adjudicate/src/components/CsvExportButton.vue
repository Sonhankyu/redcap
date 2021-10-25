<template>
  <div>
    <b-button :disabled="items.length<1" size="sm" variant="outline-primary" :href="getBlobUrl()" :download="`${file_name}.csv`">
      <font-awesome-icon :icon="['fas', 'file-csv']" /> 
      <span class="ml-1"><slot></slot></span>
    </b-button>
  </div>
</template>

<script>
export default {
  props: {
    items: {
      type: Array,
      default: () => []
    },
    file_name: {
      type: String,
      default: 'export'
    },
    text: {
      type: String,
      default: 'Export'
    },
  },
  methods: {
    /**
     * generate a blob that can be used in a link to export the errors
     */
    getBlobUrl() {
      const items = [...this.items]
      let header = null
      const rows = []
      items.forEach(item => {
        if(!header) {
          header = Object.keys(item).join(',')
          rows.push(header)
        }
        let row = []
        for(let [key, value] of Object.entries(item)) {
          let result = value.replace(/"/g, '""')
          if (result.search(/("|,|\n)/g) >= 0) result = `"${result}"`
          row.push(result)
        }
        rows.push(row.join(','))
      })

      let csv_content = rows.join("\r\n")
      const blob = new Blob([csv_content], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      // const encodedUri = encodeURI(url)
      return url
    }
  }
}
</script>

<style>

</style>