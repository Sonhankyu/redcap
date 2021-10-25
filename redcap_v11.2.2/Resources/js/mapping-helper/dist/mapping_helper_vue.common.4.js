((typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] = (typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] || []).push([[4],{

/***/ "10ad":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var global = __webpack_require__("7726");
var each = __webpack_require__("0a49")(0);
var redefine = __webpack_require__("2aba");
var meta = __webpack_require__("67ab");
var assign = __webpack_require__("73334");
var weak = __webpack_require__("643e");
var isObject = __webpack_require__("d3f4");
var validate = __webpack_require__("b39a");
var NATIVE_WEAK_MAP = __webpack_require__("b39a");
var IS_IE11 = !global.ActiveXObject && 'ActiveXObject' in global;
var WEAK_MAP = 'WeakMap';
var getWeak = meta.getWeak;
var isExtensible = Object.isExtensible;
var uncaughtFrozenStore = weak.ufstore;
var InternalMap;

var wrapper = function (get) {
  return function WeakMap() {
    return get(this, arguments.length > 0 ? arguments[0] : undefined);
  };
};

var methods = {
  // 23.3.3.3 WeakMap.prototype.get(key)
  get: function get(key) {
    if (isObject(key)) {
      var data = getWeak(key);
      if (data === true) return uncaughtFrozenStore(validate(this, WEAK_MAP)).get(key);
      return data ? data[this._i] : undefined;
    }
  },
  // 23.3.3.5 WeakMap.prototype.set(key, value)
  set: function set(key, value) {
    return weak.def(validate(this, WEAK_MAP), key, value);
  }
};

// 23.3 WeakMap Objects
var $WeakMap = module.exports = __webpack_require__("e0b8")(WEAK_MAP, wrapper, methods, weak, true, true);

// IE11 WeakMap frozen keys fix
if (NATIVE_WEAK_MAP && IS_IE11) {
  InternalMap = weak.getConstructor(wrapper, WEAK_MAP);
  assign(InternalMap.prototype, methods);
  meta.NEED = true;
  each(['delete', 'has', 'get', 'set'], function (key) {
    var proto = $WeakMap.prototype;
    var method = proto[key];
    redefine(proto, key, function (a, b) {
      // store frozen objects on internal weakmap shim
      if (isObject(a) && !isExtensible(a)) {
        if (!this._f) this._f = new InternalMap();
        var result = this._f[key](a, b);
        return key == 'set' ? this : result;
      // store all the rest on native weakmap
      } return method.call(this, a, b);
    });
  });
}


/***/ }),

/***/ "1ada":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/ResourceInfo.vue?vue&type=template&id=dc220a5a&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('span',[_vm._v(_vm._s(_vm.description))])])}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/components/endpoints/ResourceInfo.vue?vue&type=template&id=dc220a5a&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/ResourceInfo.vue?vue&type=script&lang=js&
//
//
//
//
//
//
/* harmony default export */ var ResourceInfovue_type_script_lang_js_ = ({
  props: {
    description: {
      type: String,
      default: ''
    }
  }
});
// CONCATENATED MODULE: ./src/components/endpoints/ResourceInfo.vue?vue&type=script&lang=js&
 /* harmony default export */ var endpoints_ResourceInfovue_type_script_lang_js_ = (ResourceInfovue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/endpoints/ResourceInfo.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  endpoints_ResourceInfovue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ResourceInfo = __webpack_exports__["a"] = (component.exports);

/***/ }),

/***/ "1fa8":
/***/ (function(module, exports, __webpack_require__) {

// call something on iterator step with safe closing on error
var anObject = __webpack_require__("cb7c");
module.exports = function (iterator, fn, value, entries) {
  try {
    return entries ? fn(anObject(value)[0], value[1]) : fn(value);
  // 7.4.6 IteratorClose(iterator, completion)
  } catch (e) {
    var ret = iterator['return'];
    if (ret !== undefined) anObject(ret.call(iterator));
    throw e;
  }
};


/***/ }),

/***/ "210c":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "27ee":
/***/ (function(module, exports, __webpack_require__) {

var classof = __webpack_require__("23c6");
var ITERATOR = __webpack_require__("2b4c")('iterator');
var Iterators = __webpack_require__("84f2");
module.exports = __webpack_require__("8378").getIteratorMethod = function (it) {
  if (it != undefined) return it[ITERATOR]
    || it['@@iterator']
    || Iterators[classof(it)];
};


/***/ }),

/***/ "2f12":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/forms/BaseResourceForm.vue?vue&type=template&id=0f9297b0&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_vm._t("aside")],2)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/components/endpoints/forms/BaseResourceForm.vue?vue&type=template&id=0f9297b0&

// EXTERNAL MODULE: ./src/variables.js
var variables = __webpack_require__("7eac");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/forms/BaseResourceForm.vue?vue&type=script&lang=js&
//
//
//
//
//
//

/* harmony default export */ var BaseResourceFormvue_type_script_lang_js_ = ({
  data: function data() {
    return {
      fhir_category: null,
      //base URL for the FHIR resource
      options: {}
      /* fields: [
        {
          key: 'last_name',
          sortable: true
        },
        {
          key: 'first_name',
          sortable: false
        },
        {
          key: 'age',
          label: 'Person age',
          sortable: true,
          // Variant applies to the whole column, including the header and footer
          variant: 'danger'
        }
      ], */

    };
  },
  methods: {
    getParams: function getParams() {
      var fhir_category = this.fhir_category;
      var options = this.options;
      return {
        fhir_category: fhir_category,
        options: options
      };
    },
    isValid: function isValid() {}
  },
  watch: {
    $v: {
      immediate: true,
      handler: function handler(value) {
        this.$emit('validation_changed', value);
      }
    }
  },
  validations: function validations() {
    return {};
  }
});
// CONCATENATED MODULE: ./src/components/endpoints/forms/BaseResourceForm.vue?vue&type=script&lang=js&
 /* harmony default export */ var forms_BaseResourceFormvue_type_script_lang_js_ = (BaseResourceFormvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/endpoints/forms/BaseResourceForm.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  forms_BaseResourceFormvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var BaseResourceForm = __webpack_exports__["a"] = (component.exports);

/***/ }),

/***/ "33a4":
/***/ (function(module, exports, __webpack_require__) {

// check on default Array iterator
var Iterators = __webpack_require__("84f2");
var ITERATOR = __webpack_require__("2b4c")('iterator');
var ArrayProto = Array.prototype;

module.exports = function (it) {
  return it !== undefined && (Iterators.Array === it || ArrayProto[ITERATOR] === it);
};


/***/ }),

