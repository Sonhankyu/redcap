import {default as axios, CancelToken} from 'axios'

// import {API_BASE_URL} from '@/config'

export default class API {
    route = 'CdpController' // route name for the CDP Mapping API
    actions = {} // api actions

    constructor({modules}) {
        
        this.loadModules(modules)
    }

    get baseURL() {
        const app_path_webroot = window.app_path_webroot || '/API/'
        let baseURL = `${app_path_webroot}`
        baseURL = baseURL.replace(/\/\/+/, '/')
        return baseURL
    } 

    createClient(cancelToken) {
        const redcap_params = this.getRedCapQueryParams()
        
        return axios.create({
            baseURL: this.baseURL,
            timeout: 60*1000,
            headers: {common: {'X-Requested-With': 'XMLHttpRequest'}}, // set header for REDCap ajax
            paramsSerializer: (params) => {
                params = Object.assign({}, redcap_params, params)
                const search_params =  new URLSearchParams(params)
                return search_params.toString()
            },
            cancelToken,
        })
    }

    /**
     * set project_id, page, module prefix
     * also set redcap_csrf_token if available
     */
    getRedCapQueryParams() {
        let params = new URLSearchParams(location.search)
        // get PID from current location
        let pid = params.get('pid')
        let query_params = {
            pid,
        }
        if(window.redcap_csrf_token) query_params.redcap_csrf_token = window.redcap_csrf_token // csrf token for post requests
        return query_params
    }

    dispatch(command)
    {
        const [name, action] = command.split('/')
        const params = [...arguments].slice(1)
        
        // create a cancel function and a cancelToken function
        const {token: cancelToken, cancel} = CancelToken.source()
        
        // set the context
        const context = {
            $api: this,
            api_client: this.createClient(cancelToken),
            route: this.route,
        }

        let promise = this.actions[name][action](context, ...params)
        promise.cancel = cancel // pass the cancel along with the promise
        return promise
    }

    /**
     * load action in the provided modules
     */
    loadModules(modules={}) {
        for(let [name, module={}] of Object.entries(modules)) {
            const {actions={}} = module
            this.actions[name] = {...actions}
        }
    }

    test(number=123) {
        return this.http_client.get(`test/${number}`)
    }
}