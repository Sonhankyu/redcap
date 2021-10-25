<script src="{{$app_path_js}}Libraries/alpine/alpine.js"></script>
<script src="{{$app_path_js}}Libraries/axios.min.js"></script>
<div class="settings-panel p-2 w-100 container-fluid" x-data="new AutoAdjudication()" x-init="init">
    logs
    <button @click="getLogs">get logs</button>
</div>
<script>
    // API client
    // https://redcap.test/redcap_v999.0.0/?route=DataMartController%3AgetUser&pid=14&redcap_csrf_token=e109dd3a9f9b91961028088743408dc0
    
    const Api = function() {
        let params = (new URL(document.location)).searchParams;
        let pid = params.get('pid'); //

        var api_client = axios.create({
            baseUrl: '/',
            // params,
            paramsSerializer: params => {
                const search_params =  new URLSearchParams()
                if(window.redcap_csrf_token) search_params.append('redcap_csrf_token',window.redcap_csrf_token) // csrf token for post requests
                if(pid) search_params.append('pid', pid) //inject pid
                for(let [key, value] of Object.entries(params)) {
                    search_params.append(key, value)
                }
                
                return search_params.toString()
            },
            headers: {common: {'X-Requested-With': 'XMLHttpRequest'}}
        })

        return {
            get api_client() {return api_client},
        }

    }
</script>
<script>
    var AutoAdjudication = function() {
        const api = new Api()
        const api_client = api.api_client

        return {
            init() {},
            getLogs() {
                const config = {
                    params: {
                        route: 'CdisController:getCdpAutoAdjudicationLogs',
                        _limit: 10,
                        _start: 0,
                    }
                }
                console.log(config)
                
                api_client.get('', config)
            },
        }
    }
</script>