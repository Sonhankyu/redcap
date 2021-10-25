<template>
      <b-card
      :header="lang['ehr_launch_modal_01']"
      bg-variant="warning"
      text-variant="black"
      tag="article"
      class="mb-2">
      <p>{{lang['ehr_launch_modal_02']}}</p>
      <p>{{lang['ehr_launch_modal_03']}}</p>
      <p><b>EHR system:</b> <em>{{ehrSystem}}</em></p>
      <b-button variant="primary" :href="standalone_launch_url">
        <font-awesome-icon :icon="['fas', 'sign-in-alt']" fixed-width/>
        <span class="ml-2">Log in</span>
        </b-button>
    </b-card>
</template>

<script>
export default {
  computed: {
   lang() {
      const {lang} = this.$store.state.app_settings
      return lang
    },
    ehrSystem() {
      const {ehr_system_name} = this.$store.state.app_settings
      return ehr_system_name
    },
    standalone_launch_url() {
      const {standalone_launch_url} = this.$store.state.app_settings
      return standalone_launch_url
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