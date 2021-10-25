<template>
  <div class="home-page">
    <p>{{lang['mapping_helper_02']}}</p>
  </div>
</template>

<script>

export default {
  components: {},
  computed: {
    lang() {
      const {lang} = this.$store.state.app_settings
      return lang
    }
  },
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>