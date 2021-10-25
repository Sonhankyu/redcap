export default {
    actions: {        
        getPreview(context, identifier) {
            const {route, api_client} = context
            var params = {
                route: `${route}:getPreviewData`,
                record_identifier: identifier,
            }
            return api_client.get('',{params})
        },
        fetchData(context, identifier) {
            const {route, api_client} = context
            var params = {
                route: `${route}:fetchData`,
                record_identifier: identifier,
            }
            return api_client.get('',{params})
        },
        getDdpRecordsDataStats(context) {
            const {route, api_client} = context
            var params = {
                route: `${route}:getDdpRecordsDataStats`,
            }
            return api_client.get('',{params})
        },
        adjudicateCachedRecords(context, {background=false, send_feedback=false}) {
            const {route, api_client} = context
            const data = new FormData()
            data.append('background', background)
            data.append('send_feedback', send_feedback)
            var params = {
                route: `${route}:adjudicateCachedRecords`,
            }
            return api_client.post('', data, {params})
        },
        adjudicateCachedRecord(context, record_id) {
            const {route, api_client} = context
            const data = new FormData()
            data.append('record_id', record_id)
            var params = {
                route: `${route}:adjudicateCachedRecord`,
            }
            return api_client.post('', data, {params})
        },
    }
}