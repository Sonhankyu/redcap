<template>
    <nav id="dropdown" role="navigation" :class="{'show': show}">
        <button @click="show=!show" @blur="onBlur" >events</button>
        <ul>
            <li v-for="(arm, arm_key) in project.arms" :key="arm_key">
                <span>{{arm.name}}</span>
                <ul>
                    <li v-for="(event, event_key) in arm.events" :key="event_key">
                        <span>{{event.descrip}}</span>
                        <ul v-if="project.events_forms[event_key]">
                            <li v-for="(form, forms_index) in project.events_forms[event_key]" :key="forms_index">
                                <span>{{project.forms[form].menu}}</span>
                                <ul>
                                    <li v-for="(field, field_key) in project.forms[form].fields" :key="field_key">
                                        <span>{{field}} ({{field_key}})</span>
                                    </li>
                                </ul>

                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</template>

<script>
import { mapState } from 'vuex'

export default {
    data() {
        return {
            open: [],
            show: false,
        }
    },
    computed: {
        ...mapState({
            project: state => state.app_settings.project,
        }),
    },
    mounted: function () {
        this.$el.$on('bv::dropdown::show', bvEvent => {
            this.open.push(bvEvent.vueTarget)
        })
        this.$el.$on('bv::dropdown::hide', bvEvent => {
            let index = this.open.indexOf(bvEvent.vueTarget)
            if((index<0 || index==(open.length-1))) {
                let open = [...this.open]
                open.splice(index, 1)
                this.open = open
            }else {
                // check for any focused element and close all if no children has focus
                const focused_elements = this.$refs.wrapper.querySelectorAll('*:focus')
                if(focused_elements.length>0) bvEvent.preventDefault()
                else this.open = []
            }
        })
    },
    methods: {
        onBlur(event) {
            console.log(event)
            // this.show = false
        }
    }
}
</script>

<style scoped>

#dropdown {
    position: relative;
    display: inline-block;
    z-index: 1;
    font-weight: 400;
    font-size: 16px;
}
#dropdown.show > button {
    background-color: rgba(0,0,0,0.1);
}
#dropdown > button {
    background-color: rgba(0,0,0,0);
    margin-bottom: 4px;
    border-style: solid;
    border-width: 1px;
    border-color: #6c757d;
    border-radius: 3px;
    padding: .375rem .75rem;
    display: inline-flex;
    align-items: center;
}
#dropdown > button:after {
    display: inline-block;
    margin-left: .255em;
    vertical-align: .255em;
    content: "";
    border-top: .3em solid;
    border-right: .3em solid transparent;
    border-bottom: 0;
    border-left: .3em solid transparent;

}
#dropdown.show > ul {
    display: block;
}
#dropdown ul {
    margin-top: -2px;
    background-color: white;
    padding: .5rem 0;
    display: none;
    border-radius: 3px;
    color: #6c757d;
    border-style: solid;
    border-width: 1px;
    border-color: #6c757d;
    left: 0;
    position: absolute;
    top: 0px;
    left: 80%;
    z-index: 1;
}

#dropdown > ul {
    top: 100%;
    left: 0;
}
li {
    padding: .25rem 1.5rem;
    position: relative;
    display: block;
    white-space: nowrap;
    min-width: 200px;
    color: #000;
}
#dropdown  li:hover > ul {
    display: block;

}
#dropdown  li:hover {
    cursor: pointer;
    background-color: #f8f9fa;
}
</style>