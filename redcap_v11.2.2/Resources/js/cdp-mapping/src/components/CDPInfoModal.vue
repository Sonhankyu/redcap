<template>
  <b-modal :title-html="title" v-model="visible" ok-only size="lg">
    <div v-html="content"></div>
  </b-modal>
</template>

<script>
export default {
  data() {
    return {
      title: '',
      content: '',
      visible: false,
    }
  },
  methods: {
    async open() {
      await this.getInfo()
      this.visible = true
    },
    async getInfo() {
      let service_type = null
      try {
        service_type = this.$store.state.app_settings.project.realtime_webservice_type
      } catch (error) {
        service_type = 'fhir'
      }
      const response = await this.$API.dispatch('settings/getInfoPage', service_type)
      const {data: {title='', content=''}={}} = response
      this.title = title
      this.content = content
    },
  }
}
</script>

<style>

</style>