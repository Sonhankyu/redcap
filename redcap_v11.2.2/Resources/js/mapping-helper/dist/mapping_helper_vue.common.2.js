((typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] = (typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] || []).push([[2,0],{

/***/ "15a5":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ObservationsTable_vue_vue_type_style_index_0_id_13ffc694_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("7a04");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ObservationsTable_vue_vue_type_style_index_0_id_13ffc694_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ObservationsTable_vue_vue_type_style_index_0_id_13ffc694_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */
 /* unused harmony default export */ var _unused_webpack_default_export = (_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ObservationsTable_vue_vue_type_style_index_0_id_13ffc694_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "1c4c":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var ctx = __webpack_require__("9b43");
var $export = __webpack_require__("5ca1");
var toObject = __webpack_require__("4bf8");
var call = __webpack_require__("1fa8");
var isArrayIter = __webpack_require__("33a4");
var toLength = __webpack_require__("9def");
var createProperty = __webpack_require__("f1ae");
var getIterFn = __webpack_require__("27ee");

$export($export.S + $export.F * !__webpack_require__("5cc5")(function (iter) { Array.from(iter); }), 'Array', {
  // 22.1.2.1 Array.from(arrayLike, mapfn = undefined, thisArg = undefined)
  from: function from(arrayLike /* , mapfn = undefined, thisArg = undefined */) {
    var O = toObject(arrayLike);
    var C = typeof this == 'function' ? this : Array;
    var aLen = arguments.length;
    var mapfn = aLen > 1 ? arguments[1] : undefined;
    var mapping = mapfn !== undefined;
    var index = 0;
    var iterFn = getIterFn(O);
    var length, result, step, iterator;
    if (mapping) mapfn = ctx(mapfn, aLen > 2 ? arguments[2] : undefined, 2);
    // if object isn't iterable or it's array with default iterator - use simple case
    if (iterFn != undefined && !(C == Array && isArrayIter(iterFn))) {
      for (iterator = iterFn.call(O), result = new C(); !(step = iterator.next()).done; index++) {
        createProperty(result, index, mapping ? call(iterator, mapfn, [step.value, index], true) : step.value);
      }
    } else {
      length = toLength(O.length);
      for (result = new C(length); length > index; index++) {
        createProperty(result, index, mapping ? mapfn(O[index], index) : O[index]);
      }
    }
    result.length = index;
    return result;
  }
});


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

/***/ "37c8":
/***/ (function(module, exports, __webpack_require__) {

exports.f = __webpack_require__("2b4c");


/***/ }),

/***/ "3a72":
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__("7726");
var core = __webpack_require__("8378");
var LIBRARY = __webpack_require__("2d00");
var wksExt = __webpack_require__("37c8");
var defineProperty = __webpack_require__("86cc").f;
module.exports = function (name) {
  var $Symbol = core.Symbol || (core.Symbol = LIBRARY ? {} : global.Symbol || {});
  if (name.charAt(0) != '_' && !(name in $Symbol)) defineProperty($Symbol, name, { value: wksExt.f(name) });
};


/***/ }),

/***/ "46fd":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ResourceTable_vue_vue_type_style_index_0_id_cabf33a4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("7b5b");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ResourceTable_vue_vue_type_style_index_0_id_cabf33a4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ResourceTable_vue_vue_type_style_index_0_id_cabf33a4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */
 /* unused harmony default export */ var _unused_webpack_default_export = (_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ResourceTable_vue_vue_type_style_index_0_id_cabf33a4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "4917":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var anObject = __webpack_require__("cb7c");
var toLength = __webpack_require__("9def");
var advanceStringIndex = __webpack_require__("0390");
var regExpExec = __webpack_require__("5f1b");

// @@match logic
__webpack_require__("214f")('match', 1, function (defined, MATCH, $match, maybeCallNative) {
  return [
    // `String.prototype.match` method
    // https://tc39.github.io/ecma262/#sec-string.prototype.match
    function match(regexp) {
      var O = defined(this);
      var fn = regexp == undefined ? undefined : regexp[MATCH];
      return fn !== undefined ? fn.call(regexp, O) : new RegExp(regexp)[MATCH](String(O));
    },
    // `RegExp.prototype[@@match]` method
    // https://tc39.github.io/ecma262/#sec-regexp.prototype-@@match
    function (regexp) {
      var res = maybeCallNative($match, regexp, this);
      if (res.done) return res.value;
      var rx = anObject(regexp);
      var S = String(this);
      if (!rx.global) return regExpExec(rx, S);
      var fullUnicode = rx.unicode;
      rx.lastIndex = 0;
      var A = [];
      var n = 0;
      var result;
      while ((result = regExpExec(rx, S)) !== null) {
        var matchStr = String(result[0]);
        A[n] = matchStr;
        if (matchStr === '') rx.lastIndex = advanceStringIndex(S, toLength(rx.lastIndex), fullUnicode);
        n++;
      }
      return n === 0 ? null : A;
    }
  ];
});


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

/***/ "7a04":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "7b5b":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "7bbc":
/***/ (function(module, exports, __webpack_require__) {

// fallback for IE11 buggy Object.getOwnPropertyNames with iframe and window
var toIObject = __webpack_require__("6821");
var gOPN = __webpack_require__("9093").f;
var toString = {}.toString;

var windowNames = typeof window == 'object' && window && Object.getOwnPropertyNames
  ? Object.getOwnPropertyNames(window) : [];

var getWindowNames = function (it) {
  try {
    return gOPN(it);
  } catch (e) {
    return windowNames.slice();
  }
};

module.exports.f = function getOwnPropertyNames(it) {
  return windowNames && toString.call(it) == '[object Window]' ? getWindowNames(it) : gOPN(toIObject(it));
};


/***/ }),

/***/ "8a81":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

// ECMAScript 6 symbols shim
var global = __webpack_require__("7726");
var has = __webpack_require__("69a8");
var DESCRIPTORS = __webpack_require__("9e1e");
var $export = __webpack_require__("5ca1");
var redefine = __webpack_require__("2aba");
var META = __webpack_require__("67ab").KEY;
var $fails = __webpack_require__("79e5");
var shared = __webpack_require__("5537");
var setToStringTag = __webpack_require__("7f20");
var uid = __webpack_require__("ca5a");
var wks = __webpack_require__("2b4c");
var wksExt = __webpack_require__("37c8");
var wksDefine = __webpack_require__("3a72");
var enumKeys = __webpack_require__("d4c0");
var isArray = __webpack_require__("1169");
var anObject = __webpack_require__("cb7c");
var isObject = __webpack_require__("d3f4");
var toObject = __webpack_require__("4bf8");
var toIObject = __webpack_require__("6821");
var toPrimitive = __webpack_require__("6a99");
var createDesc = __webpack_require__("4630");
var _create = __webpack_require__("2aeb");
var gOPNExt = __webpack_require__("7bbc");
var $GOPD = __webpack_require__("11e9");
var $GOPS = __webpack_require__("2621");
var $DP = __webpack_require__("86cc");
var $keys = __webpack_require__("0d58");
var gOPD = $GOPD.f;
var dP = $DP.f;
var gOPN = gOPNExt.f;
var $Symbol = global.Symbol;
var $JSON = global.JSON;
var _stringify = $JSON && $JSON.stringify;
var PROTOTYPE = 'prototype';
var HIDDEN = wks('_hidden');
var TO_PRIMITIVE = wks('toPrimitive');
var isEnum = {}.propertyIsEnumerable;
var SymbolRegistry = shared('symbol-registry');
var AllSymbols = shared('symbols');
var OPSymbols = shared('op-symbols');
var ObjectProto = Object[PROTOTYPE];
var USE_NATIVE = typeof $Symbol == 'function' && !!$GOPS.f;
var QObject = global.QObject;
// Don't use setters in Qt Script, https://github.com/zloirock/core-js/issues/173
var setter = !QObject || !QObject[PROTOTYPE] || !QObject[PROTOTYPE].findChild;

// fallback for old Android, https://code.google.com/p/v8/issues/detail?id=687
var setSymbolDesc = DESCRIPTORS && $fails(function () {
  return _create(dP({}, 'a', {
    get: function () { return dP(this, 'a', { value: 7 }).a; }
  })).a != 7;
}) ? function (it, key, D) {
  var protoDesc = gOPD(ObjectProto, key);
  if (protoDesc) delete ObjectProto[key];
  dP(it, key, D);
  if (protoDesc && it !== ObjectProto) dP(ObjectProto, key, protoDesc);
} : dP;

var wrap = function (tag) {
  var sym = AllSymbols[tag] = _create($Symbol[PROTOTYPE]);
  sym._k = tag;
  return sym;
};

var isSymbol = USE_NATIVE && typeof $Symbol.iterator == 'symbol' ? function (it) {
  return typeof it == 'symbol';
} : function (it) {
  return it instanceof $Symbol;
};

var $defineProperty = function defineProperty(it, key, D) {
  if (it === ObjectProto) $defineProperty(OPSymbols, key, D);
  anObject(it);
  key = toPrimitive(key, true);
  anObject(D);
  if (has(AllSymbols, key)) {
    if (!D.enumerable) {
      if (!has(it, HIDDEN)) dP(it, HIDDEN, createDesc(1, {}));
      it[HIDDEN][key] = true;
    } else {
      if (has(it, HIDDEN) && it[HIDDEN][key]) it[HIDDEN][key] = false;
      D = _create(D, { enumerable: createDesc(0, false) });
    } return setSymbolDesc(it, key, D);
  } return dP(it, key, D);
};
var $defineProperties = function defineProperties(it, P) {
  anObject(it);
  var keys = enumKeys(P = toIObject(P));
  var i = 0;
  var l = keys.length;
  var key;
  while (l > i) $defineProperty(it, key = keys[i++], P[key]);
  return it;
};
var $create = function create(it, P) {
  return P === undefined ? _create(it) : $defineProperties(_create(it), P);
};
var $propertyIsEnumerable = function propertyIsEnumerable(key) {
  var E = isEnum.call(this, key = toPrimitive(key, true));
  if (this === ObjectProto && has(AllSymbols, key) && !has(OPSymbols, key)) return false;
  return E || !has(this, key) || !has(AllSymbols, key) || has(this, HIDDEN) && this[HIDDEN][key] ? E : true;
};
var $getOwnPropertyDescriptor = function getOwnPropertyDescriptor(it, key) {
  it = toIObject(it);
  key = toPrimitive(key, true);
  if (it === ObjectProto && has(AllSymbols, key) && !has(OPSymbols, key)) return;
  var D = gOPD(it, key);
  if (D && has(AllSymbols, key) && !(has(it, HIDDEN) && it[HIDDEN][key])) D.enumerable = true;
  return D;
};
var $getOwnPropertyNames = function getOwnPropertyNames(it) {
  var names = gOPN(toIObject(it));
  var result = [];
  var i = 0;
  var key;
  while (names.length > i) {
    if (!has(AllSymbols, key = names[i++]) && key != HIDDEN && key != META) result.push(key);
  } return result;
};
var $getOwnPropertySymbols = function getOwnPropertySymbols(it) {
  var IS_OP = it === ObjectProto;
  var names = gOPN(IS_OP ? OPSymbols : toIObject(it));
  var result = [];
  var i = 0;
  var key;
  while (names.length > i) {
    if (has(AllSymbols, key = names[i++]) && (IS_OP ? has(ObjectProto, key) : true)) result.push(AllSymbols[key]);
  } return result;
};

// 19.4.1.1 Symbol([description])
if (!USE_NATIVE) {
  $Symbol = function Symbol() {
    if (this instanceof $Symbol) throw TypeError('Symbol is not a constructor!');
    var tag = uid(arguments.length > 0 ? arguments[0] : undefined);
    var $set = function (value) {
      if (this === ObjectProto) $set.call(OPSymbols, value);
      if (has(this, HIDDEN) && has(this[HIDDEN], tag)) this[HIDDEN][tag] = false;
      setSymbolDesc(this, tag, createDesc(1, value));
    };
    if (DESCRIPTORS && setter) setSymbolDesc(ObjectProto, tag, { configurable: true, set: $set });
    return wrap(tag);
  };
  redefine($Symbol[PROTOTYPE], 'toString', function toString() {
    return this._k;
  });

  $GOPD.f = $getOwnPropertyDescriptor;
  $DP.f = $defineProperty;
  __webpack_require__("9093").f = gOPNExt.f = $getOwnPropertyNames;
  __webpack_require__("52a7").f = $propertyIsEnumerable;
  $GOPS.f = $getOwnPropertySymbols;

  if (DESCRIPTORS && !__webpack_require__("2d00")) {
    redefine(ObjectProto, 'propertyIsEnumerable', $propertyIsEnumerable, true);
  }

  wksExt.f = function (name) {
    return wrap(wks(name));
  };
}

$export($export.G + $export.W + $export.F * !USE_NATIVE, { Symbol: $Symbol });

for (var es6Symbols = (
  // 19.4.2.2, 19.4.2.3, 19.4.2.4, 19.4.2.6, 19.4.2.8, 19.4.2.9, 19.4.2.10, 19.4.2.11, 19.4.2.12, 19.4.2.13, 19.4.2.14
  'hasInstance,isConcatSpreadable,iterator,match,replace,search,species,split,toPrimitive,toStringTag,unscopables'
).split(','), j = 0; es6Symbols.length > j;)wks(es6Symbols[j++]);

for (var wellKnownSymbols = $keys(wks.store), k = 0; wellKnownSymbols.length > k;) wksDefine(wellKnownSymbols[k++]);

$export($export.S + $export.F * !USE_NATIVE, 'Symbol', {
  // 19.4.2.1 Symbol.for(key)
  'for': function (key) {
    return has(SymbolRegistry, key += '')
      ? SymbolRegistry[key]
      : SymbolRegistry[key] = $Symbol(key);
  },
  // 19.4.2.5 Symbol.keyFor(sym)
  keyFor: function keyFor(sym) {
    if (!isSymbol(sym)) throw TypeError(sym + ' is not a symbol!');
    for (var key in SymbolRegistry) if (SymbolRegistry[key] === sym) return key;
  },
  useSetter: function () { setter = true; },
  useSimple: function () { setter = false; }
});

$export($export.S + $export.F * !USE_NATIVE, 'Object', {
  // 19.1.2.2 Object.create(O [, Properties])
  create: $create,
  // 19.1.2.4 Object.defineProperty(O, P, Attributes)
  defineProperty: $defineProperty,
  // 19.1.2.3 Object.defineProperties(O, Properties)
  defineProperties: $defineProperties,
  // 19.1.2.6 Object.getOwnPropertyDescriptor(O, P)
  getOwnPropertyDescriptor: $getOwnPropertyDescriptor,
  // 19.1.2.7 Object.getOwnPropertyNames(O)
  getOwnPropertyNames: $getOwnPropertyNames,
  // 19.1.2.8 Object.getOwnPropertySymbols(O)
  getOwnPropertySymbols: $getOwnPropertySymbols
});

// Chrome 38 and 39 `Object.getOwnPropertySymbols` fails on primitives
// https://bugs.chromium.org/p/v8/issues/detail?id=3443
var FAILS_ON_PRIMITIVES = $fails(function () { $GOPS.f(1); });

$export($export.S + $export.F * FAILS_ON_PRIMITIVES, 'Object', {
  getOwnPropertySymbols: function getOwnPropertySymbols(it) {
    return $GOPS.f(toObject(it));
  }
});

// 24.3.2 JSON.stringify(value [, replacer [, space]])
$JSON && $export($export.S + $export.F * (!USE_NATIVE || $fails(function () {
  var S = $Symbol();
  // MS Edge converts symbol values to JSON as {}
  // WebKit converts symbol values to JSON as null
  // V8 throws on boxed symbols
  return _stringify([S]) != '[null]' || _stringify({ a: S }) != '{}' || _stringify(Object(S)) != '{}';
})), 'JSON', {
  stringify: function stringify(it) {
    var args = [it];
    var i = 1;
    var replacer, $replacer;
    while (arguments.length > i) args.push(arguments[i++]);
    $replacer = replacer = args[1];
    if (!isObject(replacer) && it === undefined || isSymbol(it)) return; // IE8 returns string on undefined
    if (!isArray(replacer)) replacer = function (key, value) {
      if (typeof $replacer == 'function') value = $replacer.call(this, key, value);
      if (!isSymbol(value)) return value;
    };
    args[1] = replacer;
    return _stringify.apply($JSON, args);
  }
});

// 19.4.3.4 Symbol.prototype[@@toPrimitive](hint)
$Symbol[PROTOTYPE][TO_PRIMITIVE] || __webpack_require__("32e9")($Symbol[PROTOTYPE], TO_PRIMITIVE, $Symbol[PROTOTYPE].valueOf);
// 19.4.3.5 Symbol.prototype[@@toStringTag]
setToStringTag($Symbol, 'Symbol');
// 20.2.1.9 Math[@@toStringTag]
setToStringTag(Math, 'Math', true);
// 24.3.3 JSON[@@toStringTag]
setToStringTag(global.JSON, 'JSON', true);


/***/ }),

/***/ "8b67":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Node_vue_vue_type_style_index_0_id_6adeaa6d_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("f3d9");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Node_vue_vue_type_style_index_0_id_6adeaa6d_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Node_vue_vue_type_style_index_0_id_6adeaa6d_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */
 /* unused harmony default export */ var _unused_webpack_default_export = (_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Node_vue_vue_type_style_index_0_id_6adeaa6d_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "8ca8":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/tables/ResourceTable.vue?vue&type=template&id=cabf33a4&scoped=true&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('b-table',_vm._b({staticClass:"my-2",attrs:{"fields":_vm.fields},scopedSlots:_vm._u([{key:"cell()",fn:function(data){return [_c('span',{domProps:{"innerHTML":_vm._s(_vm.$attrs.markText(data.value))}})]}}])},'b-table',Object.assign({}, _vm.$attrs, _vm.$props),false))],1)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/components/endpoints/tables/ResourceTable.vue?vue&type=template&id=cabf33a4&scoped=true&

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.array.iterator.js
var es6_array_iterator = __webpack_require__("cadf");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.object.keys.js
var es6_object_keys = __webpack_require__("456d");

