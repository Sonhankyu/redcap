import moment from 'moment'

const user_date_format_moment = 'MM-DD-YYYY'

export const formatDate = function(date, format=user_date_format_moment) {
    if(date.trim()=='') return date
    return moment(date).format(format)
}

export const humanReadableDate = function (string_date) {
    return moment(string_date).fromNow()
}

export const uuidv4 = function() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    })
}

export const delay = ms => new Promise(res => setTimeout(res, ms))

/* export const loadComponent = function(path) {
    return import(path)
} */

 /**
  * get a color based on a number
  * use with percentages
  * @param {number} value 
  */
 export const getColor = (value) => {
    //value from 0 to 1
    var hue=((1-value)*120).toString(10)
    return ["hsl(",hue,",100%,50%)"].join("")
}



export const compare = function(obj1, obj2, type=null) {
    switch (type) {
        case 'date':
            /**
             * get rid of unwanted parts of the date (hours, minutes, seconds...)
             * it's needed when coparing dates to check if the fields are dirty
             */
            const regex = /(\d{4}-\d{2}-\d{2}).*/gi
            obj1 = obj1.replace(regex, '$1')
            obj2 = obj2.replace(regex, '$1')
            break
        default:
            break
    }
    return JSON.stringify(obj1) === JSON.stringify(obj2)
}