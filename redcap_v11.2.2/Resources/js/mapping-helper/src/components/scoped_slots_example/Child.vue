<template>
  <div>
    <h6>this is the child</h6>
    <p>{{something}}</p>
  </div>
</template>

<script>
export default {
  props: {
    something: {
      type: String,
      default: 'no string'
    }
  }
}
</script>

<style>

</style>