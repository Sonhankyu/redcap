<template>
  <div>
    <h6>this is the parent</h6>
    <slot :something="aString"></slot>
  </div>
</template>

<script>
export default {
  data() {
    return {
      aString: 'string from the parent'
    }
  }
}
</script>

<style>

</style>