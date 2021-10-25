
<template>
  <div class="page-wrapper">
    <SearchForm />
    <Charts class="mt-2"/>
  </div>
</template>


<script>
import SearchForm from '@/components/SearchForm.vue'
import Charts from '@/components/Charts.vue'

export default {
  name: 'HomePage',
  components: { SearchForm, Charts },
  methods: {},
  computed: {},
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>