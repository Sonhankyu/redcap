<template>
<div>
    <b-form-group label-for="filter-input" label-cols-sm="0" label-align-sm="left" label-size="sm" class="mb-2 ml-auto">
        <b-input-group size="sm">
            <b-form-input id="filter-input" v-model="queryProxy" type="search" placeholder="Type to Search" debounce="300" />
            <b-input-group-append>
                <b-button :disabled="!queryProxy" @click="resetQuery">Clear</b-button>
            </b-input-group-append>
        </b-input-group>
    </b-form-group>

    <section>
        <section class="d-flex align-items-center" v-if="hasItems">
            <b-pagination class="my-auto"  v-model="currentPage" :total-rows="total" :per-page="perPage" aria-controls="my-table" size="sm" />

            <b-dropdown text="Results per page" class="ml-auto" size="sm">
                <template #button-content>
                    <span>Results per page: {{perPage}}</span>
                </template>
                <template v-for="(perPageOption, index) in resultsPerPageOptions">
                    <b-dropdown-item :key="`per-page-${index}-${perPageOption}`" :active="perPageOption==perPage" @click="setPerPage(perPageOption)">{{perPageOption}}</b-dropdown-item>
                </template>
            </b-dropdown>
        </section>

        <b-table id="my-table" class="my-2" :items="items" :fields="fields"
            small bordered striped hover empty-text="nothing to show" :show-empty="true">
        
            <template #head(select)="data">
                <div class="d-flex">
                    <b-form-checkbox v-model="allSelected" :indeterminate="indeterminate" @change="toggleAll" :disabled="total==0" switch>
                    <span >{{ data.label }}</span>
                    </b-form-checkbox>
                </div>
            </template>

            <template #cell(select)="data">
                <div class="select-wrapper">
                    <b-form-checkbox v-model="queue" :value="data.item['mrn']" switch size="lg" />
                </div>
            </template>

            <template #cell(mrn)="data">
                <section class="d-flex justify-content-between mrn">
                    <span>{{ data.value }}</span>
                </section>
            </template>
        </b-table>

    </section>

    <section class="mt-2">
        <b-button size="sm" variant="danger" @click="clearQueue" :disabled="totalToFetch==0">
            <font-awesome-icon :icon="['fas', 'trash']" fixed-width />
            <span class="ml-2">clear queue</span>
        </b-button>
        <b-button class="ml-2" size="sm" variant="success" @click="fetchQueue" :disabled="disabled">
            <font-awesome-icon :icon="['fas', 'cloud-download-alt']" fixed-width />
            <span class="ml-2">fetch data</span>
        </b-button>
        <span class="ml-2 text-muted">Total MRNs: <b>{{this.queue.length}}</b></span>
        <div class="queue-container">
            <b-table id="queue-table" class="my-2" :fields="['mrn']" :items="queueItems"
                small bordered  striped hover empty-text="nothing to show" :show-empty="true">
                <template #cell(mrn)="data">
                    <section class="d-flex justify-content-between mrn">
                        <span>{{ data.value }}</span>
                        <b-button size="sm" variant="danger" @click="removeFromQueue(data.value)">
                            <font-awesome-icon :icon="['fas', 'trash']" fixed-width />
                        </b-button>
                    </section>
                </template>
            </b-table>
        </div>
    </section>


    <b-modal ref="modal-fetch" id="modal-fetch" title="Fetching Data" size="md" no-stacking no-close-on-esc no-close-on-backdrop hide-header-close>
        <RevisionFetchProgress ref="fetch-progress" :progress_total="totalToFetch" :progress_value="totalProcessed" :mrn="currentMrn" :mrnsData="results"/>
        <template #modal-footer="{ok}">
            <div class="ml-auto">
                <b-button variant="secondary" size="sm" @click="cancelFetch" v-if="loading">Cancel</b-button>
                <b-button variant="primary" size="sm" @click="ok" v-else>Ok</b-button>
            </div>
        </template>
    </b-modal>

</div>
</template>

<script>
import RevisionFetchProgress from '@/components/RevisionFetchProgress'

const resultsPerPageOptions = [10, 25, 50, 100]

