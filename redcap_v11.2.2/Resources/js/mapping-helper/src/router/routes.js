import {fhir_category_descriptions} from '@/variables'
/**
 * components are lazy loaded as the routes are visited
 */
const routes = [
    { path: '', component: () => import('@/layouts/MainLayout'),
        children: [
            { path: 'scoped-slots', name: 'scoped-slots', component: () => import('@/components/scoped_slots_example/ScopedSlots') },
            { path: '', name: 'home', component: () => import('@/pages/Home'), meta: { title: 'Home' }},
            { path: 'help', name: 'help', component: () => import('@/pages/Help'), meta: { title: 'Help' } },
            { path: 'access-tokens', name: 'access-tokens', component: () => import('@/pages/AccessTokens'), meta: { title: 'Access Tokens' } },
            { path: 'endpoints', component: () => import('@/pages/Endpoints'),
                children: [
                    // DSTU2
                    { path: 'dstu2/test', name: 'test', components: { form: () => import('@/components/endpoints/forms/TestForm') } },

                    { path: 'dstu2/demographics', name: 'demographics-dstu2', components: {
                        form: () => import('@/components/endpoints/forms/DSTU2/DemographicsForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        }, meta: { title: 'Demographics', description: fhir_category_descriptions.PATIENT }
                    },
                    
                    { path: 'dstu2/observations', name: 'observations-dstu2', components: {
                        form: () => import('@/components/endpoints/forms/DSTU2/ObservationsForm'),
                        table: () => import('@/components/endpoints/tables/ObservationsTable.vue'),
                        dateRange: () => import('@/components/DateRange'),
                        }, meta: { title: 'Observations', description: fhir_category_descriptions.OBSERVATION }
                    },
                    
                    { path: 'dstu2/medications', name: 'medications-dstu2', components: {
                        form: () => import('@/components/endpoints/forms/DSTU2/MedicationsForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        }, meta: { title: 'Medications', description: fhir_category_descriptions.MEDICATION }
                    },
                    
                    { path: 'dstu2/conditions', name: 'conditions-dstu2', components: {
                        form: () => import('@/components/endpoints/forms/DSTU2/ConditionsForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        }, meta: { title: 'Conditions', description: fhir_category_descriptions.CONDITION }
                    },
                    
                    { path: 'dstu2/allergy-intolerance', name: 'allergy-intolerance-dstu2', components: {
                        form: () => import('@/components/endpoints/forms/DSTU2/AllergyIntoleranceForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        }, meta: { title: 'Allergy Intolerance', description: fhir_category_descriptions.ALLERGYINTOLERANCE }
                    },
                    
                    // R4
                    { path: 'r4/demographics', name: 'demographics-r4', components: {
                        form: () => import('@/components/endpoints/forms/R4/DemographicsForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        }, meta: { title: 'Demographics', description: fhir_category_descriptions.PATIENT }
                    },
                    
                    { path: 'r4/observations', name: 'observations-r4', components: {
                        form: () => import('@/components/endpoints/forms/R4/ObservationsForm'),
                        table: () => import('@/components/endpoints/tables/ObservationsTable'),
                        dateRange: () => import('@/components/DateRange'),
                        }, meta: { title: 'Observations', description: fhir_category_descriptions.OBSERVATION }
                    },
                    
                    { path: 'r4/medications', name: 'medications-r4', components: {
                        form: () => import('@/components/endpoints/forms/R4/MedicationsForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        }, meta: { title: 'Medications', description: fhir_category_descriptions.MEDICATION }
                    },
                    
                    { path: 'r4/conditions', name: 'conditions-r4', components: {
                        form: () => import('@/components/endpoints/forms/R4/ConditionsForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        }, meta: { title: 'Conditions', description: fhir_category_descriptions.CONDITION }
                    },
                    
                    { path: 'r4/allergy-intolerance', name: 'allergy-intolerance-r4', components: {
                        form: () => import('@/components/endpoints/forms/R4/AllergyIntoleranceForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        }, meta: { title: 'Allergy Intolerance', description: fhir_category_descriptions.ALLERGYINTOLERANCE }
                    },
                    
                    { path: 'r4/encounter', name: 'encounter-r4', components: {
                        form: () => import('@/components/endpoints/forms/R4/EncounterForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        dateRange: () => import('@/components/DateRange'),
                        }, meta: { title: 'Encounter', description: fhir_category_descriptions.ENCOUNTER }
                    },
                    
                    { path: 'r4/immunization', name: 'immunization-r4', components: {
                        form: () => import('@/components/endpoints/forms/R4/ImmunizationForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        dateRange: () => import('@/components/DateRange'),
                        }, meta: { title: 'Immunization', description: fhir_category_descriptions.IMMUNIZATION }
                    },
                    
                    { path: 'r4/adverse-event', name: 'adverse-event-r4', components: {
                        form: () => import('@/components/endpoints/forms/R4/AdverseEventForm'),
                        table: () => import('@/components/endpoints/tables/ResourceTable.vue'),
                        }, meta: { title: 'Adverse Event', description: fhir_category_descriptions.ADVERSEEVENT }
                    },
                    
                ]
            },
            { path: 'endpoints/custom-request', name: 'custom-request', component: () => import('@/pages/CustomEndpoint'), meta: { title: 'Custom Request' , description: 'Test any endpoint'} },
            { path: "*", name: 'not_found', component: () => import('@/pages/PageNotFound'), meta: { title: 'Page not found' } },
        ]
    },

]

export default routes