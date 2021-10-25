
<script>
import { BModal } from 'bootstrap-vue'

export default {
    extends: BModal,
    created() {
        this.setAsyncShow()
        this.setAsyncHide()
    },
    methods: {
        setAsyncShow() {
            let _show = this.show
            this.show = () => {
                let promise = new Promise((resolve, reject) => {
                    this.$once('shown', () => { resolve({event:'shown',modal: this}) })
                    _show()
                })
                return promise
            }
        },
        setAsyncHide() {
           let _hide = this.hide
            this.hide = () => {
                let promise = new Promise((resolve, reject) => {
                    this.$once('hidden', () => { resolve({event:'hidden',modal: this}) })
                    _hide()
                })
                return promise
            }
        }

    }
}
</script>

<style>

</style>