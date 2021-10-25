<template>
<div>
    <b-button-group size="sm" >
        <b-button @click="onSaveClicked" :disabled="save_disabled" variant="outline-info">
            <font-awesome-icon :icon="['fas', 'save']" fixed-width/>
            <span class="ml-2" v-if="save_text" v-text="save_text"/>
        </b-button>
        <b-dropdown size="sm" variant="outline-info" :disabled="this.items.length<1" >
            <template #button-content>
                <font-awesome-icon :icon="['fas', 'database']" fixed-width/>
            </template>
            <template>
                <b-dropdown-text>Restore preset</b-dropdown-text>
                <b-dropdown-divider />
                <b-dropdown-item v-for="(item, index) in items" :key="`${index}-${item.key}`" @click="onSelect(item)">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-nowrap font-weight-bold text-muted">{{item.key}}</span>
                        <b-button class="ml-2" @click="confirmRemove(item)" size="sm" variant="outline-danger">
                            <font-awesome-icon :icon="['fas', 'trash']" fixed-width/>
                        </b-button>
                    </div>
                </b-dropdown-item>
            </template>
        </b-dropdown>
    </b-button-group>
    <slot :addItem="addItem"></slot>
</div>
</template>

<script>
function storageAvailable(type) {
    var storage;
    try {
        storage = window[type];
        var x = '__storage_test__';
        storage.setItem(x, x);
        storage.removeItem(x);
        return true;
    }
    catch(e) {
        return e instanceof DOMException && (
            // everything except Firefox
            e.code === 22 ||
            // Firefox
            e.code === 1014 ||
            // test name field too, because code might not be present
            // everything except Firefox
            e.name === 'QuotaExceededError' ||
            // Firefox
            e.name === 'NS_ERROR_DOM_QUOTA_REACHED') &&
            // acknowledge QuotaExceededError only if there's something already stored
            (storage && storage.length !== 0);
    }
}

/**
 * for v-model to work on parents using this component:
 * - the 'value' prop must be bind to the input field
 * - an input event must be emitted when the input field fires input: this.$emit('input', event.target.value)
 * 
 * the approach described above is only used if the value prop is provided.
 * if value is not provided then we will use internal_value.
 * to choose between value and internal value we use the computed property input as a proxy.
 * 
 */
export default {
    data() {
        return {
            items: [],
        }
    },
    props: {
        storage_key: {
            type: String,
            default: 'my_storage'
        },
        save_disabled: {
            type: Boolean,
            default: true,
        },
        save_text: {
          type: String,
          default: ''
        },
    },
    created() {
        this.items = this.getStoredItems()
    },
    computed: {
        storage_enabled() {
            return storageAvailable('localStorage')
        },
    },
    methods: {
        /**
         * emit input when an item is selected
         */
        onSelect(item) {
            this.$emit('restore', item.value)
        },
        /**
         * save an item in the localStorage
         */
        addItem(key, value) {
            const item = {key, value}
            console.log(key, value)
            this.items.push(item)
            this.storeItems()
        },

        /**
         * remove an item from the localStorage
         */
        async confirmRemove(item) {
            const response = await this.$bvModal.msgBoxConfirm('Are you sure you want to delete this preset?', {
                title: 'Delete this preset?',
                okTitle: 'Delete',
                okVariant: 'danger',
            })
            if(!response) return
            const index = this.items.indexOf(item)
            if(index<0) return
            // remove 1 item at index
            this.items.splice(index,1)
            this.storeItems()
        },
        getStoredItems() {
            if(!this.storage_enabled) return []
            if(!this.storage_key) return []
            const stored_items = localStorage.getItem(this.storage_key)
            const items = JSON.parse(stored_items) || []
            if(Array.isArray(items)) return items
            else return [items]
        },
        storeItems() {
            localStorage.setItem(`${this.storage_key}`, JSON.stringify([...this.items]))
        },
        /**
         * emit the save event passing the storeItems function
         */
        onSaveClicked() {
            this.$emit('save', this.addItem);
        }
    },
}
</script>

<style scoped>
.dropdown-item {
    display: flex;
    flex-direction: row;
    align-items: center;
}
.dropdown-item a {
    font-size: 0.8rem;
    color: inherit;
}
.remove-item {
    margin-left: auto;
}
</style>