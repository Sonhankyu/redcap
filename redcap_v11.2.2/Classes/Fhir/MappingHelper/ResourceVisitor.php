<?php
namespace Vanderbilt\REDCap\Classes\Fhir\MappingHelper;

use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Bundle;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Patient;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Observation;
use Vanderbilt\REDCap\Classes\Fhir\Resources\DSTU2\AllergyIntolerance as AllergyIntolerance_DSTU2;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\AllergyIntolerance as AllergyIntolerance_R4;
use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\Encounter;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\MedicationRequest;
use Vanderbilt\REDCap\Classes\Fhir\Resources\ResourceVisitorInterface;

/**
 * FHIR resource visitor
 * 
 * adjust the data based on the type of resource visited
 */
class ResourceVisitor implements ResourceVisitorInterface
{

    /**
     * store modified data
     *
     * @var array
     */
    private $data = [];

    public function getData()
    {
      return $this->data;
    }
  
    public function addData($data)
    {
      $this->data[] = $data;
    }

    /**
     * manipulate the resource
     * return an array in each resource so that Bundle
     * can perform an array_merge. This is needed for resources
     * like "Observation" where we need to create a different entry for
     * each LOINC CODE
     * 
     * @param AbstractResource $resource
     * @return object
     */
    public function visit($resource)
    {
        $results = [];
        /**
         * NOTE: To use switch with get_class
         * I need to process the resources in specific
         * methods to avoid warnings from the IDE.
         * As an alternative I can add a comment before
         * using one of its methods.
         * E.g. :
         * // @var Bundle $resource
         * $entries = $resource->getEntries();
         * 
         * I can also use if statements with instanceof
         */
        $class = get_class($resource);
        switch ($class) {
            case Bundle::class:
                $results = $this->visitBundle($resource);
                break;
            case Observation::class:
                $results = $this->visitObservation($resource);
                break;
            case Encounter::class:
                $results = $this->visitEncounter($resource);
                break;
            case MedicationRequest::class:
                $results = $this->visitMedicationRequest($resource);
                break;
            case Patient::class:
            case AllergyIntolerance_R4::class:
            case AllergyIntolerance_DSTU2::class:
            default:
                $data = $resource->getData();
                $results = [$data];
                break;
        }
        return $results;
    }


    /**
     * get data for Bundles
     *
     * @param Bundle $resource
     * @return array
     */
    private function visitBundle($resource)
    {
        $entries = $resource->getEntries();
        $results = array_reduce($entries, function($accumulator, $entry) {
            $entry_data = $this->visit($entry);
            $accumulator = array_merge($accumulator, $entry_data);
            return $accumulator;
        }, []);
        return $results;
    }

    /**
     *
     * @param Observation $resource
     * @return array
     */
    private function visitObservation($resource)
    {
        $results = [];
        $observations = $resource->split();
        foreach ($observations as $observation) {
            $results[] = $observation->getData();
        }
        return $results;
    }

    /**
     *
     * @param MedicationRequest $resource
     * @return array
     */
    private function visitMedicationRequest($resource)
    {
        $results = [];
        $medications = $resource->split();
        foreach ($medications as $medication) {
            $results[] = $medication->getData();
        }
        return $results;
    }
    
    /**
     *
     * @param Encounter $resource
     * @return array
     */
    private function visitEncounter($resource)
    {
        $data = $resource->getData();
        return [$data];
    }
}