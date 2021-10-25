<?php
namespace ExternalModules;

class Form extends ProjectChild
{
    function getFieldNames(){
        return array_keys(\REDCap::getDataDictionary($this->getProject()->getProjectId(), 'array', false, null, $this->getName()));
    }
}