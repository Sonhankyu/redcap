<template>
  <div >
    <b-form-group label="From" label-for="date-start-input" label-cols="2">
      <b-input-group>
        <b-form-input id="date-start-input"
          :state="!$v.from.$invalid" @input="$emit('update:from', $event)"
          :value="from" placeholder="YYYY-MM-DD" autocomplete="off"/>
        <b-input-group-append>
          <b-form-datepicker
            @input="$emit('update:from', $event)"
            :value="from" :max="to"
            v-bind="defaultAttributes"
          ></b-form-datepicker>
        </b-input-group-append>
      </b-input-group>
    </b-form-group>

    <b-form-group label="To" label-for="date-end-input" label-cols="2">
      <b-input-group >
        <b-form-input id="date-end-input"
        :state="!$v.to.$invalid" @input="$emit('update:to', $event)"
        :value="to" placeholder="YYYY-MM-DD" autocomplete="off"/>
        <b-input-group-append>
          <b-form-datepicker
            @input="$emit('update:to', $event)"
            :value="to" :min="from"
            v-bind="defaultAttributes"
          ></b-form-datepicker>
        </b-input-group-append>
      </b-input-group>
    </b-form-group>
  </div>
</template>

<script>
import moment from 'moment'

const isValidDate = (value) => {
  if(value=='' || value==null) return true
  const validformat = 'YYYY-MM-DD'
  return moment(value, validformat).format(validformat) === value
}
export default {
  data() {
    return {
      defaultAttributes: {
        'show-decade-nav': true,
        'button-only': true,
        'right': true,
        'today-button': true,
        'reset-button': true,
        'close-button': true,
        'date-format-options': { year: 'numeric', month: '2-digit', day: '2-digit' }
      }
    }
  },
  props: {
    from: {
      type: [Date,String],
      default: null
    },
    to: {
      type: [Date,String],
      default: null
    }
  },
  validations() {
    return {
      from: {
        isValidDate
      },
      to: {
        isValidDate
      }
    }
  },
}
</script>

<style>

</style>