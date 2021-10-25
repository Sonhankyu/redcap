<template>
  <div>
      hello from the store component
  </div>
</template>

<script>
export default {
    created() {
        console.log(this, this.$store)
    }
}
</script>

<style>

</style>