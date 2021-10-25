<template>
  <div v-if="has_errors" class="errors">
      <p>Warning - the following errors occurred:</p>
      <b-table id="errors-table" class="my-2"
            :items="errors" :fields="fields"
            :per-page="perPage" :current-page="currentPage"
            small bordered striped hover >
        <template #cell(error)="data">
            <section class="">
                <span>{{ data.value.message }}</span>
                <p v-if="data.value.detail">{{data.value.detail.message}} <span>({{error.detail.code}})</span></p>
            </section>
        </template>
      </b-table>
      <b-pagination class="ml-0" v-if="has_errors"
          v-model="currentPage"
          :total-rows="total"
          :per-page="perPage"
          aria-controls="errors-table"
          size="sm"
          ></b-pagination>
  </div>
  <!-- do not show anything if no errors -->
  <div v-else />
</template>

<script>
export default {
  name: 'FhirErrors',
  data() {
    return {
      currentPage: 1,
      perPage: 10,
      fields: ['mrn', 'error']
    }
  },
  props: {
    errors: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    has_errors() { return this.errors.length>0 },
    total() { return this.errors.length },
  },
  methods: {}
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
