<template>
  <section id="data-mart-app">
    <!-- <button type="button" @click="goToReviewProjectPage">goToReviewProjectPage</button> -->
    <template v-if="loading">
      <b-overlay :show="loading" rounded="sm">
        <b-skeleton-wrapper :loading="loading">
          <template #loading>
            <b-card>
              <b-skeleton width="85%"></b-skeleton>
              <b-skeleton width="55%"></b-skeleton>
              <b-skeleton width="75%"></b-skeleton>
              <b-skeleton width="70%"></b-skeleton>
              <b-skeleton width="80%"></b-skeleton>
            </b-card>
            <b-col class="mt-3">
              <b-skeleton-img no-aspect height="150px"></b-skeleton-img>
            </b-col>
            <b-col class="mt-3">
              <b-skeleton-img></b-skeleton-img>
            </b-col>
          </template>
        </b-skeleton-wrapper>
      </b-overlay>
    </template>

    <router-view v-else/>

    <b-alert variant="danger" class="text-center" :show="error!=false">{{error}}</b-alert>
  </section>
</template>

<script>
import '@/init.js'

import store from '@/store'
import router from '@/router'


export default {
  store: store,
  router: router,
  data: () => ({
    loading: false,
    error: false,
  }),
  created() {
    this.loadData()
  },
  methods: {
    /**
     * NOTE: order is important!
     * the metadata list should be dispatched first!
     */
    async loadData() {
      try {
        this.error = false
        this.loading = true
        const response = await this.$API.getSettings()
        const {app_settings,fhir_metadata,revisions,source_fields,user} = response.data
        
        // message: 'setting source fields',
        this.$store.dispatch('metadata/setList', fhir_metadata) // must be dispatched first since revision depends on it
        this.$store.dispatch('settings/set', app_settings),
        // message: 'setting user info',
        this.$store.dispatch('user/setInfo', user),
        // message: 'setting translations and magic',
        // this.$store.dispatch('nodes/setNodes', source_fields),
        // message: 'setting revisions',
        this.$store.dispatch('revisions/setList', revisions)
        this.$store.dispatch('revisions/selectMostRecentRevision')


      } catch (error) {
        this.onError(error)
      }finally {
        this.onLoad()
      }
    },
    /**
     * dispatch actions once data is loaded by the loader
     */
    async onLoad() {
      this.loading = false
      // emit load event so REDCap can change route if needed (see blade views)
       this.$emit('load')
    },
    onError(error) {
      let message = 'Unexpected error'
      if('response' in error) {
        let {data=message} = error.response
        message = data
      }else if('toJSON' in error) {
        let {message:errorMessage=message} = error.toJSON()
        message = errorMessage
      }
      console.log(message)
      this.error = message
    },
    /**
     * validate the settings before submitting a revision
     */
    async validate() {
      const valid = await this.$store.dispatch('revision/validate')
      return Boolean(valid)
    },
    /**
     * exposed method for routing
     */
    goToCreateProjectPage() {
      const {name:route_name} = this.$route
      if(route_name=='create-project') return
      this.$router.push({name:'create-project'})
    },
    /**
     * exposed method for routing
     */
    goToReviewProjectPage() {
      const {name:route_name} = this.$route
      if(route_name=='review-project') return
      this.$router.push({name:'review-project'})
    },
  }
}
</script>

<style scoped>
#data-mart-app {
  /* font-family: 'Avenir', Helvetica, Arial, sans-serif; */
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  /* color: #2c3e50; */
  /* height: 100%; */
  min-height: 300px;
  min-width: 300px;
  max-width: 786px;
  position: relative;
  /* display: contents; */
  /* margin: 20px auto; */
}
/* transition */
.loader-fade-enter,
.loader-fade-leave-active {
  opacity: 0;
}
.loader-fade-enter-active,
.loader-fade-leave-active {
  transition: opacity .3s ease-out;
}
.loader-container {
  position: absolute;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  flex-direction: column;
  text-align: center;
}

</style>

<style>
/* global styles */
#data-mart-app summary {
  display: block;
}
/* make sure the .swal2-container will be behind
the bootstrap modal with the autologout message */
.swal2-container {
  z-index: 1;
}
/* .swal2-container.swal2-center.swal2-backdrop-show { */
</style>
