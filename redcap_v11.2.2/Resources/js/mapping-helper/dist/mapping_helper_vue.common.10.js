((typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] = (typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] || []).push([[10],{

/***/ "00cf":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/pages/AccessTokens.vue?vue&type=template&id=8f78689a&scoped=true&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('AccessTokensTable'),(_vm.standalone_launch_url)?_c('div',[_c('b-button',{attrs:{"href":_vm.standalone_launch_url,"variant":"primary","size":"sm"}},[_vm._v("Standalone launch")])],1):_vm._e()],1)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/pages/AccessTokens.vue?vue&type=template&id=8f78689a&scoped=true&

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/AccessTokens/AccessTokensTable.vue?vue&type=template&id=51910242&scoped=true&
var AccessTokensTablevue_type_template_id_51910242_scoped_true_render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('b-table',{staticClass:"access-tokens-table",attrs:{"striped":"","bordered":"","hover":"","small":"","fields":_vm.fields,"items":_vm.tokens},scopedSlots:_vm._u([{key:"cell(index)",fn:function(data){return [_c('div',[_c('span',[_vm._v(_vm._s(data.index + 1))])])]}},{key:"cell(access_token)",fn:function(data){return [_c('div',{staticClass:"d-flex"},[_c('b-button',{attrs:{"size":"sm"},on:{"click":function($event){return _vm.copyToClipboard(data.value, ((data.field.label) + " copied"))}}},[_c('font-awesome-icon',{attrs:{"icon":['fas', 'copy']}})],1),_c('span',{staticClass:"ml-2 cell-info"},[_vm._v(_vm._s(data.value))])],1)]}},{key:"cell(refresh_token)",fn:function(data){return [_c('div',{staticClass:"d-flex"},[_c('b-button',{attrs:{"size":"sm"},on:{"click":function($event){return _vm.copyToClipboard(data.value, ((data.field.label) + " copied"))}}},[_c('font-awesome-icon',{attrs:{"icon":['fas', 'copy']}})],1),_c('span',{staticClass:"ml-2 cell-info"},[_vm._v(_vm._s(data.value))])],1)]}},{key:"cell()",fn:function(data){return [_c('span',{staticClass:"cell-info",attrs:{"title":data.value}},[_vm._v(_vm._s(data.value))])]}}])})],1)}
var AccessTokensTablevue_type_template_id_51910242_scoped_true_staticRenderFns = []


// CONCATENATED MODULE: ./src/components/AccessTokens/AccessTokensTable.vue?vue&type=template&id=51910242&scoped=true&

// EXTERNAL MODULE: ./node_modules/regenerator-runtime/runtime.js
var runtime = __webpack_require__("96cf");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/asyncToGenerator.js
var asyncToGenerator = __webpack_require__("3b8d");

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/toConsumableArray.js + 3 modules
var toConsumableArray = __webpack_require__("75fc");

// EXTERNAL MODULE: ./node_modules/core-js/modules/web.dom.iterable.js
var web_dom_iterable = __webpack_require__("ac6a");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.array.iterator.js
var es6_array_iterator = __webpack_require__("cadf");

// EXTERNAL MODULE: ./node_modules/core-js/modules/es6.object.keys.js
var es6_object_keys = __webpack_require__("456d");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/AccessTokens/AccessTokensTable.vue?vue&type=script&lang=js&






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
/* harmony default export */ var AccessTokensTablevue_type_script_lang_js_ = ({
  computed: {
    tokens: function tokens() {
      return this.$store.state.user.tokens;
    },
    fields: function fields() {
      // extract all keys
      var keys = this.tokens.reduce(function (accumulator, token) {
        var keys = Object.keys(token);
        return [].concat(Object(toConsumableArray["a" /* default */])(accumulator), Object(toConsumableArray["a" /* default */])(keys));
      }, []); // keys.splice(0, 0, 'index')

      return keys;
    }
  },
  methods: {
    copyToClipboard: function () {
      var _copyToClipboard = Object(asyncToGenerator["a" /* default */])( /*#__PURE__*/regeneratorRuntime.mark(function _callee(text, message) {
        var result;
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                message = message || 'text copied';
                _context.next = 3;
                return navigator.clipboard.writeText(text);

              case 3:
                result = _context.sent;
                this.$bvToast.toast(message, {
                  title: 'Success',
                  toaster: 'b-toaster-top-right',
                  solid: false,
                  //translucent
                  autoHideDelay: 1500 // variant: 'light',

                });

              case 5:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this);
      }));

      function copyToClipboard(_x, _x2) {
        return _copyToClipboard.apply(this, arguments);
      }

      return copyToClipboard;
    }()
  }
});
// CONCATENATED MODULE: ./src/components/AccessTokens/AccessTokensTable.vue?vue&type=script&lang=js&
 /* harmony default export */ var AccessTokens_AccessTokensTablevue_type_script_lang_js_ = (AccessTokensTablevue_type_script_lang_js_); 
// EXTERNAL MODULE: ./src/components/AccessTokens/AccessTokensTable.vue?vue&type=style&index=0&id=51910242&scoped=true&lang=css&
var AccessTokensTablevue_type_style_index_0_id_51910242_scoped_true_lang_css_ = __webpack_require__("bcbd");

// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/AccessTokens/AccessTokensTable.vue






/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  AccessTokens_AccessTokensTablevue_type_script_lang_js_,
  AccessTokensTablevue_type_template_id_51910242_scoped_true_render,
  AccessTokensTablevue_type_template_id_51910242_scoped_true_staticRenderFns,
  false,
  null,
  "51910242",
  null
  
)

/* harmony default export */ var AccessTokensTable = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/pages/AccessTokens.vue?vue&type=script&lang=js&
//
//
//
//
//
//
//
//
//

/* harmony default export */ var AccessTokensvue_type_script_lang_js_ = ({
  components: {
    AccessTokensTable: AccessTokensTable
  },
  computed: {
    standalone_launch_url: function standalone_launch_url() {
      return this.$store.state.app_settings.standalone_launch_url;
    },
    lang: function lang() {
      var lang = this.$store.state.app_settings.lang;
      return lang;
    }
  }
});
// CONCATENATED MODULE: ./src/pages/AccessTokens.vue?vue&type=script&lang=js&
 /* harmony default export */ var pages_AccessTokensvue_type_script_lang_js_ = (AccessTokensvue_type_script_lang_js_); 
// CONCATENATED MODULE: ./src/pages/AccessTokens.vue





/* normalize component */

var AccessTokens_component = Object(componentNormalizer["a" /* default */])(
  pages_AccessTokensvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  "8f78689a",
  null
  
)

/* harmony default export */ var AccessTokens = __webpack_exports__["default"] = (AccessTokens_component.exports);

/***/ }),

/***/ "07a4":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

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

/***/ "bcbd":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AccessTokensTable_vue_vue_type_style_index_0_id_51910242_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("07a4");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AccessTokensTable_vue_vue_type_style_index_0_id_51910242_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AccessTokensTable_vue_vue_type_style_index_0_id_51910242_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */
 /* unused harmony default export */ var _unused_webpack_default_export = (_node_modules_mini_css_extract_plugin_dist_loader_js_ref_6_oneOf_1_0_node_modules_css_loader_index_js_ref_6_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AccessTokensTable_vue_vue_type_style_index_0_id_51910242_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ })

}]);
//# sourceMappingURL=mapping_helper_vue.common.10.js.map