// EXTERNAL MODULE: ./node_modules/core-js/modules/web.dom.iterable.js
var web_dom_iterable = __webpack_require__("ac6a");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/toConsumableArray.js + 3 modules
var toConsumableArray = __webpack_require__("75fc");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/tables/ResourceTable.vue?vue&type=script&lang=js&




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
/* harmony default export */ var ResourceTablevue_type_script_lang_js_ = ({
  data: function data() {
    return {};
  },
  computed: {
    fields: function fields() {
      var fields = this.setFields(this.items);
      return fields;
    }
  },
  methods: {
    /**
     * build a list of fields based on the
     * keys of the items.
     * set the `sortable` property for all
     */
    setFields: function setFields(items) {
      var duplicateChecker = []; //store keys here to check for duplicates

      var fields = Object(toConsumableArray["a" /* default */])(items).reduce(function (accumulator, item) {
        Object.keys(item).forEach(function (key) {
          if (duplicateChecker.indexOf(key) < 0) {
            var field = {
              key: key,
              sortable: true
            };
            accumulator.push(field);
            duplicateChecker.push(key);
          }
        });
        return accumulator;
      }, []);

      return fields;
    }
  },
  props: {
    stacked: {
      type: Boolean,
      defaults: false
    },
    items: {
      type: Array,
      default: function _default() {
        return [];
      }
    }
  }
});
// CONCATENATED MODULE: ./src/components/endpoints/tables/ResourceTable.vue?vue&type=script&lang=js&
 /* harmony default export */ var tables_ResourceTablevue_type_script_lang_js_ = (ResourceTablevue_type_script_lang_js_); 
