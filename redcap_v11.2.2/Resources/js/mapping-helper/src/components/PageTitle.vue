<template>
  <div class="page-title my-2" v-html="title">
  </div>
</template>

<script>
export default {
  computed: {
    title() {
      let page_title = '&nbsp;'
      const {meta: {title='&nbsp;'}} = this.$route
      return title || page_title
    }
  }
}
</script>

<style scoped>
  .page-title {
    text-align: center;
    font-weight: bold;
  }
</style>