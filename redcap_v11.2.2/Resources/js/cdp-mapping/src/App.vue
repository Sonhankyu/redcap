<template>
  <div id="app">
    <b-overlay :show="status !== status_list.STATUS_READY" rounded="sm" :opacity=".90" blur="5px">
        <router-view />
        {{ test }}
    </b-overlay>
    <div v-if="status == status_list.STATUS_ERROR">
      <pre>{{ error }}</pre>
    </div>
  </div>
</template>

<script>
import Vue from "vue";
import store from "@/store"; // store
import router from "@/router"; //router

// API
import API_Plugin from "@/plugins/API";
Vue.use(API_Plugin);

/* Bootstrap */
import BootstrapVue from "bootstrap-vue";
Vue.use(BootstrapVue);
import "bootstrap/dist/css/bootstrap.css";
import "bootstrap-vue/dist/bootstrap-vue.css";

/* Vuelidate */
import Vuelidate from "vuelidate";
Vue.use(Vuelidate);

// FontAwesome
import { library } from "@fortawesome/fontawesome-svg-core";
import { fas } from "@fortawesome/free-solid-svg-icons"; //import the whole library
import {
  FontAwesomeIcon,
  FontAwesomeLayers,
} from "@fortawesome/vue-fontawesome";
library.add(fas);
Vue.component("font-awesome-icon", FontAwesomeIcon);
Vue.component("font-awesome-layers", FontAwesomeLayers); // for stacking icons

import NonBlankSpace from "@/components/NonBlankSpace";
Vue.component("non-blank-space", NonBlankSpace);

import EventBus from "@/EventBus";

const STATUS_LOADING = "loading";
const STATUS_READY = "ready";
const STATUS_ERROR = "error";

export default {
  name: "App",
  store,
  router,
  data() {
    return {
      status: null,
      error: "",
      status_list: { STATUS_LOADING, STATUS_READY, STATUS_ERROR },
    };
  },
  created() {
    EventBus.$on("settings:updated", this.init)
    EventBus.$on("settings:saved", this.onSettingsSaved)
    EventBus.$on("settings:canceled", this.onSettingsCanceled)
    EventBus.$on("settings:error", this.onSettingsError)
    this.init();
  },
  destroyed() {
    EventBus.$off("settings:updated", this.init)
    EventBus.$off("settings:saved", this.onSettingsSaved)
    EventBus.$off("settings:canceled", this.onSettingsCanceled)
    EventBus.$off("settings:error", this.onSettingsError)
  },
  computed: {
    test() {
      return this.$store.state.settings.test
    },
  },
  methods: {
    onSettingsSaved() {
      this.init();
    },
    onSettingsCanceled() {
      this.init();
    },
    onSettingsError() {
      console.log("error detected");
    },
    async init() {
      try {
        this.status = STATUS_LOADING;
        const app_settings = await this.loadSettings()
        const { mapping } = app_settings
        // generate the state of the current mapping
        await this.$store.dispatch("mapping/makeList", mapping)
        // sort by 'FHIR'
        await this.$store.dispatch('mapping/sortBy', 'fhir')
        // initialize the user settings from the server settings
        await this.$store.dispatch("settings/init", app_settings)
        // init the FHIR fields store
        await this.initSelect(app_settings)
        this.status = STATUS_READY
      } catch (error) {
        this.setError(error)
      }
    },
    setError(error) {
      this.status = STATUS_ERROR
      this.error = error
    },
    async initSelect({ fhir_fields }) {
      // const {fhir_fields} = this.$store.state.settings
      const cloned_fields = JSON.parse(JSON.stringify(fhir_fields))
      let fields_list = Object.values(cloned_fields); // just get the values as array
      await this.$store.dispatch("fhir_fields_select/setFields", fields_list)
      this.ready = true
    },
    async loadSettings() {
      const response = await this.$API.dispatch("settings/get")
      const { data: settings = {} } = response
      await this.$store.dispatch("app_settings/setState", settings)
      return settings
    },
  },
};
</script>

<style scoped>
#app {
  /* font-family: Avenir, Helvetica, Arial, sans-serif; */
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  /* text-align: center; */
  /* color: #2c3e50; */
  max-width: 800px;
}
#app >>> .alert {
    border-color: rgba(0,0,0,0.1) !important;
}
#app >>> .alert.alert-warning {
    border-color: #ffeeba !important;
}
</style>
