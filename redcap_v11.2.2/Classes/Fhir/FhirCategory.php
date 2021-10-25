<?php
namespace Vanderbilt\REDCap\Classes\Fhir;

/**
 * define the list of FHIR categories available in REDCap.
 * These categories are used by the Endpoint Factories.
 */
abstract class FhirCategory
{
  /**
   * list of available FHIR categories in REDCap
   */
  const ALLERGY_INTOLERANCE = 'Allergy Intolerance';
  const ADVERSE_EVENT = 'Adverse Event';
  const DEMOGRAPHICS = 'Demographics';
  const CONDITION = 'Condition';
  const CORE_CHARACTERISTICS = 'Core Characteristics';
  const ENCOUNTER = 'Encounter';
  const IMMUNIZATION = 'Immunization';
  const MEDICATIONS = 'Medications';
  const LABORATORY = 'Laboratory';
  const VITAL_SIGNS = 'Vital Signs';
  const SMOKING_HISTORY = 'Smoking History';
  const RESEARCH_STUDY = 'Research Study';
}