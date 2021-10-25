import API from './API'
import settings from '@/API/modules/settings'

const api = new API({
    modules: {
        settings,
    }
})

export default api
