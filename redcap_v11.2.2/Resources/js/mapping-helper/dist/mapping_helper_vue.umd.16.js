((typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] = (typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] || []).push([[16],{

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

/***/ "c280":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/forms/DSTU2/DemographicsForm.vue?vue&type=template&id=43fd967c&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div')}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/components/endpoints/forms/DSTU2/DemographicsForm.vue?vue&type=template&id=43fd967c&

// EXTERNAL MODULE: ./src/components/endpoints/forms/BaseResourceForm.vue + 4 modules
var BaseResourceForm = __webpack_require__("2f12");

// EXTERNAL MODULE: ./src/variables.js
var variables = __webpack_require__("7eac");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/endpoints/forms/DSTU2/DemographicsForm.vue?vue&type=script&lang=js&
//
//
//
//
//


/* harmony default export */ var DemographicsFormvue_type_script_lang_js_ = ({
  extends: BaseResourceForm["a" /* default */],
  data: function data() {
    return {
      fhir_category: variables["b" /* fhir_categories */].DEMOGRAPHICS //base URL for the FHIR resource

    };
  }
});
// CONCATENATED MODULE: ./src/components/endpoints/forms/DSTU2/DemographicsForm.vue?vue&type=script&lang=js&
 /* harmony default export */ var DSTU2_DemographicsFormvue_type_script_lang_js_ = (DemographicsFormvue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/endpoints/forms/DSTU2/DemographicsForm.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  DSTU2_DemographicsFormvue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var DemographicsForm = __webpack_exports__["default"] = (component.exports);

/***/ })

}]);
//# sourceMappingURL=mapping_helper_vue.umd.16.js.map