// EXTERNAL MODULE: ./src/components/endpoints/tables/ResourceTable.vue?vue&type=style&index=0&id=cabf33a4&scoped=true&lang=css&
var ResourceTablevue_type_style_index_0_id_cabf33a4_scoped_true_lang_css_ = __webpack_require__("46fd");

// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/endpoints/tables/ResourceTable.vue






/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  tables_ResourceTablevue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  "cabf33a4",
  null
  
)

/* harmony default export */ var ResourceTable = __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "ac4d":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("3a72")('asyncIterator');


/***/ }),

/***/ "d4c0":
/***/ (function(module, exports, __webpack_require__) {

// all enumerable object keys, includes symbols
var getKeys = __webpack_require__("0d58");
var gOPS = __webpack_require__("2621");
var pIE = __webpack_require__("52a7");
module.exports = function (it) {
  var result = getKeys(it);
  var getSymbols = gOPS.f;
  if (getSymbols) {
    var symbols = getSymbols(it);
    var isEnum = pIE.f;
    var i = 0;
    var key;
    while (symbols.length > i) if (isEnum.call(it, key = symbols[i++])) result.push(key);
  } return result;
};


/***/ }),

/***/ "d8d6":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("1654");
__webpack_require__("6c1c");
module.exports = __webpack_require__("ccb9").f('iterator');