/***/ "3aed":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_StorageInput_vue_vue_type_style_index_0_id_20d60c2a_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("a07e");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_StorageInput_vue_vue_type_style_index_0_id_20d60c2a_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_StorageInput_vue_vue_type_style_index_0_id_20d60c2a_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */
 /* unused harmony default export */ var _unused_webpack_default_export = (_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_StorageInput_vue_vue_type_style_index_0_id_20d60c2a_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "487d":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CustomForm_vue_vue_type_style_index_0_id_72f83aa9_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("90ad");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CustomForm_vue_vue_type_style_index_0_id_72f83aa9_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CustomForm_vue_vue_type_style_index_0_id_72f83aa9_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */
 /* unused harmony default export */ var _unused_webpack_default_export = (_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CustomForm_vue_vue_type_style_index_0_id_72f83aa9_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "4a59":
/***/ (function(module, exports, __webpack_require__) {

var ctx = __webpack_require__("9b43");
var call = __webpack_require__("1fa8");
var isArrayIter = __webpack_require__("33a4");
var anObject = __webpack_require__("cb7c");
var toLength = __webpack_require__("9def");
var getIterFn = __webpack_require__("27ee");
var BREAK = {};
var RETURN = {};
var exports = module.exports = function (iterable, entries, fn, that, ITERATOR) {
  var iterFn = ITERATOR ? function () { return iterable; } : getIterFn(iterable);
  var f = ctx(fn, that, entries ? 2 : 1);
  var index = 0;
  var length, step, iterator, result;
  if (typeof iterFn != 'function') throw TypeError(iterable + ' is not iterable!');
  // fast case for arrays with default iterator
  if (isArrayIter(iterFn)) for (length = toLength(iterable.length); length > index; index++) {
    result = entries ? f(anObject(step = iterable[index])[0], step[1]) : f(iterable[index]);
    if (result === BREAK || result === RETURN) return result;
  } else for (iterator = iterFn.call(iterable); !(step = iterator.next()).done;) {
    result = call(iterator, f, step.value, entries);
    if (result === BREAK || result === RETURN) return result;
  }
};
exports.BREAK = BREAK;
exports.RETURN = RETURN;


/***/ }),

/***/ "5062":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Payload/PayloadPanel.vue?vue&type=template&id=e3945ed0&scoped=true&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('b-alert',{attrs:{"show":_vm.payload!==null,"variant":"light"}},[_c('h6',[_vm._v("Raw response")]),_c('div',[_c('b-button',{attrs:{"size":"sm","variant":"secondary"},on:{"click":function($event){_vm.show=!_vm.show}}},[(_vm.show)?_c('font-awesome-icon',{staticClass:"icon",attrs:{"icon":['fas', 'chevron-down'],"fixed-width":""}}):_c('font-awesome-icon',{staticClass:"icon",attrs:{"icon":['fas', 'chevron-right'],"fixed-width":""}}),_c('span',{staticClass:"ml-2"},[_vm._v("Show")])],1),_c('b-button',{staticClass:"ml-2",attrs:{"size":"sm","variant":"primary"},on:{"click":_vm.onDownloadClicked}},[_c('font-awesome-icon',{staticClass:"icon",attrs:{"icon":['fas', 'download'],"fixed-width":""}}),_c('span',{staticClass:"ml-2"},[_vm._v("Download")])],1)],1),_c('Node',{directives:[{name:"show",rawName:"v-show",value:(_vm.show),expression:"show"}],staticClass:"raw-response",attrs:{"payload":_vm.payload}})],1),_c('b-modal',{ref:"save-modal",attrs:{"title":"Download response"},scopedSlots:_vm._u([{key:"modal-footer",fn:function(ref){
var cancel = ref.cancel;
return [_c('b-button',{attrs:{"size":"sm","variant":"secondary"},on:{"click":function($event){return cancel()}}},[_vm._v("\n        Cancel\n      ")]),_c('b-button',{attrs:{"size":"sm","variant":"success","disabled":!_vm.filename},on:{"click":_vm.onSaveOkClicked}},[_c('font-awesome-icon',{attrs:{"icon":['fas', 'save'],"fixed-width":""}}),_c('span',{staticClass:"ml-2"},[_vm._v("Save")])],1)]}}])},[_c('div',[_c('b-input',{attrs:{"placeholder":"enter a name...","autofocus":""},on:{"keyup":function($event){if(!$event.type.indexOf('key')&&_vm._k($event.keyCode,"enter",13,$event.key,"Enter")){ return null; }return _vm.onSaveOkClicked($event)}},model:{value:(_vm.filename),callback:function ($$v) {_vm.filename=(typeof $$v === 'string'? $$v.trim(): $$v)},expression:"filename"}})],1)])],1)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/components/Payload/PayloadPanel.vue?vue&type=template&id=e3945ed0&scoped=true&

// EXTERNAL MODULE: ./src/components/Payload/Node.vue + 4 modules
var Node = __webpack_require__("6fc5");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Payload/PayloadPanel.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var PayloadPanelvue_type_script_lang_js_ = ({
  components: {
    Node: Node["a" /* default */]
  },
  data: function data() {
    return {
      filename: '',
      show: false
    };
  },
  props: {
    payload: {
      type: [Object, Array],
      default: function _default() {
        return {};
      }
    }
  },
  methods: {
    onDownloadClicked: function onDownloadClicked() {
      var save_modal = this.$refs['save-modal'];
      if (!save_modal) return;
      this.filename = '';
      save_modal.show();
    },
    onSaveOkClicked: function onSaveOkClicked() {
      if (this.filename == '') return;
      this.download(JSON.stringify(this.payload, null, 2), this.filename);
      var save_modal = this.$refs['save-modal'];
      if (!save_modal) return;
      save_modal.hide();
    },
    download: function download(text) {
      var filename = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'export.txt';
      var element = document.createElement('a');
      element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
      element.setAttribute('download', filename);
      element.style.display = 'none';
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);
    }
  }
});
// CONCATENATED MODULE: ./src/components/Payload/PayloadPanel.vue?vue&type=script&lang=js&
 /* harmony default export */ var Payload_PayloadPanelvue_type_script_lang_js_ = (PayloadPanelvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./src/components/Payload/PayloadPanel.vue?vue&type=style&index=0&id=e3945ed0&scoped=true&lang=css&
var PayloadPanelvue_type_style_index_0_id_e3945ed0_scoped_true_lang_css_ = __webpack_require__("aa6c");

// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/Payload/PayloadPanel.vue






/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  Payload_PayloadPanelvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  "e3945ed0",
  null
  
)

/* harmony default export */ var PayloadPanel = __webpack_exports__["a"] = (component.exports);

/***/ }),

/***/ "5846":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "5cc5":
/***/ (function(module, exports, __webpack_require__) {

var ITERATOR = __webpack_require__("2b4c")('iterator');
var SAFE_CLOSING = false;

try {
  var riter = [7][ITERATOR]();
  riter['return'] = function () { SAFE_CLOSING = true; };
  // eslint-disable-next-line no-throw-literal
  Array.from(riter, function () { throw 2; });
} catch (e) { /* empty */ }

module.exports = function (exec, skipClosing) {
  if (!skipClosing && !SAFE_CLOSING) return false;
  var safe = false;
  try {
    var arr = [7];
    var iter = arr[ITERATOR]();
    iter.next = function () { return { done: safe = true }; };
    arr[ITERATOR] = function () { return iter; };
    exec(arr);
  } catch (e) { /* empty */ }
  return safe;
};


/***/ }),

/***/ "5d58":
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__("d8d6");

/***/ }),

/***/ "5dbc":
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__("d3f4");
var setPrototypeOf = __webpack_require__("8b97").set;
module.exports = function (that, target, C) {
  var S = target.constructor;
  var P;
  if (S !== C && typeof S == 'function' && (P = S.prototype) !== C.prototype && isObject(P) && setPrototypeOf) {
    setPrototypeOf(that, P);
  } return that;
};


/***/ }),

/***/ "5df3":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var $at = __webpack_require__("02f4")(true);

// 21.1.3.27 String.prototype[@@iterator]()
__webpack_require__("01f9")(String, 'String', function (iterated) {
  this._t = String(iterated); // target
  this._i = 0;                // next index
// 21.1.5.2.1 %StringIteratorPrototype%.next()
}, function () {
  var O = this._t;
  var index = this._i;
  var point;
  if (index >= O.length) return { value: undefined, done: true };
  point = $at(O, index);
  this._i += point.length;
  return { value: point, done: false };
});


/***/ }),

/***/ "643e":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var redefineAll = __webpack_require__("dcbc");
var getWeak = __webpack_require__("67ab").getWeak;
var anObject = __webpack_require__("cb7c");
var isObject = __webpack_require__("d3f4");
var anInstance = __webpack_require__("f605");
var forOf = __webpack_require__("4a59");
var createArrayMethod = __webpack_require__("0a49");
var $has = __webpack_require__("69a8");
var validate = __webpack_require__("b39a");
var arrayFind = createArrayMethod(5);
var arrayFindIndex = createArrayMethod(6);
var id = 0;

