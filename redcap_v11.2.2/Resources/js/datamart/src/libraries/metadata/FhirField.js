class FhirField {

    #properties = {
        id: '',
        field: "id",
        temporal: false,
        label: "",
        description: "",
        category: "",
        subcategory: "",
        identifier: true,
        parent: null,
        hidden: false,
        disabled: false,
        disabled_reason: '',
    }

   _setters = ['field','temporal','label','description','category','subcategory','identifier','disabled', 'disabled_reason']

    constructor(params={}, context) {
        this.#properties.id = params.field
        for(let [key, value] of Object.entries(params)) {
            if(this._setters.indexOf(key)>=0) this[key] = value
        }
        this.context = context
    }

    get id() { return this.#properties.id }
    get field() { return this.#properties.field }
    get temporal() { return this.#properties.temporal }
    get label() { return this.#properties.label }
    get description() { return this.#properties.description }
    get category() { return this.#properties.category }
    get subcategory() { return this.#properties.subcategory }
    get identifier() { return this.#properties.identifier }
    get disabled() { return this.#properties.disabled }
    get disabled_reason() { return this.#properties.disabled_reason }
    get parent() { return this.#properties.parent }
    get hidden() {
        return this.#properties.hidden
        /* const {state} = this.context
        const hiddenList = state.hiddenList
        return hiddenList.indexOf(this.field)>=0 */
    }
    set hidden(value) {
        this.#properties.hidden = Boolean(value)
        /* const {commit, dispatch, getters, rootGetters, rootState, state} = this.context
        dispatch('updateHiddenList', {name: this.field, hidden: value}) */
    }
    get selected() {
        const {rootState} = this.context
        const selectedFields = rootState.revision.fields
        return selectedFields.indexOf(this.field)>=0
    }
    /* set selected(value) {
        const {commit, dispatch, getters, rootGetters, rootState, state} = this.context
        dispatch('revision/updateFields',{name: this.field, checked: value}, { root: true })
    } */

    set field(value) { this.#properties.field = new String(value ?? '').trim() }
    set temporal(value) { this.#properties.temporal = Boolean(value) }
    set label(value) { this.#properties.label = new String(value ?? '').trim() }
    set description(value) { this.#properties.description = new String(value ?? '').trim() }
    set category(value) { this.#properties.category = new String(value ?? '').trim() }
    set subcategory(value) { this.#properties.subcategory = new String(value ?? '').trim() }
    set identifier(value) { this.#properties.identifier = Boolean(value) }
    set parent(value) { this.#properties.parent = value }
    set disabled(value) { this.#properties.disabled = Boolean(value) }
    set disabled_reason(value) { this.#properties.disabled_reason = new String(value ?? '') }

}

export default FhirField