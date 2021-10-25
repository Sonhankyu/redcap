((typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] = (typeof self !== 'undefined' ? self : this)["webpackJsonpmapping_helper_vue"] || []).push([[1],{

/***/ "9db7":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"b3a7e80c-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/DateRange.vue?vue&type=template&id=74dc344e&
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('b-form-group',{attrs:{"label":"From","label-for":"date-start-input","label-cols":"2"}},[_c('b-input-group',[_c('b-form-input',{attrs:{"id":"date-start-input","state":!_vm.$v.from.$invalid,"value":_vm.from,"placeholder":"YYYY-MM-DD","autocomplete":"off"},on:{"input":function($event){return _vm.$emit('update:from', $event)}}}),_c('b-input-group-append',[_c('b-form-datepicker',_vm._b({attrs:{"value":_vm.from,"max":_vm.to},on:{"input":function($event){return _vm.$emit('update:from', $event)}}},'b-form-datepicker',_vm.defaultAttributes,false))],1)],1)],1),_c('b-form-group',{attrs:{"label":"To","label-for":"date-end-input","label-cols":"2"}},[_c('b-input-group',[_c('b-form-input',{attrs:{"id":"date-end-input","state":!_vm.$v.to.$invalid,"value":_vm.to,"placeholder":"YYYY-MM-DD","autocomplete":"off"},on:{"input":function($event){return _vm.$emit('update:to', $event)}}}),_c('b-input-group-append',[_c('b-form-datepicker',_vm._b({attrs:{"value":_vm.to,"min":_vm.from},on:{"input":function($event){return _vm.$emit('update:to', $event)}}},'b-form-datepicker',_vm.defaultAttributes,false))],1)],1)],1)],1)}
var staticRenderFns = []


// CONCATENATED MODULE: ./src/components/DateRange.vue?vue&type=template&id=74dc344e&

// EXTERNAL MODULE: ./node_modules/moment/moment.js
var moment = __webpack_require__("c1df");
var moment_default = /*#__PURE__*/__webpack_require__.n(moment);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/DateRange.vue?vue&type=script&lang=js&
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


var DateRangevue_type_script_lang_js_isValidDate = function isValidDate(value) {
  if (value == '' || value == null) return true;
  var validformat = 'YYYY-MM-DD';
  return moment_default()(value, validformat).format(validformat) === value;
};

/* harmony default export */ var DateRangevue_type_script_lang_js_ = ({
  data: function data() {
    return {
      defaultAttributes: {
        'show-decade-nav': true,
        'button-only': true,
        'right': true,
        'today-button': true,
        'reset-button': true,
        'close-button': true,
        'date-format-options': {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit'
        }
      }
    };
  },
  props: {
    from: {
      type: [Date, String],
      default: null
    },
    to: {
      type: [Date, String],
      default: null
    }
  },
  validations: function validations() {
    return {
      from: {
        isValidDate: DateRangevue_type_script_lang_js_isValidDate
      },
      to: {
        isValidDate: DateRangevue_type_script_lang_js_isValidDate
      }
    };
  }
});
// CONCATENATED MODULE: ./src/components/DateRange.vue?vue&type=script&lang=js&
 /* harmony default export */ var components_DateRangevue_type_script_lang_js_ = (DateRangevue_type_script_lang_js_); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/components/DateRange.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  components_DateRangevue_type_script_lang_js_,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var DateRange = __webpack_exports__["default"] = (component.exports);

/***/ })

}]);
//# sourceMappingURL=mapping_helper_vue.umd.1.js.map