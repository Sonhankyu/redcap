<template>
  <Parent>
    <template v-slot:default="parent">
      <Child :something="parent.something"></Child>
    </template>
  </Parent>
</template>

<script>
import Parent from '@/components/scoped_slots_example/Parent'
import Child from '@/components/scoped_slots_example/Child'

export default {
  components: {Parent, Child}
}
</script>

<style>

</style>