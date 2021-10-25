import API from './API'
import ddp_records from '@/API/modules/ddp_records'

const api = new API({
    modules: {
      ddp_records,
    }
})

export default api
