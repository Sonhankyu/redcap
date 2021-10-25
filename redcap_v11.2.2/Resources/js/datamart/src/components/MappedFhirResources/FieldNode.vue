<template>
  <div class="m-0 p-0 font-italic field-node">
    <span :class="{'disabled-option': data.disabled}">
        <span>{{data.field}}</span>
        <span class="ml-2">({{data.label}})</span>
    </span>
    <span class="ml-2 text-danger" v-if="data.disabled" v-b-tooltip.hover :title="`${data.disabled_reason}`">
        <font-awesome-icon :icon="['fas', 'exclamation-circle']" fixed-width/>
    </span>
  </div>
</template>

<script>

export default {
    props: {
        data: {
            type: [Array,Object],
            default: () => []
        },
    }
}
</script>

<style scoped>
.field-node > .disabled-option {
    text-decoration: line-through;
}

</style>