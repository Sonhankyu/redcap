<template>
  <div>
    <span>{{description}}</span>
  </div>
</template>

<script>
export default {
  props: {
    description: {
      type: String,
      default: ''
    }
  }
}
</script>

<style>

</style>