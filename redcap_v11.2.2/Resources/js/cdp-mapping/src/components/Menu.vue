<template>
  <div>
    <router-link :to="{name:'home'}" exact>
        <font-awesome-icon icon="home"/><span> Home</span>
    </router-link>
    <router-link :to="{name:'fhir_fields'}">
        <font-awesome-icon icon="tools"/><span> FHIR fields</span>
    </router-link>
    <router-link :to="{name:'test'}"><font-awesome-icon icon="tools"/><span> Test</span></router-link>
  </div>
</template>

<script>
export default {

}
</script>

<style scoped>
a {
  text-decoration: none;
  padding: 5px;
  color: black;
}
.router-link-exact-active,
.router-link-active {
  text-decoration: underline;
  color: dodgerblue;
}
a+a {
  margin-left: 5px;
}
</style>