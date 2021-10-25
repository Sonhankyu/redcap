((typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] = (typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] || []).push([[14],{

/***/ "1f3b":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/forms/R4/ConditionsForm.vue?vue&type=template&id=276cb2e4&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('b-form-group',{attrs:{"label":"Status","label-for":"checkbox-group-1","label-cols":"2"}},[_c('b-form-checkbox-group',{staticClass:"mt-2",attrs:{"id":"checkbox-group-1","options":_vm.status_options,"name":"status"},model:{value:(_vm.selected),callback:function ($$v) {_vm.selected=$$v},expression:"selected"}})],1)],1)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/components/endpoints/forms/R4/ConditionsForm.vue?vue&type=template&id=276cb2e4&

// EXTERNAL MODULE: ./node_modules/@babel/runtime-corejs2/helpers/esm/toConsumableArray.js + 3 modules
var toConsumableArray = __webpack_require__("75fc");

// EXTERNAL MODULE: ./src/components/endpoints/forms/BaseResourceForm.vue + 4 modules
var BaseResourceForm = __webpack_require__("2f12");

// EXTERNAL MODULE: ./src/variables.js
var variables = __webpack_require__("7eac");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/forms/R4/ConditionsForm.vue?vue&type=script&lang=js&

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


var status_options = [{
  text: 'active',
  value: 'active'
}, {
  text: 'recurrence',
  value: 'recurrence'
}, {
  text: 'relapse',
  value: 'relapse'
}, {
  text: 'inactive',
  value: 'inactive'
}, {
  text: 'remission',
  value: 'remission'
}, {
  text: 'resolved',
  value: 'resolved'
}];
/* harmony default export */ var ConditionsFormvue_type_script_lang_js_ = ({
  extends: BaseResourceForm["a" /* default */],
  data: function data() {
    return {
      fhir_category: variables["b" /* fhir_categories */].CONDITION,
      //base URL for the FHIR resource
      options: {},
      selected: [],
      // Must be an array reference!
      status_options: [].concat(status_options)
    };
  },
  watch: {
    selected: {
      immediate: true,

      /**
       * update the status whenever the
       * status_options are changed
       */
      handler: function handler() {
        var selected = Object(toConsumableArray["a" /* default */])(this.selected);

        if (selected.length < 1) {
          delete this.options['clinical-status'];
        } else {
          this.options['clinical-status'] = selected.join(',');
        }
      }
    }
  }
});
// CONCATENATED MODULE: ./src/components/endpoints/forms/R4/ConditionsForm.vue?vue&type=script&lang=js&
 /* harmony default export */ var R4_ConditionsFormvue_type_script_lang_js_ = (ConditionsFormvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/endpoints/forms/R4/ConditionsForm.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  R4_ConditionsFormvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var ConditionsForm = __webpack_exports__["default"] = (component.exports);

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

/***/ })

}]);
//# sourceMappingURL=mapping_helper_vue.common.14.js.map