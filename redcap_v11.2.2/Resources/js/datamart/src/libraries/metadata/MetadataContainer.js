import Fhirield from './FhirField'

/* const makeProxy = (field, context) => {
    const handler = {
        get(target, property, receiver) {
            return Reflect.get(...arguments)
        },
        set(obj, prop, value) {
            const list = [...context.state.list]
            let index = list.indexOf(obj)
            console.log(index)
            let params = {}
            return Reflect.set(...arguments)
        }
    }
    
    const proxy = new Proxy(field, handler)
    return proxy
} */

let MetadataContainer = class {
    _parent = null
    _id = ''
    _children = []
    _hiddenCount = 0
    _fields = []
    _containers = []


    constructor(identifier, parent=null) {
        this._id = identifier
        this.parent = parent
    }

    get id() { return this._id }
    
    get parent() { return this._parent }
    set parent(container) { this._parent = container }

    get children() { return [...this._children] }
    
    /* get metadata() {
        const { visible, total, selected, visibleSelected} = this.count()
        let hidden = total-visible
        let metadata = { hidden, total, visible, selected, visibleSelected }
        return metadata
    } */

    getFields(deep=false) {
        let fields = this._fields
        if(!deep) return fields
        for (let child of this._containers) {
            fields = fields.concat(child.getFields(deep))
        }
        return fields
    }


    hasChild(identifier, deep=false) {
        for (let child of this._children) {
            if(child.id===identifier) return child
            if(deep && child instanceof MetadataContainer) {
                let found = child.hasChild(identifier)
                if(found) return found
            }
        }
        return false
    }

    /**
     * add a child and collect them per type
     * for faster access
     * NOTE: instanceof is more releable of
     * `child.constructor.name == FhirField.name`because
     * the class names are lost after transpiling
     *  
     * @param {Fhirield|MetadataContainer} child 
     */
    addChild(child) {
        child.parent = this
        if(child instanceof Fhirield) this._fields.push(child)
        if(child instanceof MetadataContainer) this._containers.push(child)
        this._children.push(child)
    }

    /* count() {
        let counts = {
            visible: 0,
            total: 0,
            selected: 0,
            visibleSelected: 0,
        }
        for (const child of this._children) {
            if(child.constructor.name == MetadataContainer.name) {
                const {visible,total,selected,visibleSelected} = child.count()
                counts.visible += visible
                counts.total += total
                counts.selected += selected
                counts.visibleSelected += visibleSelected
            }else if(child.constructor.name == Fhirield.name) {
                counts.total++
                if(!child.hidden) counts.visible++
                if (child.selected==true) counts.selected++
                if (!child.hidden && child.selected==true) counts.visibleSelected++
            }
        }
        return counts
    } */
}
export default MetadataContainer