<template>
  <div>
    <b-table striped bordered hover small :fields="fields" :items="tokens" class="access-tokens-table">
      <template #cell(index)="data">
        <div>
          <span>{{ data.index + 1 }}</span>
        </div>
      </template>
      <template #cell(access_token)="data">
        <div class="d-flex">
          <b-button @click="copyToClipboard(data.value, `${data.field.label} copied`)" size="sm"><font-awesome-icon :icon="['fas', 'copy']" /></b-button>
          <span class="ml-2 cell-info">{{data.value}}</span>
        </div>
      </template>
      <template #cell(refresh_token)="data">
        <div class="d-flex">
          <b-button @click="copyToClipboard(data.value, `${data.field.label} copied`)" size="sm"><font-awesome-icon :icon="['fas', 'copy']" /></b-button>
          <span class="ml-2 cell-info">{{data.value}}</span>
        </div>
      </template>
      <template #cell()="data">
        <span class="cell-info" :title="data.value">{{ data.value }}</span>
      </template>
    </b-table>
  </div>
</template>

<script>
export default {
  computed: {
    tokens() {
      return this.$store.state.user.tokens
    },
    fields() {
      // extract all keys
      let keys = this.tokens.reduce((accumulator, token) => {
        let keys = Object.keys(token)
        return [...accumulator, ...keys]
      }, [])
      // keys.splice(0, 0, 'index')
      return keys
    }
  },
  methods: {
    async copyToClipboard(text, message) {
      message = message || 'text copied'
      const result = await navigator.clipboard.writeText(text)
      this.$bvToast.toast(message, {
        title: 'Success',
        toaster: 'b-toaster-top-right',
        solid: false, //translucent
        autoHideDelay: 1500,
        // variant: 'light',
      })
    },
  }
}
</script>

<style scoped>

.access-tokens-table {
  max-width: 100%;
}
.access-tokens-table >>> td .cell-info {
  overflow-x: hidden;
  text-overflow: ellipsis;
  display: block;
  white-space: nowrap;
  max-width: 100px;
}
</style>