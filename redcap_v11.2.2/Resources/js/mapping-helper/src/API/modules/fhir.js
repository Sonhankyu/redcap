export default {
    actions: {
        fetchResource(context, fhir_category, mrn, options) {
            const {route, api_client} = context
            var params = {
                route: `${route}:getResources`,
                fhir_category,
                mrn,
                options: JSON.stringify(options),
            }
            return api_client.get('',{params})
        },
        customRequest(context, relative_url, options) {
            const {route, api_client} = context
            var params = {
                route: `${route}:getFhirRequest`,
                relative_url,
                options: JSON.stringify(options),
            }
            return api_client.get('',{params})
        }
    }
}