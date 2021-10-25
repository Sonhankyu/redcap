<template>
<span class="fhir-field-label">
  <template v-if="identifier">
      <span class="identifier small mr-2">(<font-awesome-icon icon="star" class="text-warning"/>{{translations['source_identifier_field']}})</span>
  </template>
  <span class="small font-weight-bold" v-show="name">{{name}}</span>
  <span class="d-block">{{label}}</span>
  <span class="d-block font-italic small" v-if="category">({{category}})</span>
</span>
</template>

<script>
import { mapState } from 'vuex'

export default {
  computed: {
     ...mapState({
        translations: state => state.app_settings.translations,
     }),
  },
  props: {
    identifier: {
      type: Boolean,
      default: false,
    },
    name: {
      type: String,
      default: '',
    },
    label: {
      type: String,
      default: '',
    },
    category: {
      type: String,
      default: '',
    },
  }
}
</script>

<style scoped>
.fhir-field-label.disabled {
    text-decoration: line-through;
}
</style>