// fallback for uncaught frozen keys
var uncaughtFrozenStore = function (that) {
  return that._l || (that._l = new UncaughtFrozenStore());
};
var UncaughtFrozenStore = function () {
  this.a = [];
};
var findUncaughtFrozen = function (store, key) {
  return arrayFind(store.a, function (it) {
    return it[0] === key;
  });
};
UncaughtFrozenStore.prototype = {
  get: function (key) {
    var entry = findUncaughtFrozen(this, key);
    if (entry) return entry[1];
  },
  has: function (key) {
    return !!findUncaughtFrozen(this, key);
  },
  set: function (key, value) {
    var entry = findUncaughtFrozen(this, key);
    if (entry) entry[1] = value;
    else this.a.push([key, value]);
  },
  'delete': function (key) {
    var index = arrayFindIndex(this.a, function (it) {
      return it[0] === key;
    });
    if (~index) this.a.splice(index, 1);
    return !!~index;
  }
};

module.exports = {
  getConstructor: function (wrapper, NAME, IS_MAP, ADDER) {
    var C = wrapper(function (that, iterable) {
      anInstance(that, C, NAME, '_i');
      that._t = NAME;      // collection type
      that._i = id++;      // collection id
      that._l = undefined; // leak store for uncaught frozen objects
      if (iterable != undefined) forOf(iterable, IS_MAP, that[ADDER], that);
    });
    redefineAll(C.prototype, {
      // 23.3.3.2 WeakMap.prototype.delete(key)
      // 23.4.3.3 WeakSet.prototype.delete(value)
      'delete': function (key) {
        if (!isObject(key)) return false;
        var data = getWeak(key);
        if (data === true) return uncaughtFrozenStore(validate(this, NAME))['delete'](key);
        return data && $has(data, this._i) && delete data[this._i];
      },
      // 23.3.3.4 WeakMap.prototype.has(key)
      // 23.4.3.4 WeakSet.prototype.has(value)
      has: function has(key) {
        if (!isObject(key)) return false;
        var data = getWeak(key);
        if (data === true) return uncaughtFrozenStore(validate(this, NAME)).has(key);
        return data && $has(data, this._i);
      }
    });
    return C;
  },
  def: function (that, key, value) {
    var data = getWeak(anObject(key), true);
    if (data === true) uncaughtFrozenStore(that).set(key, value);
    else data[that._i] = value;
    return that;
  },
  ufstore: uncaughtFrozenStore
};


/***/ }),

/***/ "68f9":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* unused harmony export Undefined */
/* harmony import */ var _Users_delacqf_code_redcap_html_redcap_v999_0_0_Resources_js_mapping_helper_node_modules_babel_runtime_corejs2_helpers_esm_typeof__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("7618");
/* harmony import */ var core_js_modules_es6_reflect_get__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__("d185");
/* harmony import */ var core_js_modules_es6_reflect_get__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es6_reflect_get__WEBPACK_IMPORTED_MODULE_1__);


var Undefined = new Proxy(function () {
  return {};
}, {
  get: function get(target, key, receiver) {
    if (key === 'name') {
      return 'Undefined';
    }

    return Undefined;
  },
  apply: function apply() {
    return Undefined;
  }
});

var Seatbelt = function Seatbelt(obj) {
  return new Proxy(obj, {
    get: function get(target, key) {
      var accessed_property = Reflect.get(target, key);

      if (Object(_Users_delacqf_code_redcap_html_redcap_v999_0_0_Resources_js_mapping_helper_node_modules_babel_runtime_corejs2_helpers_esm_typeof__WEBPACK_IMPORTED_MODULE_0__[/* default */ "a"])(accessed_property) === 'object') {
        return Seatbelt(accessed_property);
      } else {
        if (accessed_property == undefined) return Undefined;
        return accessed_property;
      }
    }
  });
};

/* harmony default export */ __webpack_exports__["a"] = (Seatbelt);

/***/ }),

/***/ "6fc5":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Payload/Node.vue?vue&type=template&id=6adeaa6d&scoped=true&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"node"},[(_vm.isArray(_vm.payload))?_c('span',{staticClass:"parenthesis open",domProps:{"textContent":_vm._s("[")}}):_vm._e(),(_vm.isObject(_vm.payload))?_c('span',{staticClass:"parenthesis open",domProps:{"textContent":_vm._s("{")}}):_vm._e(),_c('span',{staticClass:"expander text-muted",on:{"click":function($event){$event.stopPropagation();return _vm.onClick($event)}}},[(_vm.open)?_c('font-awesome-icon',{attrs:{"icon":['fas', 'chevron-circle-down']}}):_c('font-awesome-icon',{attrs:{"icon":['fas', 'chevron-circle-right']}})],1),(_vm.open)?_vm._l((_vm.payload),function(value,key){return _c('span',{key:key,staticClass:"child d-block ml-2"},[(!_vm.isArray(_vm.payload))?_c('span',{staticClass:"mr-2 font-weight-bold"},[_vm._v("\""+_vm._s(key)+"\":")]):_vm._e(),(_vm.isLeaf(value))?_c('span',{class:_vm.getClass(value),domProps:{"textContent":_vm._s(_vm.print(value))}}):_c('Node',{attrs:{"payload":value}}),(!_vm.isLast(key))?_c('span',[_vm._v(",")]):_vm._e()],1)}):_vm._e(),(_vm.isArray(_vm.payload))?_c('span',{staticClass:"parenthesis closed",domProps:{"textContent":_vm._s("]")}}):_vm._e(),(_vm.isObject(_vm.payload))?_c('span',{staticClass:"parenthesis closed",domProps:{"textContent":_vm._s("}")}}):_vm._e()],2)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/components/Payload/Node.vue?vue&type=template&id=6adeaa6d&scoped=true&

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/toConsumableArray.js + 3 modules
var toConsumableArray = __webpack_require__("75fc");

// EXTERNAL MODULE: ./node_modules/core-js/modules/web.dom.iterable.js
var web_dom_iterable = __webpack_require__("ac6a");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.array.iterator.js
var es6_array_iterator = __webpack_require__("cadf");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.object.keys.js
var es6_object_keys = __webpack_require__("456d");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/typeof.js
var esm_typeof = __webpack_require__("7618");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/Payload/Node.vue?vue&type=script&lang=js&





//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ var Nodevue_type_script_lang_js_ = ({
  name: 'Node',
  data: function data() {
    return {
      open: true
    };
  },
  components: {
    Node: Node
  },
  props: {
    payload: {
      type: [Object, Array],
      default: function _default() {
        return {};
      }
    }
  },
  methods: {
    isLeaf: function isLeaf(item) {
      return !(this.isObject(item) || this.isArray(item));
    },
    isObject: function isObject(item) {
      return Object(esm_typeof["a" /* default */])(item) === 'object' && !Array.isArray(item);
    },
    isArray: function isArray(item) {
      return Array.isArray(item);
    },
    isLast: function isLast(key) {
      if (this.isObject(this.payload)) {
        var keys = Object.keys(this.payload);
        return keys.slice(-1)[0] === key;
      } else if (this.isArray(this.payload)) {
        return key === Object(toConsumableArray["a" /* default */])(this.payload).length - 1;
      }

      return false;
    },
    print: function print(value) {
      if (typeof value === 'string') return "\"".concat(value, "\"");else return value;
    },
    getClass: function getClass(value) {
      var type = Object(esm_typeof["a" /* default */])(value);

      return type;
    },
    onClick: function onClick() {
      this.open = !this.open;
    }
  }
});
// CONCATENATED MODULE: ./src/components/Payload/Node.vue?vue&type=script&lang=js&
 /* harmony default export */ var Payload_Nodevue_type_script_lang_js_ = (Nodevue_type_script_lang_js_); 
// EXTERNAL MODULE: ./src/components/Payload/Node.vue?vue&type=style&index=0&id=6adeaa6d&scoped=true&lang=css&
var Nodevue_type_style_index_0_id_6adeaa6d_scoped_true_lang_css_ = __webpack_require__("8b67");

// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/Payload/Node.vue






/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  Payload_Nodevue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  "6adeaa6d",
  null
  
)

/* harmony default export */ var Payload_Node = __webpack_exports__["a"] = (component.exports);

/***/ }),

/***/ "75fc":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// EXPORTS
__webpack_require__.d(__webpack_exports__, "a", function() { return /* binding */ _toConsumableArray; });

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/core-js/array/is-array.js
var is_array = __webpack_require__("a745");
var is_array_default = /*#__PURE__*/__webpack_require__.n(is_array);

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/arrayLikeToArray.js
var arrayLikeToArray = __webpack_require__("db2a");

// CONCATENATED MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/arrayWithoutHoles.js


function _arrayWithoutHoles(arr) {
  if (is_array_default()(arr)) return Object(arrayLikeToArray["a" /* default */])(arr);
}
// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/core-js/array/from.js
var from = __webpack_require__("774e");
var from_default = /*#__PURE__*/__webpack_require__.n(from);

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/core-js/is-iterable.js
var is_iterable = __webpack_require__("c8bb");
var is_iterable_default = /*#__PURE__*/__webpack_require__.n(is_iterable);

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/core-js/symbol.js
var symbol = __webpack_require__("67bb");
var symbol_default = /*#__PURE__*/__webpack_require__.n(symbol);

// CONCATENATED MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/iterableToArray.js



function _iterableToArray(iter) {
  if (typeof symbol_default.a !== "undefined" && is_iterable_default()(Object(iter))) return from_default()(iter);
}
// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/unsupportedIterableToArray.js
var unsupportedIterableToArray = __webpack_require__("e630");

// CONCATENATED MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/nonIterableSpread.js
function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}
// CONCATENATED MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/toConsumableArray.js




function _toConsumableArray(arr) {
  return _arrayWithoutHoles(arr) || _iterableToArray(arr) || Object(unsupportedIterableToArray["a" /* default */])(arr) || _nonIterableSpread();
}

/***/ }),

/***/ "7618":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return _typeof; });
/* harmony import */ var _core_js_symbol_iterator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("5d58");
/* harmony import */ var _core_js_symbol_iterator__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_core_js_symbol_iterator__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _core_js_symbol__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__("67bb");
/* harmony import */ var _core_js_symbol__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_core_js_symbol__WEBPACK_IMPORTED_MODULE_1__);


function _typeof(obj) {
  "@babel/helpers - typeof";

  if (typeof _core_js_symbol__WEBPACK_IMPORTED_MODULE_1___default.a === "function" && typeof _core_js_symbol_iterator__WEBPACK_IMPORTED_MODULE_0___default.a === "symbol") {
    _typeof = function _typeof(obj) {
      return typeof obj;
    };
  } else {
    _typeof = function _typeof(obj) {
      return obj && typeof _core_js_symbol__WEBPACK_IMPORTED_MODULE_1___default.a === "function" && obj.constructor === _core_js_symbol__WEBPACK_IMPORTED_MODULE_1___default.a && obj !== _core_js_symbol__WEBPACK_IMPORTED_MODULE_1___default.a.prototype ? "symbol" : typeof obj;
    };
  }

  return _typeof(obj);
}

/***/ }),

/***/ "8615":
/***/ (function(module, exports, __webpack_require__) {

// https://github.com/tc39/proposal-object-values-entries
var $export = __webpack_require__("5ca1");
var $values = __webpack_require__("504c")(false);

$export($export.S, 'Object', {
  values: function values(it) {
    return $values(it);
  }
});


/***/ }),

/***/ "8b67":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Node_vue_vue_type_style_index_0_id_6adeaa6d_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("f3d9");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Node_vue_vue_type_style_index_0_id_6adeaa6d_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Node_vue_vue_type_style_index_0_id_6adeaa6d_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */
 /* unused harmony default export */ var _unused_webpack_default_export = (_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Node_vue_vue_type_style_index_0_id_6adeaa6d_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "8b97":
/***/ (function(module, exports, __webpack_require__) {

// Works with __proto__ only. Old v8 can't work with null proto objects.
/* eslint-disable no-proto */
var isObject = __webpack_require__("d3f4");
var anObject = __webpack_require__("cb7c");
var check = function (O, proto) {
  anObject(O);
  if (!isObject(proto) && proto !== null) throw TypeError(proto + ": can't set as prototype!");
};
module.exports = {
  set: Object.setPrototypeOf || ('__proto__' in {} ? // eslint-disable-line
    function (test, buggy, set) {
      try {
        set = __webpack_require__("9b43")(Function.call, __webpack_require__("11e9").f(Object.prototype, '__proto__').set, 2);
        set(test, []);
        buggy = !(test instanceof Array);
      } catch (e) { buggy = true; }
      return function setPrototypeOf(O, proto) {
        check(O, proto);
        if (buggy) O.__proto__ = proto;
        else set(O, proto);
        return O;
      };
    }({}, false) : undefined),
  check: check
};


/***/ }),

/***/ "90ad":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "a07e":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "a799":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CustomEndpoint_vue_vue_type_style_index_0_id_2f90581e_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("5846");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CustomEndpoint_vue_vue_type_style_index_0_id_2f90581e_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CustomEndpoint_vue_vue_type_style_index_0_id_2f90581e_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */
 /* unused harmony default export */ var _unused_webpack_default_export = (_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CustomEndpoint_vue_vue_type_style_index_0_id_2f90581e_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "aa6c":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_PayloadPanel_vue_vue_type_style_index_0_id_e3945ed0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("210c");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_PayloadPanel_vue_vue_type_style_index_0_id_e3945ed0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_PayloadPanel_vue_vue_type_style_index_0_id_e3945ed0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */
 /* unused harmony default export */ var _unused_webpack_default_export = (_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_PayloadPanel_vue_vue_type_style_index_0_id_e3945ed0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "b39a":
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__("d3f4");
module.exports = function (it, TYPE) {
  if (!isObject(it) || it._t !== TYPE) throw TypeError('Incompatible receiver, ' + TYPE + ' required!');
  return it;
};


/***/ }),

/***/ "d185":
/***/ (function(module, exports, __webpack_require__) {

// 26.1.6 Reflect.get(target, propertyKey [, receiver])
var gOPD = __webpack_require__("11e9");
var getPrototypeOf = __webpack_require__("38fd");
var has = __webpack_require__("69a8");
var $export = __webpack_require__("5ca1");
var isObject = __webpack_require__("d3f4");
var anObject = __webpack_require__("cb7c");

function get(target, propertyKey /* , receiver */) {
  var receiver = arguments.length < 3 ? target : arguments[2];
  var desc, proto;
  if (anObject(target) === receiver) return target[propertyKey];
  if (desc = gOPD.f(target, propertyKey)) return has(desc, 'value')
    ? desc.value
    : desc.get !== undefined
      ? desc.get.call(receiver)
      : undefined;
  if (isObject(proto = getPrototypeOf(target))) return get(proto, propertyKey, receiver);
}

$export($export.S, 'Reflect', { get: get });


/***/ }),

/***/ "d8d6":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("1654");
__webpack_require__("6c1c");
module.exports = __webpack_require__("ccb9").f('iterator');


/***/ }),

/***/ "dcbc":
/***/ (function(module, exports, __webpack_require__) {

var redefine = __webpack_require__("2aba");
module.exports = function (target, src, safe) {
  for (var key in src) redefine(target, key, src[key], safe);
  return target;
};


/***/ }),

/***/ "e0b8":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var global = __webpack_require__("7726");
var $export = __webpack_require__("5ca1");
var redefine = __webpack_require__("2aba");
var redefineAll = __webpack_require__("dcbc");
var meta = __webpack_require__("67ab");
var forOf = __webpack_require__("4a59");
var anInstance = __webpack_require__("f605");
var isObject = __webpack_require__("d3f4");
var fails = __webpack_require__("79e5");
var $iterDetect = __webpack_require__("5cc5");
var setToStringTag = __webpack_require__("7f20");
var inheritIfRequired = __webpack_require__("5dbc");