export default {
    components: { RevisionFetchProgress },
    data() {
        return {
            currentMrn: '',
            searching: false,
            loading: false,
            minimumQueryLength: 1,
            query: '',
            perPage: resultsPerPageOptions[0],
            currentPage: 1,
            items: [], // items in the table
            total: 0,
            fields: ['mrn','select'], // headers of the table
            totalProcessed: 0, // processed (fetched) items
            results: [], // collect fetched data
            queue: [],
            maxQueueSize: 10,
            resultsPerPageOptions
        }
    },
    computed: {
        queryProxy: {
            get() { return this.query},
            set(value) {
                if(value=='') return this.resetQuery()
                this.query = value
                this.submitQuery()
            }
        },
        allSelected: {
            get() {
                if(this.items.length==0) return false
                let allSelected = this.items.every(item => {
                    const {mrn} = item
                    return this.queue.indexOf(mrn)>=0
                })
                return allSelected
             },
            set(value) {} // do nothing
        },
        indeterminate: {
            get() {
                if(this.queue.length==0) return false
                if(this.items.every(({mrn}) => this.queue.indexOf(mrn)<0)) return false
                if(this.allSelected) return false
                return true
            },
            set(value) {} // do nothing
        },
        totalToFetch() {
            return this.queue.length
        },
        hasItems() {
            return this.items.length>0
        },
        searchDisabled() {
            return this.searching || String(this.query).trim().length<this.minimumQueryLength
        },
        revision() {
          return this.$store.getters['revisions/selected']
        },
        disabled() {
            try {
                if(!this.revision) return true
                if(this.queue.length<1) return true
                const active = this.$store.getters['revisions/isActive'](this.revision)
                const user = this.$store.state.user.info
                return !active || !user.hasValidToken()
            } catch (error) {
                return true
            }
        },
        /**
         * transform the queue items to be table compatible
         */
        queueItems() {
            return this.queue.map(mrn => ({mrn}))
        }
    },
    methods: {
        setPerPage(perPageOption) {
            this.perPage = perPageOption
        },
        notify(title, message) {
            this.$bvToast.toast(message, {
                title: 'Search results',
                autoHideDelay: 1000,
                appendToast: true
            })
        },
        async searchMrns() {
            let query = String(this.query)
            if(query.trim().length<this.minimumQueryLength) return
            try {
                this.searching = true
                let start = (this.currentPage-1) * this.perPage
                let limit = this.perPage
                const response = await this.$API.searchMrns({query, start, limit})
                const {data: {list=[], total=0}={}} = response
                this.total = total
                this.items = [...list]
            } catch (error) {
                console.log(error)
            }finally {
                this.searching = false
            }
        },
        /**
         * this is to have a consistent query in
         * pagination if the user updates the text without
         * performing a new search
         */
        async submitQuery() {
            this.currentPage = 1
            await this.searchMrns()
            const notificationTitle = 'Search results'
            if(this.total===0) this.notify(notificationTitle, `No results for '${this.query}'`)
            else this.notify(notificationTitle, `${this.total} match${this.total===1 ? '' : 'es'} found`)
        },
        resetQuery() {
            this.total = 0
            this.query = ''
            this.items = []
        },
        async fetchQueue() {
            this.results = []
            this.loading = true
            this.totalProcessed = 0
            for(let mrn of this.queue) {
                await this.fetchMrn(mrn)
            }
            this.loading = false
            this.currentMrn = ''
        },
        async fetchMrn(mrn) {
            try {
                const modal = this.$refs['modal-fetch']
                modal.show()
                this.currentMrn = mrn
                const { metadata: { id: revision_id } } = this.revision
                this.promise = this.$API.runRevision(revision_id, mrn)
                const response = await this.promise
                const {data={}} = response
                if(typeof data !== 'object') throw new Error(`Malformed data received: '${String(data)}'`)
                this.results.push({mrn, data})
                this.totalProcessed++
            } catch (error) {
                console.log(error)
                this.cancelFetch()
                this.showErrorModal(error)
            }
        },
        cancelFetch() {
            // the promise returned from fetchMrn is altered to include a cancel token
            const cancelToken = this.promise.cancelToken
            if(cancelToken) cancelToken.cancel('operation canceled by user')
            this.$refs['modal-fetch'].hide()
        },
        showErrorModal(response) {
            const { responseText, message='Error fetching data' } = response // get the error message from REDCap
            const errorMessage = responseText || message
            this.$bvModal.msgBoxOk(errorMessage, {
                title: 'Error',
                size: 'md',
                buttonSize: 'sm',
                headerClass: 'font-weight-bold',
                bodyClass: 'text-break',
                okVariant: 'secondary',
            })
        },
        addToQueue(value) {
            if(this.queue.indexOf(value)>=0) return
            if(this.queue.length>=this.maxQueueSize) {
                this.notify('Max queue size reached', `A maximum of ${this.maxQueueSize} values can be selected.`)
                return
            }
            this.queue.push(value)
        },
        removeFromQueue(value) {
            const index = this.queue.indexOf(value)
            if(index<0) return
            this.queue.splice(index, 1)
        },
        clearQueue() {
            this.queue = []
        },
        queueExists(value) {
            return this.queue.indexOf(value)>=0
        },
        toggleAll(checked) {
            const items = [...this.items]
            if(checked) {
                items.forEach(({mrn}) => {
                    const index = this.queue.indexOf(mrn)
                    if(index>=0) return
                    this.queue.push(mrn)
                })
            }else {
                items.forEach(({mrn}) => {
                    const index = this.queue.indexOf(mrn)
                    if(index<0) return
                    this.queue.splice(index, 1)
                })
            }
        }
    },
    watch: {
        /**
         * perform a new search when page is changed
         */
        currentPage: {
            immediate: true,
            handler() {
                this.searchMrns()
            }
        },
        perPage: {
            immediate: true,
            handler(current, previous) {
                if(current==previous) return
                this.searchMrns()
            }
        },
    }
}
</script>

<style scoped>
.queue-container {
    max-height: 400px;
    overflow-y: auto;
}
</style>