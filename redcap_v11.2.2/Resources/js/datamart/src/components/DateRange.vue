<template>
  <div class="d-flex">
      <b-form-group label-cols="2" id="date-min-group" label="from" label-for="date-min"  class="w-50">
        <b-form-datepicker :date-format-options="dateFormat" placeholder="from" v-model="min" :max="max" today-button reset-button close-button id="date-min" />
      </b-form-group>
      <b-form-group label-cols="2" id="date-max-group" label="to" label-for="date-max" class="ml-2 w-50">
          <b-form-datepicker :date-format-options="dateFormat" placeholder="end date" v-model="max" :min="min" today-button reset-button close-button id="date-max" />
      </b-form-group>
  </div>
</template>

<script>

export default {
  data() {
    return {
      dateFormat: { year: 'numeric', month: '2-digit', day: '2-digit' }
    }
  },
  computed: {
    min: {
      get() {
        const date = this.$store.state.revision.dateMin
        return date;
      },
      set(value) {
        this.$store.dispatch('revision/setDateMin', value)
      },
    },
    max: {
      get() {
        const date = this.$store.state.revision.dateMax
        return date
      },
      set(value) {
        this.$store.dispatch('revision/setDateMax', value)
      },
    },
  },
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
