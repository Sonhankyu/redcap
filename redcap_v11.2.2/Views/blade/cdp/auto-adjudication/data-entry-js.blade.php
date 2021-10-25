<!-- <script type='text/javascript' src='{{$app_path_js}}DataEntryCDPAutoAdjudicationListener.js'></script> -->

<div class="modal fade" id="fetch-modal" tabindex="-1" role="dialog" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">__Title__</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        __Body__
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary modal-cancel-button" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary modal-ok-button">Fetch</button>
      </div>
    </div>
  </div>
</div>


<script src="<?php print APP_PATH_JS; ?>vue.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen,print" href="<?php print APP_PATH_JS; ?>cdp-preview/dist/cdp_preview.css"/>
<script type="text/javascript" src="<?php print APP_PATH_JS; ?>cdp-preview/dist/cdp_preview.umd.min.js" defer></script>
<div id="cdp-preview-container"></div>
<script>
	
</script>

<script>
  (function(Vue, window, document, $) {
    // variables
    const app_path_webroot = '{{$app_path_webroot}}'
    const app_path_webroot_full = '{{$app_path_webroot_full}}'
    const ajax_url = '{{$ajax_url}}'
    const redcap_csrf_token = window.redcap_csrf_token || ''
    const project_id = '{{$project_id}}'
    const event_id = '{{$event_id}}'
    const record_id = '{{$record_id}}'
    const record_identifier_field = '{{$record_identifier_field}}'
    
    
    window.addEventListener('DOMContentLoaded', function(event) {
      const identifier_element = document.querySelector('#questiontable input[name="{{$record_identifier_field}}"]')
      const cdp_preview_app = new Vue(cdp_preview).$mount('#cdp-preview-container')
      cdp_preview_app.observeElement(identifier_element)
    })


    /**
     * class that manages the modal
     */
    const PreviewFetchModal = function() {
      const modal_element = document.querySelector('#fetch-modal')
      const title_element = modal_element.querySelector('.modal-title')
      const body_element = modal_element.querySelector('.modal-body')
      const ok_element = modal_element.querySelector('.modal-ok-button')
      const cancel_element = modal_element.querySelector('.modal-cancel-button')
      
      // promise
      let promise, _resolve, _reject = null
      
      // private variable for the ok callback

      const _prototype = {
        setTitle(html) { title_element.innerHTML = html },
        setBody(html) { body_element.innerHTML = html },
        setOk(html) { ok_element.innerHTML = html },
        setOkVisibility(visible) { ok_element.style.display = visible ? 'block' : 'none' },
        setCancelVisibility(visible) { cancel_element.style.display = visible ? 'block' : 'none' },
        setCancel(html) { cancel_element.innerHTML = html },
        hide() { $(modal_element).modal('hide') },
        // reset defaults
        reset() {
          // store the resolve and reject locally
          promise = new Promise((resolve, reject) => {
            _resolve = resolve
            _reject = reject
          })
          ok_element.removeEventListener('click', this.onOkClicked)
          ok_element.addEventListener('click', this.onOkClicked, )
          this.setOkVisibility(true)
          this.setCancelVisibility(true)
          this.setCancel('Cancel')
          this.setOk('Ok')
          this.setTitle('Notice')
          this.setBody('')
          // this.setCallback(null)
        },
        onOkClicked(event) {
          $(modal_element).one('hidden.bs.modal', _resolve)
          this.hide()
        },

        showPreview(identifier, preview_data) {
          this.reset()
          this.setTitle("Fetching data from EHR system")
          const body_text = [
            'REDCap has fetched peview data for this record.',
            'Review the preview data below to ensure that the values match the record you wish to pull from the external source system.',
            'If they are correct, click the "Fetch" button to save this value and automatically begin fetching data from the source system.',
            'If the data displayed below is not correct for the value you entered, click "Cancel" to change the value.',
          ].join("\n")
          const createPreviewNodes = (data) => {
            let container = document.createElement('div')
            container.classList.add('my-2')
            data.forEach(element => {
              let div = document.createElement('div')
              div.innerHTML = `<b>${element.field}</b>: ${element.value}`
              container.appendChild(div)
            })
            body_element.appendChild(container)
          }
          this.setBody(body_text)
          createPreviewNodes(preview_data)
          this.setCancel('Cancel')
          this.setOk('Fetch')
          $(modal_element).modal('show')
          console.log(identifier)
          return promise
        },
        showError(error) {
          this.reset()
          this.setTitle("Error")
          this.setBody(error)
          this.setCancel('Ok')
          this.setOkVisibility(false)
          $(modal_element).modal('show')
          return promise
        }
      }
      // bind all public methods and variables to the class
      for(let[key,value] of Object.entries(_prototype)) this[key] = value.bind(this)
    }

    /**
     * class responsible for fetching data
     */
    const DataFetcher = function() {
      const default_headers = {
        "accept": "application/json",
        "x-requested-with": "XMLHttpRequest"
      }
      // utility to detect fetch response error
      const handleResponse = async function(response) {
        let data = null
        if(typeof response.json==='function') data = await response.json()
        if(response.ok) {
          return data
        }else {
          throw new Error(data)
        }
      }
      const _prototype = {
        async getPreviewData(ajax_url, record_identifier) {
            try {

              const params =  {
                redcap_csrf_token,
                route: 'CdpController:getPreviewData',
                pid: project_id,
                record_identifier,
              }
              const url = new URL(`${ajax_url}`)
              for(let[key, value] of Object.entries(params)) {
                url.searchParams.append(key, value)
              }

              const response = await fetch(url, {
                "headers": default_headers,
                "body": null,
                "method": "GET",
              })
              
              const data = await handleResponse(response)
              return data
            } catch (error) {
              console.error('Error:', error)
              throw error
            }
          },
        }
        for (let[key, value] of Object.entries(_prototype)) this[key] = value.bind(this)
    }

    /**
     * this class listens for changes on the field containing the record identifier 
     */
    const FHIRFetchListener = function(field_name, callback) {
      const _prototype = {
        setChangeEventListener(field_name, callback) {
          const element = document.querySelector(`#questiontable input[name="${field_name}"]`)
          if(!element) return
          element.addEventListener('change', event => {
            const identifier = element.value
            if(typeof callback==='function') {
              callback(identifier)
            }
          })
        },
      }
      for (let[key, value] of Object.entries(_prototype)) this[key] = value.bind(this)
      // set the listener and the callback
      this.setChangeEventListener(field_name, callback)
    }
    
    

    // define the callback to trigger whenever the record identifier field changes
    const listener_callback = async function(record_identifier) {
      try {
        const data = await data_fetcher.getPreviewData(ajax_url, record_identifier)
        const closed = await preview_fetch_modal.showPreview(record_identifier, data)
        // alert('closed')
      } catch (error) {
        const closed = await preview_fetch_modal.showError(error)
      }
    }
    // instantiate the classes
    // const preview_fetch_modal = new PreviewFetchModal()
    // const fetch_listener = new FHIRFetchListener(record_identifier_field, listener_callback)
    // const data_fetcher = new DataFetcher()
    

  }(Vue, window, document, jQuery))
</script>
