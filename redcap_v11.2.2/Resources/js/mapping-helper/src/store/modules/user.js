import moment from 'moment'
const initialState = {
    info: null,
    tokens: [],
}

const module = {
    namespaced: true,
    state: {...initialState},
    mutations: {
        SET_INFO: (state, info) => state.info = info ,
        SET_TOKENS: (state, list) =>  state.tokens = list ,
        SET: (state, payload) =>  {
            for(let [key, value] of Object.entries(payload)) state[key] = value
        },
    },
    actions: {
        setInfo(context, info) { context.commit('SET_INFO', info) },
        setTokens(context, tokens) { context.commit('SET_TOKENS', tokens) },
        set(context,{info, tokens}) {
            context.commit('SET', {info, tokens})
            return {info}
        },
    },
    getters: {
        anyValidToken: state => {
            const {tokens=[]} = state
            if(tokens.length<1) return false
            const now = moment()
            const anyValid = tokens.find(({expiration=''}) => {
                const expirationDate = moment(expiration)
                if(!expirationDate.isValid()) return false
                return expirationDate.isAfter(now)
            })
            return anyValid !== undefined
        }
    }
}

export default module;