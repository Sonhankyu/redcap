<template>
<div>
    <!-- <ul class="nav nav-tabs">
        <li v-for="(link, index) in links" :key="index" class="nav-item">
            <router-link :to="{name: link.route_name}" class="nav-link" :class="activeClass(link.route_name)" :exact="link.exact">{{link.label}}</router-link>
        </li>
    </ul> -->
  <b-navbar toggleable="lg"  type="dark" variant="info">
      <b-navbar-brand href="#">Mapping Helper</b-navbar-brand>

      <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

      <b-collapse id="nav-collapse" is-nav>
        <b-navbar-nav>
          <!-- <b-nav-item v-for="(link, index) in links" :key="index"
            :to="{name: link.route_name}" :exact="link.exact" :active="isLinkActive(link.route_name)">{{link.label}}</b-nav-item> -->
          <b-nav-item :to="{name: 'home'}" :exact="true" :active="isLinkActive('home')">Home</b-nav-item>

          <!-- Navbar dropdowns -->
          <b-nav-item-dropdown text="Resources">
            <!-- <template #button-content>
              <span>{{endpoint_dropdown_title}}</span>
            </template> -->
            <b-dropdown-item v-for="(link, index) in fhir_links" :key="index"
              :to="{name: link.route_name}" :exact="link.exact" :active="isLinkActive(link.route_name)">{{link.label}}</b-dropdown-item>
            <b-dropdown-divider />
            <b-dropdown-item :to="{name: 'custom-request'}" :active="isLinkActive('custom-request')">Custom request</b-dropdown-item>
          </b-nav-item-dropdown>
          
          <!-- <b-nav-item :to="{name: 'scoped-slots'}" :exact="true" :active="isLinkActive('scoped-slots')">Scoped Slots</b-nav-item> -->
          
        </b-navbar-nav>


        <!-- Right aligned nav items -->
        <b-navbar-nav class="ml-auto">

          <b-nav-item-dropdown text="Tools" right>

            <b-dropdown-text>
              <div class="d-flex justify-content-center align-items-center">
                <img class="fhir-logo" src="@/assets/fhir-logo-www.png" />
                <b-nav-item><b-badge variant="light">{{fhir_code}}</b-badge></b-nav-item>
              </div>
            </b-dropdown-text>

            <b-dropdown-item :to="{name: 'access-tokens'}" :active="isLinkActive('access-tokens')">Access Tokens</b-dropdown-item>

          </b-nav-item-dropdown>

        </b-navbar-nav>
      </b-collapse>
  </b-navbar>
</div>
</template>

<script>
import {menu_links, fhir_menu_links} from '@/variables'

export default {
  data() {
    return {

    }
  },
  computed: {
    links() {
      return {...menu_links}
    },
    fhir_links() {
      const links = [...fhir_menu_links].filter(link => {
        const regexp = new RegExp(`^${this.fhir_code}$`, 'i')
        return link.tags.some( tag => tag.match(regexp))
      })
      return links
    },
    fhir_code() {
      return this.$store.state.app_settings.fhir_code
    },
    endpoint_dropdown_title() {
      const fhir_links_names = [...this.fhir_links].map(link => link.route_name)
      const index = fhir_links_names.indexOf(this.$route.name)
      if(index>=0) return this.fhir_links[index].label
      else return 'Resources'
    },
    tokens() {
      return this.$store.state.user.tokens
    },
  },
  methods: {
    activeClass(...names) {
      for (let name of names) {
        if(name == this.$route.name) return 'active'
      }
    },
    isLinkActive(name) {
      if(name == this.$route.name) return true
      return false
    }
  }
}
</script>

<style scoped>
.fhir-logo {
  max-width: 100px;
  height: auto;
}
.token-wrapper {
  max-width: 200px;
}
.token-wrapper .token {
  display: block;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
</style>