module.exports = function (NAME, wrapper, methods, common, IS_MAP, IS_WEAK) {
  var Base = global[NAME];
  var C = Base;
  var ADDER = IS_MAP ? 'set' : 'add';
  var proto = C && C.prototype;
  var O = {};
  var fixMethod = function (KEY) {
    var fn = proto[KEY];
    redefine(proto, KEY,
      KEY == 'delete' ? function (a) {
        return IS_WEAK && !isObject(a) ? false : fn.call(this, a === 0 ? 0 : a);
      } : KEY == 'has' ? function has(a) {
        return IS_WEAK && !isObject(a) ? false : fn.call(this, a === 0 ? 0 : a);
      } : KEY == 'get' ? function get(a) {
        return IS_WEAK && !isObject(a) ? undefined : fn.call(this, a === 0 ? 0 : a);
      } : KEY == 'add' ? function add(a) { fn.call(this, a === 0 ? 0 : a); return this; }
        : function set(a, b) { fn.call(this, a === 0 ? 0 : a, b); return this; }
    );
  };
  if (typeof C != 'function' || !(IS_WEAK || proto.forEach && !fails(function () {
    new C().entries().next();
  }))) {
    // create collection constructor
    C = common.getConstructor(wrapper, NAME, IS_MAP, ADDER);
    redefineAll(C.prototype, methods);
    meta.NEED = true;
  } else {
    var instance = new C();
    // early implementations not supports chaining
    var HASNT_CHAINING = instance[ADDER](IS_WEAK ? {} : -0, 1) != instance;
    // V8 ~  Chromium 40- weak-collections throws on primitives, but should return false
    var THROWS_ON_PRIMITIVES = fails(function () { instance.has(1); });
    // most early implementations doesn't supports iterables, most modern - not close it correctly
    var ACCEPT_ITERABLES = $iterDetect(function (iter) { new C(iter); }); // eslint-disable-line no-new
    // for early implementations -0 and +0 not the same
    var BUGGY_ZERO = !IS_WEAK && fails(function () {
      // V8 ~ Chromium 42- fails only with 5+ elements
      var $instance = new C();
      var index = 5;
      while (index--) $instance[ADDER](index, index);
      return !$instance.has(-0);
    });
    if (!ACCEPT_ITERABLES) {
      C = wrapper(function (target, iterable) {
        anInstance(target, C, NAME);
        var that = inheritIfRequired(new Base(), target, C);
        if (iterable != undefined) forOf(iterable, IS_MAP, that[ADDER], that);
        return that;
      });
      C.prototype = proto;
      proto.constructor = C;
    }
    if (THROWS_ON_PRIMITIVES || BUGGY_ZERO) {
      fixMethod('delete');
      fixMethod('has');
      IS_MAP && fixMethod('get');
    }
    if (BUGGY_ZERO || HASNT_CHAINING) fixMethod(ADDER);
    // weak collections should not contains .clear method
    if (IS_WEAK && proto.clear) delete proto.clear;
  }

  setToStringTag(C, NAME);

  O[NAME] = C;
  $export($export.G + $export.W + $export.F * (C != Base), O);

  if (!IS_WEAK) common.setStrong(C, NAME, IS_MAP);

  return C;
};


/***/ }),

/***/ "f0a2":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/pages/CustomEndpoint.vue?vue&type=template&id=2f90581e&scoped=true&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('b-card',[_c('CustomForm',{on:{"data-received":_vm.onDataReceived,"error":_vm.onError}}),_c('ResourceInfo',{staticClass:"my-2",attrs:{"description":_vm.page_description}})],1),_c('PayloadPanel',{staticClass:"mt-2",attrs:{"payload":_vm.payload}})],1)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/pages/CustomEndpoint.vue?vue&type=template&id=2f90581e&scoped=true&

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.function.name.js
var es6_function_name = __webpack_require__("7f7f");

// EXTERNAL MODULE: ./src/libraries/Seatbelt.js
var Seatbelt = __webpack_require__("68f9");

// EXTERNAL MODULE: ./src/components/endpoints/ResourceInfo.vue + 4 modules
var ResourceInfo = __webpack_require__("1ada");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/forms/CustomForm.vue?vue&type=template&id=72f83aa9&scoped=true&
var CustomFormvue_type_template_id_72f83aa9_scoped_true_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('b-card',[_c('div',{staticClass:"d-flex align-items-center mb-2"},[_c('span',{staticClass:"fhir_base_url d-block font-italic small mr-2"},[_vm._v(_vm._s(_vm.fhir_base_url))]),_c('b-form-input',{attrs:{"size":"sm","name":"relative_url","placeholder":"enter URL..."},model:{value:(_vm.relative_url),callback:function ($$v) {_vm.relative_url=(typeof $$v === 'string'? $$v.trim(): $$v)},expression:"relative_url"}}),_c('b-form-select',{staticClass:"ml-2",attrs:{"options":_vm.methods,"size":"sm"},model:{value:(_vm.method),callback:function ($$v) {_vm.method=$$v},expression:"method"}})],1),_c('div',{staticClass:"d-flex justify-content-between"},[_c('b-button',{attrs:{"size":"sm","variant":"outline-primary","disabled":_vm.isSendDisabled},on:{"click":function($event){return _vm.onSendRequestClicked()}}},[(_vm.loading)?_c('font-awesome-icon',{attrs:{"icon":['fas', 'spinner'],"spin":"","fixed-width":""}}):_c('font-awesome-icon',{attrs:{"icon":['fas', 'cloud-download-alt'],"fixed-width":""}}),_c('span',{staticClass:"ml-2"},[_vm._v("Send request")])],1),_c('div',{staticClass:"d-flex"},[_c('CustomPresetStorage',{staticClass:"mr-2",attrs:{"preset":_vm.preset},on:{"restore":_vm.onRestore}}),_c('b-button',{attrs:{"size":"sm","variant":"outline-success"},on:{"click":_vm.onAddParameterClicked}},[_c('font-awesome-icon',{attrs:{"icon":['fas', 'plus-circle'],"fixed-width":""}}),_c('span',{staticClass:"ml-2"},[_vm._v("Add a new parameter")])],1)],1)],1),_c('div',{staticClass:"my-2"},_vm._l((_vm.params),function(param,index){return _c('div',{key:param.id,staticClass:"param-row"},[_c('div',{staticClass:"d-flex align-items-center"},[_c('b-form-input',{attrs:{"name":("key-" + index),"placeholder":"key"},model:{value:(param.key),callback:function ($$v) {_vm.$set(param, "key", $$v)},expression:"param.key"}}),_c('b-form-input',{staticClass:"mx-2",attrs:{"name":("value-" + index),"placeholder":"value"},model:{value:(param.value),callback:function ($$v) {_vm.$set(param, "value", $$v)},expression:"param.value"}}),_c('div',{staticClass:"d-flex align-items-center"},[_c('b-form-checkbox',{staticClass:"mr-2",attrs:{"switch":"","variant":"success"},model:{value:(param.enabled),callback:function ($$v) {_vm.$set(param, "enabled", $$v)},expression:"param.enabled"}}),_c('b-button',{attrs:{"size":"sm","variant":"outline-light"},on:{"click":function($event){return _vm.onDeleteParameterClicked(index)}}},[_c('font-awesome-icon',{staticClass:"text-danger",attrs:{"icon":['fas', 'trash'],"fixed-width":""}})],1)],1)],1)])}),0)])],1)}
var CustomFormvue_type_template_id_72f83aa9_scoped_true_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/endpoints/forms/CustomForm.vue?vue&type=template&id=72f83aa9&scoped=true&

// EXTERNAL MODULE: ./node_modules/regenerator-runtime/runtime.js
var runtime = __webpack_require__("96cf");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/asyncToGenerator.js
var asyncToGenerator = __webpack_require__("3b8d");

