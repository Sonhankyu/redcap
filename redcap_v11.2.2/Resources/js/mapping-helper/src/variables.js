export const medication_status_list = [
    'active',
    'completed',
    'on-hold',
    'stopped',
]

export const fhir_categories = Object.freeze({
    ADVERSE_EVENT: 'Adverse Event',
    ALLERGY_INTOLERANCE: 'Allergy Intolerance',
    DEMOGRAPHICS: 'Demographics',
    CONDITION: 'Condition',
    CORE_CHARACTERISTICS: 'Core Characteristics',
    ENCOUNTER: 'Encounter',
    IMMUNIZATION: 'Immunization',
    MEDICATIONS: 'Medications',
    LABORATORY: 'Laboratory',
    VITAL_SIGNS: 'Vital Signs',
    SMOKING_HISTORY: 'Smoking History',
})
export const fhir_category_descriptions = Object.freeze({
    ADVERSEEVENT: 'Actual or potential/avoided event causing unintended physical injury resulting from or contributed to by medical care, a research study or other healthcare setting factors that requires additional monitoring, treatment, or hospitalization, or that results in death.',
    ALLERGYINTOLERANCE: 'Risk of harmful or undesirable, physiological response which is unique to an individual and associated with exposure to a substance.',
    PATIENT: 'Demographics and other administrative information about an individual or animal receiving care or other health-related services.',
    CONDITION: 'A clinical condition, problem, diagnosis, or other event, situation, issue, or clinical concept that has risen to a level of concern.',
    OBSERVATION: 'Measurements and simple assertions made about a patient, device or other subject.',
    ENCOUNTER: 'An interaction between a patient and healthcare provider(s) for the purpose of providing healthcare service(s) or assessing the health status of a patient.',
    IMMUNIZATION: 'Describes the event of a patient being administered a vaccine or a record of an immunization as reported by a patient, a clinician or another party.',
    MEDICATION: 'This resource is primarily used for the identification and definition of a medication for the purposes of prescribing, dispensing, and administering a medication as well as for making statements about medication use.',
})



export const interactions = Object.freeze({
    READ: 'read',
    UPDATE: 'update',
    DELETE: 'delete',
    CREATE: 'create',
    SEARCH: 'search',
    HISTORY: 'history',
    TRANSACTION: 'transaction',
    OPERATION: 'operation',
})

export const menu_links = {

    home: { label:'Home', route_name: 'home', exact:true, tags: ['home'] },
    custom: { label:'Custom request', route_name: 'custom-request', tags: ['custom'] },
}

export const fhir_menu_links = [

    { label:'Demographics', route_name: 'demographics-dstu2', tags: ['DSTU2'] },
    { label:'Medications', route_name: 'medications-dstu2', tags: ['DSTU2'] },
    { label:'Labs and Vitals', route_name: 'observations-dstu2', tags: ['DSTU2'] },
    { label:'Problem list', route_name: 'conditions-dstu2', tags: ['DSTU2'] },
    { label:'Allergy Intolerance', route_name: 'allergy-intolerance-dstu2', tags: ['DSTU2'] },

    { label:'Demographics', route_name: 'demographics-r4', tags: ['R4'] },
    { label:'Medications', route_name: 'medications-r4', tags: ['R4'] },
    { label:'Labs and Vitals', route_name: 'observations-r4', tags: ['R4'] },
    { label:'Problem list', route_name: 'conditions-r4', tags: ['R4'] },
    { label:'Allergy Intolerance', route_name: 'allergy-intolerance-r4', tags: ['R4'] },
    { label:'Encounter', route_name: 'encounter-r4', tags: ['R4'] },
    { label:'Immunization', route_name: 'immunization-r4', tags: ['R4'] },
    { label:'Adverse Event', route_name: 'adverse-event-r4', tags: ['R4'] },

]

export const date_format = 'YYYY-MM-DD HH:mm'

export const route = 'FhirMappingHelperController'