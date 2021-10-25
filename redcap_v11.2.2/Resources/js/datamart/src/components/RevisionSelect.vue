<template>
<div >
  <b-dropdown id="revision-selector1" class="m-0" variant="secondary" :disabled="!userIsAdmin" :no-caret="!userIsAdmin">
    <template #button-content>
      <span v-if="selected && selected.index">Revision {{selected.index}}</span>
    </template>
    <template v-if="userIsAdmin">
    <b-dropdown-item-button v-for="(revision, index) in revisions" :key="index"
      :class="{selected: isCurrent(revision), approved: revision.isApproved()}" 
      @click="onSelect(revision)">
      <section class=info>
        <span class="badge badge-info mr-1">{{revision.index}}</span>
        <span>Revision date: {{ revision.metadata.date}}</span>
      </section>
      <RevisionMetadataIcons class="metadata-icons d-inline ml-1" :revision="revision" />
    </b-dropdown-item-button>
    </template>
  </b-dropdown>
</div>
</template>

<script>
import RevisionMetadataIcons from '@/components/RevisionMetadataIcons'

export default {
  name: 'RevisionList',
  components: { RevisionMetadataIcons },
  computed: {
    revisions() {
      return this.$store.state.revisions.list
    },
    selected() {
      return this.$store.getters['revisions/selected']
    },
    isCurrent() {
      return (revision) => {
        if(this.selected==null) return false
        try {
          return this.selected.metadata.id == revision.metadata.id
        } catch (error) {
          return false
        }
        // selected.metadata.id==revision.metadata.id
      };
    },
    buttonText() {
      if(!this.userIsAdmin || this.revisions.length==1) {
        if(this.selected) return `Revision ${this.selected.index}`
        else return 'no revisions'
      }else {
        if(this.selected) return `Revision ${this.selected.index}`
        else return 'Select a revision'
      }
    },
    /**
     * check if current user is a Super user.
     * A super user can approve revisions
     */
    userIsAdmin() {
      const user = this.$store.state.user.info
      if(!user) return false
      const { super_user: super_user=false } = user
      return super_user
    },
  },
  methods: {
    onSelect(revision) {
      const revision_id = revision.metadata.id
      this.$store.dispatch('revisions/setSelected', revision_id)
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
#revision-selector1 >>> .selected .dropdown-item{
  font-weight: bold;

}
#revision-selector1 >>> .dropdown-item {
  display: flex;
  white-space: nowrap;
  padding: 5px 5px;
  color: inherit;
  text-decoration: none;
  font-size: 13px;
  align-items: center;
}
#revision-selector1 >>> .dropdown-item:hover {
  background-color: #f8f9fa;
}
#revision-selector1 >>> .dropdown-item.selected {
  font-weight: bold;
}
#revision-selector1 >>> .dropdown-item .info {
  margin-right: auto;
}
#revision-selector1 >>> .dropdown-item .metadata-icons {
  margin-left: 3px;
  padding-left: 3px;
  border-left: solid 1px #cacaca;
  display: inline-block;
  min-width: 13px;
}

@media only screen and (max-width: 768px) {
  nav.menu .submenu {
    width: 100%;
  }
}
</style>
