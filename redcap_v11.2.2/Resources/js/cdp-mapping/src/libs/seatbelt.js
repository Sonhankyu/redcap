import Undefined from './Undefined'

function seatbelt(obj) {
    return new Proxy(obj, {
      get(target, key) {
        const accessedProperty = Reflect.get(target, key);
  
        if (typeof accessedProperty === 'object') {
          return seatbelt(accessedProperty);
        } else {
          return accessedProperty ?
          accessedProperty :
          Undefined;
        }
      }
    });
  }

export default seatbelt