// EXTERNAL MODULE: ./node_modules/core-js/modules/web.dom.iterable.js
var web_dom_iterable = __webpack_require__("ac6a");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.array.iterator.js
var es6_array_iterator = __webpack_require__("cadf");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es7.object.values.js
var es7_object_values = __webpack_require__("8615");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/classCallCheck.js
var classCallCheck = __webpack_require__("d225");

// CONCATENATED MODULE: ./node_modules/uuid/dist/esm-browser/rng.js
// Unique ID creation requires a high quality random # generator. In the browser we therefore
// require the crypto API and do not support built-in fallback to lower quality random number
// generators (like Math.random()).
var getRandomValues;
var rnds8 = new Uint8Array(16);
function rng() {
  // lazy load so that environments that need to polyfill have a chance to do so
  if (!getRandomValues) {
    // getRandomValues needs to be invoked in a context where "this" is a Crypto implementation. Also,
    // find the complete implementation of crypto (msCrypto) on IE11.
    getRandomValues = typeof crypto !== 'undefined' && crypto.getRandomValues && crypto.getRandomValues.bind(crypto) || typeof msCrypto !== 'undefined' && typeof msCrypto.getRandomValues === 'function' && msCrypto.getRandomValues.bind(msCrypto);

    if (!getRandomValues) {
      throw new Error('crypto.getRandomValues() not supported. See https://github.com/uuidjs/uuid#getrandomvalues-not-supported');
    }
  }

  return getRandomValues(rnds8);
}
// CONCATENATED MODULE: ./node_modules/uuid/dist/esm-browser/regex.js
/* harmony default export */ var regex = (/^(?:[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}|00000000-0000-0000-0000-000000000000)$/i);
// CONCATENATED MODULE: ./node_modules/uuid/dist/esm-browser/validate.js


function validate(uuid) {
  return typeof uuid === 'string' && regex.test(uuid);
}

/* harmony default export */ var esm_browser_validate = (validate);
// CONCATENATED MODULE: ./node_modules/uuid/dist/esm-browser/stringify.js

/**
 * Convert array of 16 byte values to UUID string format of the form:
 * XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX
 */

var byteToHex = [];

for (var stringify_i = 0; stringify_i < 256; ++stringify_i) {
  byteToHex.push((stringify_i + 0x100).toString(16).substr(1));
}

function stringify(arr) {
  var offset = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  // Note: Be careful editing this code!  It's been tuned for performance
  // and works in ways you may not expect. See https://github.com/uuidjs/uuid/pull/434
  var uuid = (byteToHex[arr[offset + 0]] + byteToHex[arr[offset + 1]] + byteToHex[arr[offset + 2]] + byteToHex[arr[offset + 3]] + '-' + byteToHex[arr[offset + 4]] + byteToHex[arr[offset + 5]] + '-' + byteToHex[arr[offset + 6]] + byteToHex[arr[offset + 7]] + '-' + byteToHex[arr[offset + 8]] + byteToHex[arr[offset + 9]] + '-' + byteToHex[arr[offset + 10]] + byteToHex[arr[offset + 11]] + byteToHex[arr[offset + 12]] + byteToHex[arr[offset + 13]] + byteToHex[arr[offset + 14]] + byteToHex[arr[offset + 15]]).toLowerCase(); // Consistency check for valid UUID.  If this throws, it's likely due to one
  // of the following:
  // - One or more input array values don't map to a hex octet (leading to
  // "undefined" in the uuid)
  // - Invalid input values for the RFC `version` or `variant` fields

  if (!esm_browser_validate(uuid)) {
    throw TypeError('Stringified UUID is invalid');
  }

  return uuid;
}

/* harmony default export */ var esm_browser_stringify = (stringify);
// CONCATENATED MODULE: ./node_modules/uuid/dist/esm-browser/v4.js



function v4(options, buf, offset) {
  options = options || {};
  var rnds = options.random || (options.rng || rng)(); // Per 4.4, set bits for version and `clock_seq_hi_and_reserved`

  rnds[6] = rnds[6] & 0x0f | 0x40;
  rnds[8] = rnds[8] & 0x3f | 0x80; // Copy bytes to buffer, if provided

  if (buf) {
    offset = offset || 0;

    for (var i = 0; i < 16; ++i) {
      buf[offset + i] = rnds[i];
    }

    return buf;
  }

  return esm_browser_stringify(rnds);
}

/* harmony default export */ var esm_browser_v4 = (v4);
// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.string.iterator.js
var es6_string_iterator = __webpack_require__("5df3");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.weak-map.js
var es6_weak_map = __webpack_require__("10ad");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/toConsumableArray.js + 3 modules
var toConsumableArray = __webpack_require__("75fc");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/createClass.js
var createClass = __webpack_require__("b0b4");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.object.freeze.js
var es6_object_freeze = __webpack_require__("0d6d");

// CONCATENATED MODULE: ./src/models/Request.js








var methods = Object.freeze({
  GET: 'GET',
  POST: 'POST',
  PUT: 'PUT',
  DELETE: 'DELETE'
});

var Request_Request = /*#__PURE__*/function () {
  function Request(url) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
    var method = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : methods.GET;

    Object(classCallCheck["a" /* default */])(this, Request);

    _url.set(this, {
      writable: true,
      value: void 0
    });

    _options.set(this, {
      writable: true,
      value: void 0
    });

    _method.set(this, {
      writable: true,
      value: void 0
    });

    this._url = url;
    this._options = Object(toConsumableArray["a" /* default */])(options);
    this._method = method;
  }

  Object(createClass["a" /* default */])(Request, [{
    key: "url",
    get: function get() {
      return this._url;
    }
  }, {
    key: "options",
    get: function get() {
      return Object(toConsumableArray["a" /* default */])(this._options);
    }
  }, {
    key: "method",
    get: function get() {
      return this._method;
    }
  }]);

  return Request;
}();

var _url = new WeakMap();

var _options = new WeakMap();

var _method = new WeakMap();


