import moment from 'moment'
import {date_format} from '@/variables'

const downloadBlob = (text, filename) => {
  const url = window.URL.createObjectURL(new Blob([text]))
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', filename)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const downloadDataURI = (text, filename='export.txt') => { 
  var element = document.createElement('a');
  element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
  element.setAttribute('download', filename);

  element.style.display = 'none';
  document.body.appendChild(element);

  element.click();

  document.body.removeChild(element);
}

const formatDate = (date) => {
  if(!date) return ''
  const date_string = moment(date).format(date_format) // date_format defined in variables
  return date_string
}

  export {downloadBlob, downloadDataURI, formatDate}