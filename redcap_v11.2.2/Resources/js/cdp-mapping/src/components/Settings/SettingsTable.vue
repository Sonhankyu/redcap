<template>
    <div>
<!--         preview_fields_title
preview_fields_description
    preview_fields_select_label
    preview_fields_select_up_to
    default_day_offset_title
    default_day_offset_description
    default_day_offset_label
    days
    min_max_days
    instant_adjudication_title
    instant_adjudication_description
    instant_adjudication_label
    instant_adjudication_total_selected_fields -->
  <table class="table table-bordered">
        <thead class="thead-light">
            <tr>
                <th colspan="2"><span>{{translations['settings_table_title']}}</span></th>
            </tr>
        </thead>

        <tbody>
            <!-- preview fields -->
            <tr>
                <td>
                    <h6>{{translations['preview_fields_title']}}</h6>
                    <p>{{translations['preview_fields_description']}}</p>
                </td>
                <td>
                    <label for="select-preview-field">
                        <span>{{translations['preview_fields_select_label']}}</span> <em>{{fhirSourceName}}</em> <span>{{translations['preview_fields_select_up_to']}}</span>
                    </label>

                    <PreviewFields />

                </td>
            </tr>

            <!-- day offset -->
            <tr>
                <td>
                    <h6>{{translations['default_day_offset_title']}}</h6>
                    <p>{{translations['default_day_offset_description']}}</p>
                </td>
                <td>
                    <label for="day-offset">{{translations['default_day_offset_label']}}</label>
                    <DaysOffset />
                </td>
            </tr>

            <!-- auto-adjudication -->
            <tr>
                <td>
                    <h6>{{translations['instant_adjudication_title']}}</h6>
                    <p>{{translations['instant_adjudication_description']}}</p>
                </td>
                <td>
                    <label class="mr-2" for="auto-adjudication">{{translations['instant_adjudication_label']}}</label>
                    <AutoAdjudication />
                </td>
            </tr>
      </tbody>
  </table>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import * as R from 'ramda'
import PreviewFields from '@/components/Settings/PreviewFields'
import DaysOffset from '@/components/Settings/DaysOffset'
import AutoAdjudication from '@/components/Settings/AutoAdjudication'

export default {
    components: { PreviewFields,DaysOffset,AutoAdjudication, },
    data() {
        return {
            auto_adjudication: false,
            preview_fields: [],
        }
    },
    computed: {
        ...mapState({
            translations: state => state.app_settings.translations,
            fhirSourceName: state => state.app_settings.fhir_source_name,
            project: state => state.app_settings.project,
            fhir_cdp_auto_adjudication_enabled: state => state.app_settings.project.fhir_cdp_auto_adjudication_enabled,
        }),
        ...mapGetters({
            project_preview_fields: 'settings/previewFields',
        }),
    },
    watch: {
        fhir_cdp_auto_adjudication_enabled: { immediate: true, handler(value) {this.auto_adjudication=Boolean(value)} },
        // group fields by category and store them in local data
        /* project_preview_fields: {
            immediate: true,
            handler(fhir_fields) {
                // group fields by category
                const groupByCategory = R.groupBy(field => field.category)
                let fields = groupByCategory(fhir_fields)
                this.preview_fields = fields
            }
        }, */
    }
}
</script>

<style scoped>
label {
    font-weight: bold;;
}
table {
    position: relative;
}
tr td:nth-child(2) {
    width: 50%;
}
</style>