// EXTERNAL MODULE: ./src/components/endpoints/forms/BaseResourceForm.vue + 4 modules
var BaseResourceForm = __webpack_require__("2f12");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/CustomPresetStorage.vue?vue&type=template&id=1706a130&
var CustomPresetStoragevue_type_template_id_1706a130_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('StorageInput',{attrs:{"save_disabled":!_vm.preset,"save_text":"Save preset"},on:{"restore":_vm.onRestore,"save":_vm.startSave},scopedSlots:_vm._u([{key:"default",fn:function(ref){
var addItem = ref.addItem;
return [_c('b-modal',{ref:"save-modal",attrs:{"title":"Save configuration"},scopedSlots:_vm._u([{key:"modal-footer",fn:function(ref){
var cancel = ref.cancel;
return [_c('b-button',{attrs:{"size":"sm","variant":"secondary"},on:{"click":function($event){return cancel()}}},[_vm._v("\n                Cancel\n              ")]),_c('b-button',{attrs:{"size":"sm","variant":"success","disabled":!_vm.preset_name},on:{"click":function($event){return _vm.onSaveOkClicked(addItem)}}},[_c('font-awesome-icon',{attrs:{"icon":['fas', 'save'],"fixed-width":""}}),_c('span',{staticClass:"ml-2"},[_vm._v("Save")])],1)]}}],null,true)},[_c('div',[_c('p',{staticClass:"my-4"},[_vm._v("Do you want to save the current configuration?")]),_c('b-input',{attrs:{"placeholder":"enter a name for this preset..."},model:{value:(_vm.preset_name),callback:function ($$v) {_vm.preset_name=(typeof $$v === 'string'? $$v.trim(): $$v)},expression:"preset_name"}})],1)])]}}])})],1)}
var CustomPresetStoragevue_type_template_id_1706a130_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/CustomPresetStorage.vue?vue&type=template&id=1706a130&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/StorageInput.vue?vue&type=template&id=20d60c2a&scoped=true&
var StorageInputvue_type_template_id_20d60c2a_scoped_true_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('b-button-group',{attrs:{"size":"sm"}},[_c('b-button',{attrs:{"disabled":_vm.save_disabled,"variant":"outline-info"},on:{"click":_vm.onSaveClicked}},[_c('font-awesome-icon',{attrs:{"icon":['fas', 'save'],"fixed-width":""}}),(_vm.save_text)?_c('span',{staticClass:"ml-2",domProps:{"textContent":_vm._s(_vm.save_text)}}):_vm._e()],1),_c('b-dropdown',{attrs:{"size":"sm","variant":"outline-info","disabled":this.items.length<1},scopedSlots:_vm._u([{key:"button-content",fn:function(){return [_c('font-awesome-icon',{attrs:{"icon":['fas', 'database'],"fixed-width":""}})]},proxy:true}])},[[_c('b-dropdown-text',[_vm._v("Restore preset")]),_c('b-dropdown-divider'),_vm._l((_vm.items),function(item,index){return _c('b-dropdown-item',{key:(index + "-" + (item.key)),on:{"click":function($event){return _vm.onSelect(item)}}},[_c('div',{staticClass:"d-flex justify-content-between align-items-center"},[_c('span',{staticClass:"small text-nowrap font-weight-bold text-muted"},[_vm._v(_vm._s(item.key))]),_c('b-button',{staticClass:"ml-2",attrs:{"size":"sm","variant":"outline-danger"},on:{"click":function($event){return _vm.confirmRemove(item)}}},[_c('font-awesome-icon',{attrs:{"icon":['fas', 'trash'],"fixed-width":""}})],1)],1)])})]],2)],1),_vm._t("default",null,{"addItem":_vm.addItem})],2)}
var StorageInputvue_type_template_id_20d60c2a_scoped_true_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/StorageInput.vue?vue&type=template&id=20d60c2a&scoped=true&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/StorageInput.vue?vue&type=script&lang=js&





//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
function storageAvailable(type) {
  var storage;

  try {
    storage = window[type];
    var x = '__storage_test__';
    storage.setItem(x, x);
    storage.removeItem(x);
    return true;
  } catch (e) {
    return e instanceof DOMException && ( // everything except Firefox
    e.code === 22 || // Firefox
    e.code === 1014 || // test name field too, because code might not be present
    // everything except Firefox
    e.name === 'QuotaExceededError' || // Firefox
    e.name === 'NS_ERROR_DOM_QUOTA_REACHED') && // acknowledge QuotaExceededError only if there's something already stored
    storage && storage.length !== 0;
  }
}
/**
 * for v-model to work on parents using this component:
 * - the 'value' prop must be bind to the input field
 * - an input event must be emitted when the input field fires input: this.$emit('input', event.target.value)
 * 
 * the approach described above is only used if the value prop is provided.
 * if value is not provided then we will use internal_value.
 * to choose between value and internal value we use the computed property input as a proxy.
 * 
 */


/* harmony default export */ var StorageInputvue_type_script_lang_js_ = ({
  data: function data() {
    return {
      items: []
    };
  },
  props: {
    storage_key: {
      type: String,
      default: 'my_storage'
    },
    save_disabled: {
      type: Boolean,
      default: true
    },
    save_text: {
      type: String,
      default: ''
    }
  },
  created: function created() {
    this.items = this.getStoredItems();
  },
  computed: {
    storage_enabled: function storage_enabled() {
      return storageAvailable('localStorage');
    }
  },
  methods: {
    /**
     * emit input when an item is selected
     */
    onSelect: function onSelect(item) {
      this.$emit('restore', item.value);
    },

    /**
     * save an item in the localStorage
     */
    addItem: function addItem(key, value) {
      var item = {
        key: key,
        value: value
      };
      console.log(key, value);
      this.items.push(item);
      this.storeItems();
    },

    /**
     * remove an item from the localStorage
     */
    confirmRemove: function () {
      var _confirmRemove = Object(asyncToGenerator["a" /* default */])( /*#__PURE__*/regeneratorRuntime.mark(function _callee(item) {
        var response, index;
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.next = 2;
                return this.$bvModal.msgBoxConfirm('Are you sure you want to delete this preset?', {
                  title: 'Delete this preset?',
                  okTitle: 'Delete',
                  okVariant: 'danger'
                });

              case 2:
                response = _context.sent;

                if (response) {
                  _context.next = 5;
                  break;
                }

                return _context.abrupt("return");

              case 5:
                index = this.items.indexOf(item);

                if (!(index < 0)) {
                  _context.next = 8;
                  break;
                }

                return _context.abrupt("return");

              case 8:
                // remove 1 item at index
                this.items.splice(index, 1);
                this.storeItems();

              case 10:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this);
      }));

      function confirmRemove(_x) {
        return _confirmRemove.apply(this, arguments);
      }

      return confirmRemove;
    }(),
    getStoredItems: function getStoredItems() {
      if (!this.storage_enabled) return [];
      if (!this.storage_key) return [];
      var stored_items = localStorage.getItem(this.storage_key);
      var items = JSON.parse(stored_items) || [];
      if (Array.isArray(items)) return items;else return [items];
    },
    storeItems: function storeItems() {
      localStorage.setItem("".concat(this.storage_key), JSON.stringify(Object(toConsumableArray["a" /* default */])(this.items)));
    },

    /**
     * emit the save event passing the storeItems function
     */
    onSaveClicked: function onSaveClicked() {
      this.$emit('save', this.addItem);
    }
  }
});
// CONCATENATED MODULE: ./src/components/StorageInput.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_StorageInputvue_type_script_lang_js_ = (StorageInputvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./src/components/StorageInput.vue?vue&type=style&index=0&id=20d60c2a&scoped=true&lang=css&
var StorageInputvue_type_style_index_0_id_20d60c2a_scoped_true_lang_css_ = __webpack_require__("3aed");

// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/StorageInput.vue






/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  components_StorageInputvue_type_script_lang_js_,
  StorageInputvue_type_template_id_20d60c2a_scoped_true_render,
  StorageInputvue_type_template_id_20d60c2a_scoped_true_staticRenderFns,
  false,
  null,
  "20d60c2a",
  null
  
)

/* harmony default export */ var StorageInput = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/CustomPresetStorage.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ var CustomPresetStoragevue_type_script_lang_js_ = ({
  components: {
    StorageInput: StorageInput
  },
  data: function data() {
    return {
      preset_name: ''
    };
  },
  props: {
    preset: {
      type: Object,
      default: null
    },
    save_disabled: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    startSave: function startSave() {
      this.preset_name = ''; // reset the input field

      var save_modal = this.$refs['save-modal'];
      if (!save_modal) return;
      save_modal.show();
    },
    onRestore: function onRestore(item) {
      this.$emit('restore', item);
    },
    onSaveOkClicked: function onSaveOkClicked(save_callable) {
      if (typeof save_callable === 'function') save_callable(this.preset_name, this.preset);
      var save_modal = this.$refs['save-modal'];
      if (!save_modal) return;
      save_modal.hide();
    }
  }
});
// CONCATENATED MODULE: ./src/components/CustomPresetStorage.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_CustomPresetStoragevue_type_script_lang_js_ = (CustomPresetStoragevue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/components/CustomPresetStorage.vue





/* normalize component */

