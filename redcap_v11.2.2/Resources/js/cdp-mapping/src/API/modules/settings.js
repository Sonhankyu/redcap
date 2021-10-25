export default {
    actions: {        
        get(context) {
            const {route, api_client} = context
            var params = {
                route: `${route}:getSettings`,
            }
            return api_client.get('',{params})
        },
        set(context, config) {
            const {route, api_client} = context
            const data = new FormData()
            for (let [key, value] of Object.entries(config)) {
                data.append(key, JSON.stringify(value))
            }
            var params = {
                route: `${route}:setSettings`,
            }
            return api_client.post('',data, {params})
        },
        importMapping(context, file) {
            const {route, api_client} = context
            const data = new FormData()
            data.append('file', file)
            var params = {
                route: `${route}:importMapping`,
            }
            return api_client.post('',data, {params})
        },
        exportMapping(context) {
            const {route, api_client} = context
            const data = new FormData()
            var params = {
                route: `${route}:exportMapping`,
            }
            return api_client.post('',data, {params})
        },
        getInfoPage(context, service_type='fhir') {
            /* let json = import('@/assets/info-page')
            console.log(json)
            let promise = new Promise(resolve => {
                setTimeout(() => {
                    resolve({data: json})
                }, 1000)
            })
            return promise */
            service_type = service_type.toLowerCase()
            const { api_client } = context
            return api_client.get(`DynamicDataPull/info.php?type=${service_type}`)
        },
    }
}