/***/ }),

/***/ "e671":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/tables/ObservationsTable.vue?vue&type=template&id=13ffc694&scoped=true&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"table-wrapper"},[_c('b-table',_vm._b({attrs:{"fields":_vm.fields},scopedSlots:_vm._u([{key:"head(select)",fn:function(data){return [_c('div',{staticClass:"d-flex"},[_c('b-form-checkbox',{attrs:{"indeterminate":_vm.indeterminate},on:{"change":_vm.toggleAll},model:{value:(_vm.allSelected),callback:function ($$v) {_vm.allSelected=$$v},expression:"allSelected"}},[_c('span',[_vm._v(_vm._s(data.label))])])],1),_c('b-button',{staticClass:"w-100 text-nowrap",attrs:{"size":"sm","variant":"outline-secondary","disabled":!(_vm.allSelected||_vm.indeterminate)},on:{"click":_vm.showExportPreview}},[_c('font-awesome-icon',{staticClass:"icon",attrs:{"icon":['fas', 'file-download'],"fixed-width":""}}),_c('span',[_vm._v("export")])],1)]}},{key:"cell(select)",fn:function(data){return [(_vm.isLoincCode(data.item))?_c('div',{staticClass:"select-wrapper"},[(!_vm.isMapped(data.item['coding_code']))?_c('b-form-checkbox',{attrs:{"disabled":!data.item['coding_code'],"value":data.item['coding_code'],"switch":"","size":"lg"},model:{value:(_vm.selected_codes),callback:function ($$v) {_vm.selected_codes=$$v},expression:"selected_codes"}}):_vm._e()],1):_vm._e()]}},{key:"cell(coding_display)",fn:function(data){return [_c('span',{domProps:{"innerHTML":_vm._s(_vm.$attrs.markText(data.value))}}),(data.item['coding_text']!=data.value)?[_c('span',{staticClass:"font-italic small d-block"},[_vm._v("("+_vm._s(data.item['coding_text'])+")")])]:_vm._e()]}},{key:"cell(coding_code)",fn:function(data){return [_c('span',{domProps:{"innerHTML":_vm._s(_vm.$attrs.markText(data.value))}}),(_vm.isLoincCode(data.item))?_c('div',{staticClass:"small"},[(!_vm.isMapped(data.value))?_c('b-badge',{attrs:{"variant":"warning"}},[_vm._v("not mapped")]):_c('b-badge',{attrs:{"variant":"info"}},[_vm._v("mapped")])],1):_vm._e()]}},{key:"cell()",fn:function(data){return [(_vm.isObject(data.value))?_c('div',[_c('Node',{attrs:{"payload":data.value}})],1):_c('div',[_c('span',{domProps:{"innerHTML":_vm._s(_vm.$attrs.markText(data.value))}})])]}}])},'b-table',Object.assign({}, _vm.$attrs, _vm.$props),false)),_c('b-alert',{attrs:{"show":true}},[_vm._v("\n    Please note that results with multiple coding systems are split in separated rows.\n  ")]),_c('b-modal',{ref:"export-modal",attrs:{"title":"Export codes"},scopedSlots:_vm._u([{key:"modal-footer",fn:function(ref){
var cancel = ref.cancel;
return [_c('b-button',{attrs:{"size":"sm","variant":"secondary"},on:{"click":function($event){return cancel()}}},[_vm._v("\n            Cancel\n          ")]),_c('b-button',{attrs:{"size":"sm","variant":"success","disabled":!_vm.exportFileName},on:{"click":function($event){return _vm.onExportClicked()}}},[_c('font-awesome-icon',{attrs:{"icon":['fas', 'file-export'],"fixed-width":""}}),_c('span',{staticClass:"ml-2"},[_vm._v("Export")])],1)]}}])},[_c('div',[_c('p',{staticClass:"mb-2"},[_vm._v("Do you want to export the current selection?")]),_c('b-table',{staticClass:"mb-2",attrs:{"small":"","items":_vm.exportItems,"striped":"","bordered":"","sticky-header":"100"},scopedSlots:_vm._u([{key:"cell(code)",fn:function(data){return [_c('b',{staticClass:"text-nowrap"},[_vm._v(_vm._s(data.value))])]}}])}),_c('b-input',{attrs:{"placeholder":"enter a name for this selection..."},model:{value:(_vm.exportFileName),callback:function ($$v) {_vm.exportFileName=(typeof $$v === 'string'? $$v.trim(): $$v)},expression:"exportFileName"}})],1)])],1)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/components/endpoints/tables/ObservationsTable.vue?vue&type=template&id=13ffc694&scoped=true&

