const Undefined = new Proxy(function() {}, {
    get(target, key, receiver) {
      if (key === 'name') {
        return 'Undefined';
      }
  
      return Undefined;
    },
    apply() {
      return Undefined;
    },
  });
export default Undefined