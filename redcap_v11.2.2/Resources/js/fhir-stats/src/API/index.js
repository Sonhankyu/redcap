import API from './API'
import stats from '@/API/modules/stats'

const api = new API({
    modules: {
      stats,
    }
})

export default api