var CustomPresetStorage_component = Object(componentNormalizer["a" /* default */])(
  components_CustomPresetStoragevue_type_script_lang_js_,
  CustomPresetStoragevue_type_template_id_1706a130_render,
  CustomPresetStoragevue_type_template_id_1706a130_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var CustomPresetStorage = (CustomPresetStorage_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/forms/CustomForm.vue?vue&type=script&lang=js&






//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//





/**
 * class to create a Param
 */

var CustomFormvue_type_script_lang_js_Param = function Param() {
  var key = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
  var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
  var enabled = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

  Object(classCallCheck["a" /* default */])(this, Param);

  this.id = esm_browser_v4();
  this.key = key;
  this.value = value;
  this.enabled = enabled;
};

/* harmony default export */ var CustomFormvue_type_script_lang_js_ = ({
  extends: BaseResourceForm["a" /* default */],
  components: {
    CustomPresetStorage: CustomPresetStorage
  },
  data: function data() {
    return {
      loading: false,
      method: methods.GET,
      methods: Object.values(methods),
      params: [],
      relative_url: ''
    };
  },
  computed: {
    fhir_base_url: function fhir_base_url() {
      var fhir_base_url = this.$store.state.app_settings.fhir_base_url;
      return fhir_base_url;
    },
    // get payload that needs to be persisted in localStorage
    preset: function preset() {
      if (this.relative_url == '') return;
      var preset = {
        relative_url: this.relative_url,
        params: this.params,
        method: this.method
      };
      return preset;
    },
    isSendDisabled: function isSendDisabled() {
      return this.loading || this.relative_url == '';
    }
  },
  methods: {
    onAddParameterClicked: function onAddParameterClicked() {
      var param = new CustomFormvue_type_script_lang_js_Param();
      this.params.splice(this.params.length, 0, param);
    },
    onDeleteParameterClicked: function onDeleteParameterClicked(index) {
      this.params.splice(index, 1);
    },
    onRestore: function onRestore(preset) {
      var _preset$params = preset.params,
          params = _preset$params === void 0 ? [] : _preset$params,
          _preset$relative_url = preset.relative_url,
          relative_url = _preset$relative_url === void 0 ? '' : _preset$relative_url,
          method = preset.method;
      this.method = method;
      this.relative_url = relative_url;
      var restored_params = [];
      params.forEach(function (item) {
        var param = new CustomFormvue_type_script_lang_js_Param(item.key, item.value, item.enabled);
        restored_params.push(param);
      });
      this.params = restored_params;
    },
    onSendRequestClicked: function () {
      var _onSendRequestClicked = Object(asyncToGenerator["a" /* default */])( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
        var options, params, response, data, error_message, _Seatbelt, _data, is_error, message, code;

        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                this.loading = true;
                options = {};
                params = this.params.filter(function (param) {
                  return param.enabled;
                });
                params.forEach(function (param) {
                  if (param.key in options && !Array.isArray(options[param.key])) {
                    // transform to array if there is a duplicate key (only do this once)
                    options[param.key] = [options[param.key]];
                  } // push or set


                  if (Array.isArray(options[param.key])) {
                    options[param.key].push(param.value);
                  } else {
                    options[param.key] = param.value;
                  }
                });
                _context.prev = 4;
                _context.next = 7;
                return this.$API.dispatch('fhir/customRequest', this.relative_url, options);

              case 7:
                response = _context.sent;
                data = response.data;
                this.$emit('data-received', data);
                _context.next = 20;
                break;

              case 12:
                _context.prev = 12;
                _context.t0 = _context["catch"](4);
                error_message = '';
                _Seatbelt = Object(Seatbelt["a" /* default */])(_context.t0), _data = _Seatbelt.response.data;
                is_error = _data.is_error, message = _data.message, code = _data.code;
                if (message) error_message = message;else error_message = _context.t0;
                this.$bvModal.msgBoxOk(error_message, {
                  title: 'Error',
                  bodyClass: 'text-break'
                });
                this.$emit('error', error_message);

              case 20:
                _context.prev = 20;
                this.loading = false;
                return _context.finish(20);

              case 23:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this, [[4, 12, 20, 23]]);
      }));

      function onSendRequestClicked() {
        return _onSendRequestClicked.apply(this, arguments);
      }

      return onSendRequestClicked;
    }()
  }
});
// CONCATENATED MODULE: ./src/components/endpoints/forms/CustomForm.vue?vue&type=script&lang=js&
 /* harmony default export */ var forms_CustomFormvue_type_script_lang_js_ = (CustomFormvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./src/components/endpoints/forms/CustomForm.vue?vue&type=style&index=0&id=72f83aa9&scoped=true&lang=css&
var CustomFormvue_type_style_index_0_id_72f83aa9_scoped_true_lang_css_ = __webpack_require__("487d");

// CONCATENATED MODULE: ./src/components/endpoints/forms/CustomForm.vue






/* normalize component */

var CustomForm_component = Object(componentNormalizer["a" /* default */])(
  forms_CustomFormvue_type_script_lang_js_,
  CustomFormvue_type_template_id_72f83aa9_scoped_true_render,
  CustomFormvue_type_template_id_72f83aa9_scoped_true_staticRenderFns,
  false,
  null,
  "72f83aa9",
  null
  
)

/* harmony default export */ var CustomForm = (CustomForm_component.exports);
// EXTERNAL MODULE: ./src/components/Payload/PayloadPanel.vue + 4 modules
var PayloadPanel = __webpack_require__("5062");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/pages/CustomEndpoint.vue?vue&type=script&lang=js&

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/**
 * The FHIR resource depends on the current route
 * and is accessible via $refs['fhir-resource].
 * All resources are of type /components/endpoints/BaseResource
 * and have the method getParams
 */




/* harmony default export */ var CustomEndpointvue_type_script_lang_js_ = ({
  components: {
    ResourceInfo: ResourceInfo["a" /* default */],
    PayloadPanel: PayloadPanel["a" /* default */],
    CustomForm: CustomForm
  },
  data: function data() {
    return {
      filter: null,
      filterOn: [],
      stacked: false,
      payload: null
    };
  },
  computed: {
    page_description: function page_description() {
      var _this$$route$meta$des = this.$route.meta.description,
          description = _this$$route$meta$des === void 0 ? '' : _this$$route$meta$des;
      return description;
    }
  },
  methods: {
    onDataReceived: function onDataReceived(payload) {
      var data = payload.data,
          metadata = payload.metadata;
      this.data = data;
      this.metadata = Object(Seatbelt["a" /* default */])(metadata);
      this.payload = metadata.payload;
    },
    onError: function onError() {
      this.payload = null;
    },

    /**
     * update the title of the page
     * using the name of the resource.
     * the information is defined in the route
     */
    updateTitle: function updateTitle(value, previous) {
      var new_name = Object(Seatbelt["a" /* default */])(value).name;
      var previous_name = Object(Seatbelt["a" /* default */])(previous).name;
      if (new_name !== previous_name) this.payload = null;
    }
  },
  watch: {
    $route: {
      immediate: true,
      handler: function handler() {
        var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var previous = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        this.updateTitle(value, previous);
        this.fhir_resources = [];
      }
    }
  }
});
// CONCATENATED MODULE: ./src/pages/CustomEndpoint.vue?vue&type=script&lang=js&
 /* harmony default export */ var pages_CustomEndpointvue_type_script_lang_js_ = (CustomEndpointvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./src/pages/CustomEndpoint.vue?vue&type=style&index=0&id=2f90581e&scoped=true&lang=css&
var CustomEndpointvue_type_style_index_0_id_2f90581e_scoped_true_lang_css_ = __webpack_require__("a799");

// CONCATENATED MODULE: ./src/pages/CustomEndpoint.vue






/* normalize component */

var CustomEndpoint_component = Object(componentNormalizer["a" /* default */])(
  pages_CustomEndpointvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  "2f90581e",
  null
  
)

/* harmony default export */ var CustomEndpoint = __webpack_exports__["default"] = (CustomEndpoint_component.exports);

/***/ }),

/***/ "f3d9":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "f605":
/***/ (function(module, exports) {

module.exports = function (it, Constructor, name, forbiddenField) {
  if (!(it instanceof Constructor) || (forbiddenField !== undefined && forbiddenField in it)) {
    throw TypeError(name + ': incorrect invocation!');
  } return it;
};


/***/ })

}]);
//# sourceMappingURL=mapping_helper_vue.common.4.js.map