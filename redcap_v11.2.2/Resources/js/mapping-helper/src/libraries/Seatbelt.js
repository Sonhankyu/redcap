export const Undefined = new Proxy(() => ({}), {
  get(target, key, receiver) {
    if(key === 'name') {
      return 'Undefined'
    }
    return Undefined
  },
  apply() {
    return Undefined
  }
})

const Seatbelt = (obj) => {
  return new Proxy(obj, {
    get(target, key) {
      const accessed_property = Reflect.get(target, key)
      if(typeof accessed_property === 'object') {
        return Seatbelt(accessed_property)
      }else {
        if(accessed_property == undefined) return Undefined
        return accessed_property
      }
    }
  })
}

export default Seatbelt