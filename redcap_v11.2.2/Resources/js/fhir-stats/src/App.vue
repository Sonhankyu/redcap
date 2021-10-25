<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
import '@/init.js'
import store from '@/store'
import router from '@/router'


export default {
  name: 'App',
  store: store,
  router: router,
  components: {}
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
