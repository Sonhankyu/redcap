<template>
  <div>
    <span class="d-block" v-html="text_with_links" />
    <b-button :href="mapping_helper_url" class="text-white" variant="info" size="sm">
      <font-awesome-icon :icon="['fas', 'code-branch']" />
      <span> Mapping Helper</span>
    </b-button>
  </div>
</template>

<script>
export default {
computed: {
    translations() { return this.$store.state.app_settings.translations },
    mapping_helper_url() { return this.$store.state.app_settings.mapping_helper_url },
    text() {
      const {cdp_settings_mapping_helper_text: text=''} = this.translations
      return text
    },
    text_with_links() {
      let text = String(this.text)
      let mapping_helper_string = 'Mapping Helper'
      const regexp = new RegExp(mapping_helper_string,'ig')
      const url = this.mapping_helper_url
      const link = `<a href="${url}">${mapping_helper_string}</a>`
      const html = text.replace(regexp, link)
      return html
    }
  }
}
</script>

<style>

</style>