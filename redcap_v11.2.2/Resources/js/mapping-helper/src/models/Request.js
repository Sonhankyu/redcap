
export const methods = Object.freeze({
  GET: 'GET',
  POST: 'POST',
  PUT: 'PUT',
  DELETE: 'DELETE',
})

export default class Request
{
  #_url
  #_options
  #_method

  constructor(url, options=[], method=methods.GET) {
    this._url = url
    this._options = [...options]
    this._method = method
  }

  get url() {
    return this._url
  }

  get options() {
    return [...this._options]
  }

  get method() {
    return this._method
  }
}
