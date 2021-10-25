<template>
    <div>
        <div class="form-inline">
            <select class="form-control ml-0" v-model="day_offset_plusminus">
                <option value="+-">±</option>
                <option value="+">+</option>
                <option value="-">-</option>
            </select>
            <input class="form-control ml-2" type="number" id="day-offset" :min="day_offset_min" :max="day_offset_max" :step="day_offset_min" v-model="day_offset" required/>
            <span class="ml-2">{{translations['days']}}</span>

            <input class="custom-range" type="range" :min="day_offset_min" :max="day_offset_max" :step="day_offset_min" v-model="day_offset"> 
        </div>
        <span>{{day_offset_text}}</span>
    </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
    data() {
        return {}
    },
    computed: {
    ...mapState({
            translations: state => state.app_settings.translations,
            day_offset_min: state => state.app_settings.day_offset_min,
            day_offset_max: state => state.app_settings.day_offset_max,
        }),
        day_offset: {
            get() { return this.$store.state.settings.realtime_webservice_offset_days },
            set(value) {
                this.$store.dispatch('settings/setOffsetDays', value)
            },
        },
        day_offset_plusminus: {
            get() { return this.$store.state.settings.realtime_webservice_offset_plusminus },
            set(value) {
                this.$store.dispatch('settings/setOffsetPlusminus', value)
            },
        },
        day_offset_text() {
            let text = this.translations.min_max_days
            if(!text) return
            const replacements = {
                min_days: this.day_offset_min,
                min_minutes: Math.ceil(60*24/100),
                max_days: this.day_offset_max,
            }
            for(let[key, value] of Object.entries(replacements)) {
                text = text.replace(`{{${key}}}`, value)
            }
            return text
        }
    },
}
</script>

<style>

</style>