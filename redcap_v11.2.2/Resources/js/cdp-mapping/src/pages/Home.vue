<template>
  <div>
      <section class="text-justify">
        <p>{{translations['rws_description']}}</p>
        <p>{{translations['rws_description_1']}}</p>
        <p>
            <span>{{translations['rws_description_2']}}</span>
            <span class="d-block">
              <a href="#" @click.prevent="onTellMeMoreClicked">{{translations['tell_me_more']}}.</a>
            </span>
        </p>
        <MappingHelperPanel class="my-2"/>
      </section>

      <SettingsTable class="mb-2"/>

      <MappingTable>
        <template v-slot:head><ImportExport /></template>
      </MappingTable>

      <SaveCancel class=" mt-2" />

      <CDPInfoModal ref="info-modal"/>

  </div>
</template>

<script>
import MappingTable from '@/components/MappingTable/MappingTable'
import SettingsTable from '@/components/Settings/SettingsTable'
import ImportExport from '@/components/ButtonGroups/ImportExport'
import SaveCancel from '@/components/ButtonGroups/SaveCancel'
import CDPInfoModal from '@/components/CDPInfoModal'
import MappingHelperPanel from '@/components/MappingHelperPanel'

export default {
  components: {MappingTable,SettingsTable,ImportExport,SaveCancel, MappingHelperPanel, CDPInfoModal},
  computed: {
    translations() { return this.$store.state.app_settings.translations },
    mapping_helper_url() { return this.$store.state.app_settings.mapping_helper_url },
  },
  methods: {
    onTellMeMoreClicked() {
      const infoModal = this.$refs['info-modal']
      if(infoModal) infoModal.open()
    }
  },
}
</script>

<style>

</style>