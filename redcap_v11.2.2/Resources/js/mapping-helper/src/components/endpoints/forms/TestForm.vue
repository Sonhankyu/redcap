<template>
  <div>
    <p>asdasdadsa</p>
    <!-- slots are used to share data with the container component -->
    <slot :endpoint="endpoint" :interaction="interaction"></slot>
  </div>
</template>

<script>


export default {
  data() {
    return {
      endpoint: 'Patient', //base URL for the FHIR resource
    }
  },
}
</script>

<style>

</style>