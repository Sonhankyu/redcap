<?php
namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull;

use SplObserver;
use SplSubject;
use Vanderbilt\REDCap\Classes\Fhir\FhirClient;

class RecordAdapter implements SplObserver
{

    private $data = [];
    private $errors = [];

    /**
	 * react to notifications (from the FHIR client)
	 *
	 * @param SplSubject $subject
	 * @param string $event
	 * @param mixed $data
	 * @return void
	 */
	public function update($subject, string $event = null, $data = null)
	{
		switch ($event) {
			case FhirClient::NOTIFICATION_ENTRIES_RECEIVED:
				$category = @$data['category'];
				$entries = @$data['entries'];
				$mapping = @$data['mapping'];
				$this->addData($category, $entries, $mapping);
				break;
            case FhirClient::NOTIFICATION_ERROR:
                $this->addError($data);
                break;
			default:
				break;
		}
	}

    /**
	 * apply the resource visitor to the received data
	 * and store it in its group
	 *
	 * @param string $category
	 * @param array $entries
	 * @param array $mapping [[field, timestamp_min, timestamp_max]]
	 * @return void
	 */
	public function addData($category, $entries, $mapping)
	{
		/**
		 * extract data from each resource
		 * and make necessary transformations if needed
		 */
		$mapEntries = function($entries, $mapping) {
			$resourceVisitor = new ResourceVisitor($mapping['fields'], $mapping['dateMin'], $mapping['dateMax']);
			foreach ($entries as $entry) {
				$entry->accept($resourceVisitor);
			}
            $data = $resourceVisitor->getData();
			return $data;
		};

		$mappedEntries = $mapEntries($entries, $mapping);
        // spread the mapped
        foreach ($mappedEntries as $mappedData) {
            $this->data[] = $mappedData;
        }
	}

    /**
     * check if there is any error
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return count($this->errors)>0;
    }

    /**
     * add an error
     *
     * @param \Exception $exception
     * @return void
     */
    public function addError($exception)
    {
        $this->errors[] = $exception;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getData()
    {
        $data =  $this->data;
        /* foreach ($data as $category => $entry) {
            # code...
        } */
        return $data;
    }
}
