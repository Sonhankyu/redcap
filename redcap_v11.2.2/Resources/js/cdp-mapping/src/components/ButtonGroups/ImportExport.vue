<template>
<div>
    <b-button-group>
        <b-button variant="outline-primary" size="sm" @click="importClicked"><font-awesome-icon icon="file-import" /> Import</b-button>
        <b-button variant="outline-primary" size="sm" @click="exportClicked"><font-awesome-icon icon="file-export" /> Export</b-button>
    </b-button-group>
    <input type="file" ref="file" @change="onFileChanged" class="d-none">
    <b-modal id="download-modal" title="Mapping export" @ok="onDownloadOkClicked" ok-title="Download" button-size="sm">
        <div class="text-center">
            <h6 class="my-4">Your data is ready to be downloaded!</h6>
            <div class="d-flex flex-column align-content-center justify-content-center">
                <a :href="download_url" download class="mx-auto text-center text-primary">
                    <font-awesome-icon icon="file-csv" size="5x"/>
                    <span class="d-block">click here to download</span>
                </a>
            </div>
        </div>
    </b-modal>
</div>
</template>

<script>
import EventBus from '@/EventBus'

export default {
    data() {
        return {
            download_url: null,
        }
    },
    methods: {
        importClicked() {
            this.$refs.file.value = null
            this.$refs.file.click()
        },
        async onFileChanged(event) {
            const files = this.$refs.file.files
            if(files.length<1) {
                return
            }
            try {
                const file = files[0]
                const response = await this.$API.dispatch('settings/importMapping', file)
                const {data: {imported=[]}={}} = response
                const total_imported = imported.length
                const message = `Total imported mapping: ${total_imported}`
                const title = 'Import process completed'
                await this.$bvModal.msgBoxOk(message, {title})
                EventBus.$emit('settings:updated')
            } catch (error) {
                let error_message = ''
                if(typeof error==='string') {
                    error_message = error
                }else {
                    const {response:{data={}}={}} = error
                    error_message = data.message || 'error importing data'
                }
                const error_title = 'Import error'
                this.$bvModal.msgBoxOk(error_message, {title: error_title})
            }
        },
        async exportClicked() {
            this.download_url = null
            const response = await this.$API.dispatch('settings/exportMapping')
            const {data:{download_url=''}={}} = response
            this.download_url = download_url
            this.$bvModal.show('download-modal')
        },
        onDownloadOkClicked() {
            if(!this.download_url) return
            location.replace(this.download_url)
        }
    }
}
</script>

<style>

</style>