<template>
  <div class="small">
    <span>{{data.label}}</span>
    <span>{{data.field}}</span>
    <span>{{data}}</span>
    <span>{{data.hidden}}</span>
  </div>
</template>

<script>
export default {
  name: 'FhirLeaf',
  props: {
    data: {
      type: Object,
      default: null
    }
  }
}
</script>

<style scoped>

</style>