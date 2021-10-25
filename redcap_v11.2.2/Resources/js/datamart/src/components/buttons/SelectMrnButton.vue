<template>

<div>
  <b-button v-b-toggle.sidebar-search variant="info">
    <font-awesome-icon :icon="['fas', 'search']"/> 
    <span class="ml-1">Select and fetch records...</span>
  </b-button>
  <b-sidebar id="sidebar-search" title="Search MRN" right shadow lazy width="500px">
    <div class="px-3 py-2">
      <p>Search a fetchable MRN available in the project and fetch its data individually</p>
      <SearchMrns />
    </div>
  </b-sidebar>
</div>




</template>

<script>

import SearchMrns from '@/components/SearchMrns'

export default {
  components: { SearchMrns },
  data: () => ({

  }),
  computed: {
    
  },
  methods: {
    
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  /* button:disabled {
    cursor: help;
  } */
  button {
    transition-property: opacity;
    transition-duration: 150ms;
    transition-timing-function: ease-in-out;
    opacity: 1.0;
  }
  #fetch-form {
    max-width: 80vw;
  }
  #fetch-form >>> label {
    white-space: nowrap;
  }
</style>
