/*eslint no-unused-vars: "off"*/
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

function Seatbelt(obj) {
    return new Proxy(obj, {
      get(target, key) {
        const accessedProperty = Reflect.get(target, key);
        if (typeof accessedProperty === 'object') {
          return Seatbelt(accessedProperty);
        } else {
          return accessedProperty ?
          accessedProperty :
          Undefined;
        }
      }
    });
  }

export {Seatbelt as default, Undefined}