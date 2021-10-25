export default {
    actions: {        
        get(context, identifier) {
            const {route, api_client} = context
            var params = {
                route: `${route}:getSettings`,
                record_identifier: identifier,
            }
            return api_client.get('',{params})
        },
    }
}