<template>
    <div class="leaf">
        <label :for="`checkbox_${data.field}`" >
            <span v-html="`• ${data.field} (${data.label})`" :class="{disabled: data.disabled}"/>
            <span v-b-tooltip.hover :title="`${data.description}`"><font-awesome-icon icon="info-circle" class="ml-2" /></span>
        </label>
        <span v-if="data.disabled" class="text-danger" v-b-tooltip.hover :title="`${data.disabled_reason}`">
            <font-awesome-icon :icon="['fas', 'exclamation-circle']" fixed-width/>
        </span>
    </div>
</template>

<script>

export default {
    props: {
        data: {
            type: Object,
            default: ()=>({})
        },
    },
}
</script>

<style>
mark {
    padding: 0;
    /* background-color: aliceblue; */
}
</style>
<style scoped>
.leaf label .disabled {
    text-decoration: line-through;
}
.leaf {
    display: flex;
    flex-direction: row;
    align-items: center;
    padding: 3px 0;
}
.leaf:hover {
    background-color: rgba(0,0,0,0.1);
}
.leaf > * {
    cursor: pointer;
    display: block;
    margin: auto 0;
}
.leaf label{
    margin-left: 5px;
}
</style>