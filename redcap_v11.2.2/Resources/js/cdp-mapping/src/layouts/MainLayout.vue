
<template>
    <section class="layout">

      <div class="page-wrapper">
        <transition name="fade" appear>
          <router-view />
        </transition>
      </div>
    </section>
</template>

<script>


export default {
  components: {}
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition-property: opacity;
  transition-duration: .25s;
}

.fade-enter-active {
  transition-delay: .25s;
}

.fade-enter, .fade-leave-active {
  opacity: 0
}
</style>