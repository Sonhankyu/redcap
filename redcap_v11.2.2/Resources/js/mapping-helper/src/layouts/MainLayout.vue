
<template>
    <section class="layout">
      <Menu />
      <PageTitle />
      <StandAloneLaunchPanel v-if="!anyValidAccessTokens" />
      <div class="page-wrapper">
          <router-view />
      </div>

    </section>
</template>

<script>

import Menu from '@/components/Menu'
import PageTitle from '@/components/PageTitle'
import StandAloneLaunchPanel from '@/components/StandAloneLaunchPanel'

export default {
  name: 'MainLayout',
  components: {Menu,PageTitle,StandAloneLaunchPanel},
  computed: {
    anyValidAccessTokens() {
      const {info={}} = this.$store.state.user
      const {has_valid_access_token=false} = info
      return has_valid_access_token
    }
  }
}
</script>

<style scoped>
.layout {
  display: flex;
  flex-direction: column;
}
.page-wrapper {
  /* padding: 25px 0; */
  flex: 1;
}
nav a + a {
  margin-left: 5px;
}
@media only screen and (max-width: 768px) {

}
</style>