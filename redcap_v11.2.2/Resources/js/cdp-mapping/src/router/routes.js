/**
 * components are lazy loaded as the routes are visited
 */

const routes = [
    { path: '/', component: () => import('@/layouts/MainLayout'),
        children: [
            { path: '', name: 'home', component: () => import('@/pages/Home') },
            // { path: 'fhir_fields', name: 'fhir_fields', component: () => import('@/pages/FhirFields') },
            { path: 'test', name: 'test', component: () => import('@/pages/Test') },
            { path: "*", name: 'not_found', component: () => import('@/pages/PageNotFound')  }
        ]
    }
]

export default routes