// EXTERNAL MODULE: ./node_modules/core-js/modules/es7.symbol.async-iterator.js
var es7_symbol_async_iterator = __webpack_require__("ac4d");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.symbol.js
var es6_symbol = __webpack_require__("8a81");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.string.iterator.js
var es6_string_iterator = __webpack_require__("5df3");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.array.from.js
var es6_array_from = __webpack_require__("1c4c");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.function.name.js
var es6_function_name = __webpack_require__("7f7f");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.regexp.to-string.js
var es6_regexp_to_string = __webpack_require__("6b54");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/slicedToArray.js + 3 modules
var slicedToArray = __webpack_require__("768b");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es7.object.entries.js
var es7_object_entries = __webpack_require__("ffc1");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/toConsumableArray.js + 3 modules
var toConsumableArray = __webpack_require__("75fc");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.regexp.match.js
var es6_regexp_match = __webpack_require__("4917");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.object.assign.js
var es6_object_assign = __webpack_require__("f751");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/typeof.js
var esm_typeof = __webpack_require__("7618");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.array.iterator.js
var es6_array_iterator = __webpack_require__("cadf");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.object.keys.js
var es6_object_keys = __webpack_require__("456d");

// EXTERNAL MODULE: ./node_modules/core-js/modules/web.dom.iterable.js
var web_dom_iterable = __webpack_require__("ac6a");

