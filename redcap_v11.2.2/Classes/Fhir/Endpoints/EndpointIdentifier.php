<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Endpoints;

/**
 * Lis of FHIR endpoint names.
 * This names are used along with the FHIR base URL
 * to fetch data from an EHR system.
 */
abstract class EndpointIdentifier
{
  const ADVERSE_EVENT = 'AdverseEvent';
  const ALLERGY_INTOLERANCE = 'AllergyIntolerance';
  const APPOINTMENT = 'Appointment';
  const BINARY_CCDA_DOCUMENTS = 'Binary'; //(CCDA Documents)
  const BINARY_CLINICAL_NOTES = 'Binary'; //(Clinical Notes)
  const BINARY_PRACTITIONER_PHOTO = 'Binary'; //(Practitioner Photo)
  const CARE_PLAN = 'CarePlan';
  const CARE_PLAN_ENCOUNTER_LEVEL_CARE_PLAN = 'CarePlan'; //(Encounter Level Care Plan)
  const CARE_PLAN_LONGITUDINAL_CARE_PLAN = 'CarePlan'; //(Longitudinal Care Plan)
  const CARE_TEAM = 'CareTeam';
  const CONDITION = 'Condition';
  const CONDITION_ENCOUNTER_DIAGNOSIS = 'Condition'; //(Encounter Diagnosis)
  const CONDITION_GENOMICS = 'Condition'; //(Genomics)
  const CONDITION_HEALTH_CONCERN = 'Condition'; //(Health Concern)
  const CONDITION_PROBLEMS = 'Condition'; //(Problems)
  const CONSENT = 'Consent';
  const COVERAGE = 'Coverage';
  const DEVICE = 'Device';
  const DIAGNOSTIC_REPORT = 'DiagnosticReport';
  const DOCUMENT_REFERENCE = 'DocumentReference';
  const DOCUMENT_REFERENCE_CLINICAL_NOTES = 'DocumentReference'; //(Clinical Notes)
  const ENCOUNTER = 'Encounter';
  const ENDPOINT = 'Endpoint';
  const EXPLANATION_OF_BENEFIT = 'ExplanationOfBenefit';
  const FAMILY_MEMBER_HISTORY = 'FamilyMemberHistory';
  const GOAL = 'Goal';
  const IMMUNIZATION = 'Immunization';
  const LIST = 'List';
  const LOCATION = 'Location';
  const MEDICATION = 'Medication';
  const MEDICATION_REQUEST_UNSIGNED_MEDICATION_ORDER = 'MedicationRequest'; //(Unsigned Medication Order)
  const MEDICATION_REQUEST = 'MedicationRequest';
  const MEDICATION_ORDER = 'MedicationOrder';
  const MEDICATIONSTATEMENT = 'MedicationStatement';
  const OBSERVATION_CORE_CHARACTERSITICS = 'Observation'; //(Core Charactersitics)
  const OBSERVATION_LABS = 'Observation'; //(Labs)
  const OBSERVATION_LDA_W = 'Observation'; //(LDA-W)
  const OBSERVATION_OBSTETRIC_DETAILS = 'Observation'; //(Obstetric Details)
  const OBSERVATION_SMOKING_HISTORY = 'Observation'; //(Smoking History)
  const OBSERVATION_VITALS = 'Observation'; //(Vitals)
  const ORGANIZATION = 'Organization';
  const PATIENT = 'Patient';
  const PRACTITIONER = 'Practitioner';
  const PRACTITIONERROLE = 'PractitionerRole';
  const PROCEDURE = 'Procedure';
  const RELATEDPERSON = 'RelatedPerson';
  const RESEARCHSTUDY = 'ResearchStudy';
  const SCHEDULE = 'Schedule';
  const SERVICE_REQUEST_UNSIGNED_PROCEDURE_ORDER = 'ServiceRequest'; //(Unsigned Procedure Order)
  const SERVICE_REQUEST = 'ServiceRequest';
  const PROCEDURE_REQUEST = 'ProcedureRequest';
  const SLOT = 'Slot';

}