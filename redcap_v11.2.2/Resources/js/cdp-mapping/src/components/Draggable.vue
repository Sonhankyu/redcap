<template>
  <div>

    <div>
        <transition-group ref="test" type="transition" name="sort-list">
            <slot />
        </transition-group>
    </div>
    <div ref="draggable_container">

    </div>

    <div>
        {{items}}
        <p>selected_index: {{selected_index}}</p>
        <p>dropped_index: {{dropped_index}}</p>
        <p>over_index: {{over_index}}</p>
    </div>

  </div>
</template>

<script>

const array_move = (arr, old_index, new_index) => {
    if (new_index >= arr.length) {
        var k = new_index - arr.length + 1;
        while (k--) {
            arr.push(undefined);
        }
    }
    arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
    return arr; // for testing
};

export default {
    data() {
        return {
            items: [],
            selected_index: null,
            dropped_index: null,
            over_index: null,
        }
    },
    mounted() {
        let container = this.$refs.draggable_container
        console.log(container, this.$refs)
        const elements = this.$slots.default.forEach(component => {
            let element = component.elm
            let clone = element.cloneNode(true)

            clone.draggable = true
            clone.addEventListener('click', this.onClick)
            clone.addEventListener('dragstart', (event) => this.onDragStart(event))
            clone.addEventListener('dragend', (event) => this.onDragStop(event))
            container.appendChild(clone)
            // element.addEventListener('dragover', (event) => this.onDragOver(event))
        })
        // this.$el.addEventListener('dragover', (event) => this.onDragOver(event))
    },
    beforeDestroy() {
        let container = this.$refs.draggable_container
         container.childNodes.forEach(element => {
             console.log(element)
            element.removeEventListener('click', this.onClick)
            element.removeEventListener('dragstart', (event) => this.onDragStart(event))
            element.removeEventListener('dragend', (event) => this.onDragStop(event))
            // element.removeEventListener('dragover', (event) => this.onDragOver(event))
        })
        this.$el.removeEventListener('dragover', (event) => this.onDragOver(event))
    },
    props: {
        value: {
            type: Array,
            default: () => []
        }   
    },
    computed: {
        draggableElements() {
            return this.$refs.draggable_container.childNodes
        },
    },
    methods: {
        onClick(event) {
            const element = event.toElement
            const node_index = this.getDraggableIndex(element)
            console.log(element, event, typeof element, node_index, element.nextElementSibling)
        },
        onMove(e) {
            console.log(e)
            this.$emit('input', this.value)
        },
        getDraggableIndex(element) {
            return [...this.draggableElements].indexOf(element)
        },
        onDragStart(event) {
            // event.preventDefault()
            const element = event.target
            const node_index = this.getDraggableIndex(element)
            this.selected_index = node_index
            // console.log(event.target, event.target.classList, component)
            event.target.classList.add('dragging')
        },
        onDragStop(event) {
            // event.preventDefault()
            const element = event.target
            const node_index = this.getDraggableIndex(element)
            this.dropped_index = node_index

            const boundingRect = element.getBoundingClientRect()
            const elementY = boundingRect.y
            const elementHeight = boundingRect.height
            const mouseY = event.screenY


            this.dragging = null

            event.target.classList.remove('dragging')
            this.$emit('input', [...this.items])
        },
        onDragOver(event) {
            event.preventDefault()
            const dragged_element = this.draggableElements[this.selected_index]

            const container = dragged_element.parentNode
            const element = event.target
            const node_index = this.getDraggableIndex(element)
            this.over_index = node_index

            const over_element = this.draggableElements[this.over_index]

            if(dragged_element==over_element) return
            if(!over_element) return

            const boundingRect = over_element.getBoundingClientRect()
            const elementHeight = boundingRect.height
            const elementY = boundingRect.y
            const mouseY = event.clientY

            let move_index = node_index
            let move_after = mouseY>=(elementY+elementHeight/2)
            if(move_after) move_index++

            this.items = array_move(this.items, this.selected_index, move_index)
        },
    },
    watch: {
        value: {
            immediate: true,
            handler(value) {
                this.items = [...value]
            }
        }
    }
}
</script>

<style scoped>
.dragging {
    background-color: red;
}
</style>