// EXTERNAL MODULE: ./src/components/Payload/Node.vue + 4 modules
var Node = __webpack_require__("6fc5");

// EXTERNAL MODULE: ./node_modules/moment/moment.js
var moment = __webpack_require__("c1df");
var moment_default = /*#__PURE__*/__webpack_require__.n(moment);

// EXTERNAL MODULE: ./src/variables.js
var variables = __webpack_require__("7eac");

// CONCATENATED MODULE: ./src/libraries/Utils.js



var downloadBlob = function downloadBlob(text, filename) {
  var url = window.URL.createObjectURL(new Blob([text]));
  var link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

var downloadDataURI = function downloadDataURI(text) {
  var filename = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'export.txt';
  var element = document.createElement('a');
  element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
  element.setAttribute('download', filename);
  element.style.display = 'none';
  document.body.appendChild(element);
  element.click();
  document.body.removeChild(element);
};

var Utils_formatDate = function formatDate(date) {
  if (!date) return '';
  var date_string = moment_default()(date).format(variables["a" /* date_format */]); // date_format defined in variables

  return date_string;
};


// EXTERNAL MODULE: ./src/components/endpoints/tables/ResourceTable.vue + 4 modules
var ResourceTable = __webpack_require__("8ca8");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/tables/ObservationsTable.vue?vue&type=script&lang=js&
















function _createForOfIteratorHelper(o) { if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (o = _unsupportedIterableToArray(o))) { var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var it, normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

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
 * helper function that flattens an object.
 * specifically, this is being used to flattedn the coding
 * systems of each observation
 */

var ObservationsTablevue_type_script_lang_js_flatten = function flatten(obj) {
  var flattened = {};
  Object.keys(obj).forEach(function (key) {
    if (Object(esm_typeof["a" /* default */])(obj[key]) === 'object' && obj[key] !== null) {
      Object.assign(flattened, flatten(obj[key]));
    } else {
      flattened[key] = obj[key];
    }
  });
  return flattened;
};

/* harmony default export */ var ObservationsTablevue_type_script_lang_js_ = ({
  extends: ResourceTable["default"],
  components: {
    Node: Node["a" /* default */]
  },
  data: function data() {
    return {
      exportFileName: 'codes.txt',
      allSelected: false,
      indeterminate: false,
      selected_codes: [],
      custom_fields: [{
        key: 'select',
        sortable: false
      }, {
        key: 'coding_display',
        sortable: true
      }, // {key: 'coding_text', sortable: true,},
      {
        key: 'coding_code',
        sortable: true
      }, {
        key: 'value',
        sortable: true
      }, {
        key: 'valueUnit',
        sortable: true
      }, {
        key: 'timestamp',
        sortable: true
      }, {
        key: 'coding_system',
        sortable: true
      } // {key: 'loinc', sortable: true,},
      // {key: 'loinc-codes', sortable: true,},
      // {key: 'valueQuantity', sortable: true,},
      // {key: 'valueBoolean', sortable: true,},
      // {key: 'valueCodeableConcept', sortable: true,},
      // {key: 'valueDateTime', sortable: true,},
      // {key: 'valueInteger', sortable: true,},
      // {key: 'valuePeriod', sortable: true,},
      // {key: 'valueRange', sortable: true,},
      // {key: 'valueRatio', sortable: true,},
      // {key: 'valueSampledData', sortable: true,},
      // {key: 'valueString', sortable: true,},
      // {key: 'valueTime', sortable: true,},

      /* {
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
      } */
      ]
    };
  },
  methods: {
    isObject: function isObject(value) {
      return Object(esm_typeof["a" /* default */])(value) == 'object';
    },
    isNonArrayObject: function isNonArrayObject(value) {
      if (Array.isArray(value)) return false;
      return this.isObject(value);
    },
    shouldDisplayCell: function shouldDisplayCell(value) {
      if (Array.isArray(value) && value.length < 1) return false;
      return true;
    },
    isLoincCode: function isLoincCode(data) {
      var system = data.coding_system;
      if (!system) return;
      return system.match(/loinc/i);
    },
    isMapped: function isMapped(code) {
      return this.cdpMappingNames.indexOf(code) > 0;
    },
    toggleAll: function toggleAll(checked) {
      this.selected_codes = checked ? Object.keys(this.selectable).slice() : [];
    },
    showExportPreview: function showExportPreview() {
      var modal = this.$refs['export-modal'];
      if (modal) modal.show();
    },
    onExportClicked: function onExportClicked() {
      var text = '';

      var _iterator = _createForOfIteratorHelper(this.exportItems),
          _step;

      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var _step$value = _step.value,
              code = _step$value.code,
              label = _step$value.label;
          text += "".concat(code, ", ").concat(label, "\n");
        }
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }

      downloadBlob(text, this.exportFileName);
      var modal = this.$refs['export-modal'];
      if (modal) modal.hide();
    }
  },
  computed: {
    /**
     * transform the observation so that the code is flat
     * instead of being a nested object like this {coding: {code,system,display}, text}
     */
    itemsProxy: function itemsProxy() {
      var items = Object(toConsumableArray["a" /* default */])(this.items); // ilst of items coming from $attrs, specifically from ResourceTableWrapper


      var flatCodeItems = items.map(function (item) {
        var _item$code = item.code,
            code = _item$code === void 0 ? {} : _item$code;

        for (var _i = 0, _Object$entries = Object.entries(ObservationsTablevue_type_script_lang_js_flatten(code)); _i < _Object$entries.length; _i++) {
          var _Object$entries$_i = Object(slicedToArray["a" /* default */])(_Object$entries[_i], 2),
              key = _Object$entries$_i[0],
              value = _Object$entries$_i[1];

          item["coding_".concat(key)] = value;
        }

        return item;
      });
      return flatCodeItems;
    },
    allChecked: {
      get: function get() {
        return this.selected_codes.length == Object.keys(this.selectable).length;
      },
      set: function set(value) {
        console.log('allChecked', value);
      }
    },
    intermediateChecked: {
      get: function get() {
        if (this.selected_codes.length == 0) return false;
        if (this.selected_codes.length == Object.keys(this.selectable).length) return false;
        return true;
      },
      set: function set(value) {
        console.log('intermediate', value);
      }
    },

    /**
     * list of LOINC codes that can be selected (along with label)
     */
    selectable: function selectable() {
      var _this = this;

      var codes = {};
      this.itemsProxy.forEach(function (item) {
        if (!_this.isLoincCode(item)) return;
        var code = item.coding_code,
            display = item.coding_display;
        if (!code || Object.keys(codes).indexOf(code) >= 0) return;
        var mapping = _this.cdpMappingNames;
        if (mapping.indexOf(code) >= 0) return;
        codes[code] = display;
      });
      return codes;
    },

    /**
     * used to display the selected codes in the export table
     */
    exportItems: function exportItems() {
      var items = [];

      var selected_codes = Object(toConsumableArray["a" /* default */])(this.selected_codes);

      for (var _i2 = 0, _Object$entries2 = Object.entries(this.selectable); _i2 < _Object$entries2.length; _i2++) {
        var _Object$entries2$_i = Object(slicedToArray["a" /* default */])(_Object$entries2[_i2], 2),
            code = _Object$entries2$_i[0],
            label = _Object$entries2$_i[1];

        var entry = {
          code: code,
          label: label
        };
        if (selected_codes.indexOf(code) >= 0 && items.indexOf(entry) < 0) items.push(entry);
      }

      return items;
    },
    fields: function fields() {
      return this.custom_fields;
    },
    cdpMappingNames: function cdpMappingNames() {
      var names = this.$store.getters['project/mappingSourceNames'];
      return names;
    }
  },
  watch: {
    items: {
      immediate: true,

      /**
       * reset the selected codes when the items are updated
       */
      handler: function handler() {
        this.selected_codes = [];
      }
    },
    selected_codes: function selected_codes(newValue) {
      // Handle changes in individual flavour checkboxes
      if (newValue.length === 0) {
        this.indeterminate = false;
        this.allSelected = false;
      } else if (newValue.length === Object.keys(this.selectable).length) {
        this.indeterminate = false;
        this.allSelected = true;
      } else {
        this.indeterminate = true;
        this.allSelected = false;
      }
    }
  }
});
// CONCATENATED MODULE: ./src/components/endpoints/tables/ObservationsTable.vue?vue&type=script&lang=js&
 /* harmony default export */ var tables_ObservationsTablevue_type_script_lang_js_ = (ObservationsTablevue_type_script_lang_js_); 
// EXTERNAL MODULE: ./src/components/endpoints/tables/ObservationsTable.vue?vue&type=style&index=0&id=13ffc694&scoped=true&lang=css&
var ObservationsTablevue_type_style_index_0_id_13ffc694_scoped_true_lang_css_ = __webpack_require__("15a5");

// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/endpoints/tables/ObservationsTable.vue






/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  tables_ObservationsTablevue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  "13ffc694",
  null
  
)

/* harmony default export */ var ObservationsTable = __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "f3d9":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ })

}]);
//# sourceMappingURL=mapping_helper_vue.common.2.js.map