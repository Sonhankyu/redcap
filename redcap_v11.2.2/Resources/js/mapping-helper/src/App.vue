<template>
  <section id="mapping-helper-app">

    <transition name="loader-fade" >
      <div>
        <b-overlay class="loading-overlay" :show="status == status_list.LOADING" rounded="sm" :opacity=".90" blur="5px">
          <router-view v-if="status===status_list.READY"/>
        </b-overlay>
        <div v-if="status===status_list.ERROR">
          {{error}}
        </div>
      </div>
    </transition>
  </section>
</template>

<script>
import '@/init' //load all libraries
import store from '@/store'
import router from '@/router'

const STATUS = Object.freeze({
  LOADING: 'loading',
  READY: 'ready',
  ERROR: 'error',
})

export default {
  data() {
    return {
      status_list: {...STATUS},
      status: null,
      error: null,
    }
  },
  created() {
    this.init()
  },
  methods: {
    async init() {
      try {
        this.error = null
        this.status = STATUS.LOADING
        const response = await this.$API.dispatch('settings/get')
        const {data={}} = response
        const {app_settings={}, project_info={}, user_info={}} = data
        await this.$store.dispatch('app_settings/set', app_settings)
        await this.$store.dispatch('user/set', user_info)
        await this.$store.dispatch('project/set', project_info)
        this.status = STATUS.READY
      } catch (error) {
        this.error = error
        this.status = STATUS.ERROR
      }
    },
  },
  store,
  router,
}
</script>

<style>


</style>
<style scoped>
* >>> .alert {
  border-color: rgba(0,0,0,0.2) !important;
}

.loading-overlay {
  min-height: 200px;
}
#mapping-helper-app {
  /* font-family: 'Avenir', Helvetica, Arial, sans-serif; */
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  min-width: 300px;
  /* max-width: 786px; */
  /* text-align: center; */
  color: #2c3e50;
  /* width: 80%; */
  padding: 0;
  position: relative;
}
.loader-container {
  position: absolute;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  margin-top: 20%;
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

</style>
