import API from './API'
import settings from '@/API/modules/settings'
import fhir from '@/API/modules/fhir'

const api = new API({
    modules: {
      settings,
      fhir,
    }
})

export default api
