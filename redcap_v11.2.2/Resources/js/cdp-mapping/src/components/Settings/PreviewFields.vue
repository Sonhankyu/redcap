<template>
  <div>

  <section class="mb-2" v-for="(preview_field, index) in preview_fields" :key="index">
    <b-button-group class="w-100">
      <b-dropdown class="scrollable" size="sm" left lazy block variant="outline-secondary" v-bind="$attrs">
        <template #button-content>
          <span class="label" v-if="!preview_field">Select...</span>
          <span class="label" v-else>{{preview_field}}</span>
        </template>
        <b-dropdown-text v-if="options_length<1">No data</b-dropdown-text>
        <template v-else>
          <b-dropdown-group v-for="(items, category) in awailable_preview_fields" :key="`cat-${category}`">
            <b-dropdown-header><span >{{category}}</span></b-dropdown-header>
            <b-dropdown-item-button v-for="(item, key) in items || []" :key="`${key}-${item.field}`"
            :active="item.field===preview_field" NOT_disabled="preview_fields.indexOf(item.field)>=0"
            @click="onSetFieldClicked(index, item.field)">
              <span>{{item.label}}</span>
            </b-dropdown-item-button>
            <b-dropdown-divider></b-dropdown-divider>
          </b-dropdown-group>
        </template>
      </b-dropdown>
      <b-button class="delete-button" size="sm" variant="outline-secondary"
      @click="onDeletePreviewFieldClicked(index)" NOT_disabled="total_preview_fields<=1">
        <font-awesome-icon icon="times" class="text-danger"/>
      </b-button>
      
    </b-button-group>
  </section>
  <b-button class="mt-0" variant="success" size="sm" 
    @click="onAddPreviewFieldClicked" :disabled="total_preview_fields>=max_preview_fields">
      <font-awesome-icon icon="plus-circle"/><span> {{translations['preview_fields_add_another_field']}}</span>
     </b-button>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import DropDown from '@/components/DropDown'

export default {
  components: { DropDown },
  data() {
    return {
      max_preview_fields: 5,
    }
  },
  computed: {
    ...mapState({
      preview_fields: state => state.settings.preview_fields,
      translations: state => state.app_settings.translations,
    }),
    ...mapGetters({
      awailable_preview_fields: 'app_settings/previewFields',
    }),
    options_length() {
      const options = this.awailable_preview_fields
      if(Array.isArray(options)) return options.length
      else return Object.values(options).length
    },
    total_preview_fields() {
      return [...this.preview_fields].length
    }
  },
  methods: {
    onSetFieldClicked(index, field) {
      this.$store.dispatch('settings/setPreviewField', {index, field})
    },
    onAddPreviewFieldClicked() {
      this.$store.dispatch('settings/addPreviewField')
    },
    onDeletePreviewFieldClicked(index)
    {
      this.$store.dispatch('settings/deletePreviewField', index)
    }
  }
}
</script>

<style scoped>
.label {
    display: block;
    overflow: hidden;
    max-height: 70%;
    text-overflow: ellipsis;
}
.scrollable {
  flex: 1;
}
.delete-button {
  flex: 0;
}
.scrollable >>> ul[role="menu"] {
  max-height: 50vh;
  overflow: auto;
}
.scrollable >>> button.dropdown-toggle {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  flex: 1;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}
</style>