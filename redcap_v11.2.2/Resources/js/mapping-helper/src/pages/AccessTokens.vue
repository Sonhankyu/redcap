<template>
  <div>
    <AccessTokensTable />
    <div v-if="standalone_launch_url">
      <b-button :href="standalone_launch_url" variant="primary" size="sm">Standalone launch</b-button>
    </div>
  </div>
</template>

<script>
import AccessTokensTable from '@/components/AccessTokens/AccessTokensTable'

export default {
  components: {AccessTokensTable},
  computed: {
    standalone_launch_url() {
      return this.$store.state.app_settings.standalone_launch_url
    },
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