<template>
  <div>
      <b-form @submit="onSubmit" @reset="onReset" >
        <div class="d-flex mb-2">
            <b-form-datepicker placeholder="start date" v-model="fromProxy" class="" :max="toProxy || today" today-button reset-button close-button/>
            <b-form-datepicker placeholder="end date" v-model="toProxy" class="ml-2" :min="fromProxy" :max="today" today-button reset-button close-button/>
        </div>

        <b-button type="submit" variant="primary" :disabled="loading">
            <font-awesome-icon :icon="['fas', 'spinner']" spin fixed-width v-if="loading" />
            <font-awesome-icon :icon="['fas', 'chart-bar']" fixed-width v-else />
            <span class="ml-2">Display results</span>
        </b-button>
        <b-button type="reset" variant="danger" class="ml-2">
            <font-awesome-icon :icon="['fas', 'undo']" fixed-width />
            <span class="ml-2">Reset</span>
        </b-button>
      </b-form>
  </div>
</template>

<script>
export default {
    data() {
        const today = new Date()
        return {
            loading: false,
            today: today,
        }
    },
    computed: {
        queryParams() {
            return {
                date_start: this.fromProxy,
                date_end: this.toProxy,
            }
        },
        fromProxy: {
            get() { return this.$route.query.date_start },
            set(value) {
                const query = this.queryParams
                query.date_start = value
                this.$router.push({query})
            }
        },
        toProxy: {
            get() { return this.$route.query.date_end },
            set(value) {
                const query = this.queryParams
                query.date_end = value
                this.$router.push({query})
            }
        },
    },
    methods: {
        async onSubmit(event) {
            event.preventDefault()
            this.loading = true
            await this.$store.dispatch('stats/getStats', {from:this.fromProxy, to:this.toProxy})
            this.loading = false
        },
        onReset() {
            this.fromProxy = this.toProxy = null
        },
    },
}
</script>

<style>

</style>