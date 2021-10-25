<?php
namespace ExternalModules;

use Exception;

class Project
{
    private $framework;
    private $project_id;
    private $redcap_project_object;
    private $forms;
    private $fields;

    function __construct($framework, $project_id){
        $this->framework = $framework;
        $this->project_id = $framework->requireInteger($project_id);
    }

    function getFramework(){
        return $this->framework;
    }

    /**
     * This method should only ever be used by framework classes, and should never be documented for use by modules.
     */
    function getREDCapProjectObject(){
        if(!isset($this->redcap_project_object)){
            $this->redcap_project_object = ExternalModules::getREDCapProjectObject($this->project_id);
        }

        return $this->redcap_project_object;
    }

    function getUsers(){
        $results = $this->framework->query("
			select username
			from redcap_user_rights
			where project_id = ?
			order by username
		", $this->project_id);

        $users = [];
        while($row = $results->fetch_assoc()){
            $users[] = new User($this->framework, $row['username']);
        }

        return $users;
    }

    function getProjectId() {
        return $this->project_id;
    }

    function getTitle(){
        return $this->getREDCapProjectObject()->project['app_title'];
    }

    function getRecordIdField(){
        $metadata = $this->getREDCapProjectObject()->metadata;
        return array_keys($metadata)[0];
    }

    function getEventId(){
        $eventId = @$_GET['event_id'];
        if($eventId){
            return $eventId;
        }

        $arms = $this->getREDCapProjectObject()->events;
        $armKeys = array_keys($arms);
        $arm = $arms[$armKeys[0]];
        $events = $arm['events']; 

        if(count($events) === 0){
            throw new Exception("No events found for project " . $this->getProjectId());
        }
        else if(count($events) > 1){
            throw new Exception("Multiple events found for project " . $this->getProjectId());
        }

        return array_keys($events)[0];
    }

    function addOrUpdateInstances($instances, $keyFieldNames){
        if(empty($instances)){
            return;
        }

        if(!is_array($keyFieldNames)){
            $keyFieldNames = [$keyFieldNames];
        }

        if(empty($keyFieldNames)){
            throw new Exception(ExternalModules::tt('em_errors_132'));
        }

        $instrumentName = null;
        foreach($keyFieldNames as $field){
            $instrumentNameForField = $this->getFormForField($field);
            if(empty($instrumentNameForField)){
                throw new Exception(ExternalModules::tt('em_errors_139', $field));
            }
            else if($instrumentName === null){
                $instrumentName = $instrumentNameForField;
            }
            else if($instrumentNameForField !== $instrumentName){
                throw new Exception(ExternalModules::tt('em_errors_133'));
            }
        }
        
        $recordIdFieldName = $this->getRecordIdField();
        array_unshift($keyFieldNames, $recordIdFieldName);

        $recordIds = array_unique(array_column($instances, $recordIdFieldName));

        $fields = $this->getForm($instrumentName)->getFieldNames();
        $existingInstances = json_decode(\REDCap::getData($this->getProjectId(), 'json', $recordIds, array_merge([$recordIdFieldName], $fields)), true);
        
        $getInstanceIndex = function($instance) use ($keyFieldNames){
            $instanceIndex = [];
            foreach($keyFieldNames as $field){
                $value = $instance[$field];
                if($value === null){
                    throw new Exception(ExternalModules::tt('em_errors_134', $field));
                }

                // Use string values to make sure comparisons work properly
                // regardless of whether string or integer values are used.
                $instanceIndex[] = (string) $value;
            }

            return json_encode($instanceIndex);
        };

        $existingInstancesIndexed = [];
        $remainingIndexes = [];
        $lastInstanceNumbers = [];
        foreach($existingInstances as $instance){
            if($instance['redcap_repeat_instrument'] === ''){
                // The is the non-repeating row for the record itself.  Skip it.
                continue;
            }

            $instanceIndex = $getInstanceIndex($instance);
            if(isset($existingInstancesIndexed[$instanceIndex])){
                throw new Exception(ExternalModules::tt('em_errors_135', $instrumentName) . json_encode($instance, JSON_PRETTY_PRINT));
            }

            $existingInstancesIndexed[$instanceIndex] = $instance;
            $recordId = $instance[$recordIdFieldName];
            $redcapRepeatInstance = $instance['redcap_repeat_instance'];
            $lastInstanceNumbers[$recordId] = max($redcapRepeatInstance, $lastInstanceNumbers[$recordId]);
            $remainingIndexes[$redcapRepeatInstance] = true;
        }

        $dataToSave = [];
        $uniqueIndexes = [];
        foreach($instances as $instance){
            if(!is_array($instance)){
                throw new Exception(ExternalModules::tt('em_errors_136'));
            }

            $instanceInstrumentName = $instance['redcap_repeat_instrument'];
            if(empty($instanceInstrumentName)){
                // Assume the correct instrument.
                $instance['redcap_repeat_instrument'] = $instrumentName;
            }
            elseif($instanceInstrumentName !== $instrumentName){
                throw new Exception(ExternalModules::tt('em_errors_137', $instrumentName, $instanceInstrumentName));
            }

            $instanceIndex = $getInstanceIndex($instance);
            if(isset($uniqueIndexes[$instanceIndex])){
                throw new Exception(ExternalModules::tt('em_errors_138') . json_encode($instance, JSON_PRETTY_PRINT));
            }
            else{
                $uniqueIndexes[$instanceIndex] = true;
            }

            $recordId = $instance[$recordIdFieldName];
            $existingInstance = @$existingInstancesIndexed[$instanceIndex];
            if($existingInstance === null){
                $instance['redcap_repeat_instance'] = ++$lastInstanceNumbers[$recordId];
            }
            else{
                $instance = array_merge($existingInstance, $instance);
                $remainingIndexes[$recordId][$instance['redcap_repeat_instance']];
            }
            
            $dataToSave[] = $instance;
        }
        
        $results = \REDCap::saveData(
            $this->getProjectId(),
            'json',
            json_encode($dataToSave),
            'overwrite'
        );

        // TODO - In the future maybe add a flag (or an additional replaceInstance() method) to remove old instances that no longer exist.
        // foreach($remainingIndexes as $recordId=>$instanceNumber){
        //     $instanceNumbers = array_keys($instanceNumbers);
        //     $this->removeInstances($recordId, $instrumentName, $instanceNumbers);
        // }

        return $results;
    }

    function getFormForField($fieldName){
        $result = $this->framework->query('select form_name from redcap_metadata where project_id = ? and field_name = ?', [$this->getProjectId(), $fieldName]);
        return $result->fetch_row()[0];
    }

    function addUser($username, $rights = []){
        $rights = array_merge($rights, [
            'username' => $username
        ]);

        \UserRights::addPrivileges($this->getProjectId(), $rights);
    }

    function setRights($username, $rights){
        $rights = array_merge($rights, [
            'username' => $username
        ]);
        
        \UserRights::updatePrivileges($this->getProjectId(), $rights);
    }

    function removeUser($username){
        \UserRights::removePrivileges($this->getProjectId(), $username);
    }

    function getRights($username){
        // Some users are stored with an uppercase first letter on REDCap Test.
        // The getRights() still expects them to be lowercase though.
        // Not sure if this is the best location for this fix...
        $username = strtolower($username);

        return $this->framework->getUser($username)->getRights($this->getProjectId());
    }

    function addRole($roleName, $rights = []){
        $originalPost = $_POST;
        
        $_POST = $rights;
        \UserRights::addRole($this->getREDCapProjectObject(), $roleName, ExternalModules::getUsername());

        $_POST = $originalPost;
    }

    function removeRole($roleName){
        \UserRights::removeRole($this->getProjectId(), $this->getRoleId($roleName), $roleName);
    }

    function setRoleForUser($roleName, $username){
        $this->framework->query('
            update redcap_user_rights
            set role_id = ?
            where project_id = ?
                and username = ?
        ', [
            $this->getRoleId($roleName),
            $this->getProjectId(),
            $username
        ]);
    }

    private function getRoleId($roleName){
        $result = $this->framework->query("
            select role_id
            from redcap_user_roles
            where project_id = ?
                and role_name = ?
        ", [$this->getProjectId(), $roleName]);

        $row = $result->fetch_assoc();
        if($result->fetch_assoc() !== null){
            throw new Exception("More than one row exists for project ID " . $this->getProjectId() . " and role '$roleName'!");
        }

        return $row['role_id'];
    }

    function getLogTable(){
        return $this->getREDCapProjectObject()->project['log_event_table'];
    }
    
    function deleteRecords(){
        /**
         * This method could fairly easily be implemented by:
         * 1. Creating a PR for REDCap core to extract the record deletion code in API\record\delete.php to a shared method (perhaps Records::deleteRecords())
         * 2. Calling that new method here.
         */

        throw new Exception('Not implemented');
    }
    
    function queryData($sql, $parameters){
        $query = new DataPseudoQuery($this);
        $sql = $query->getActualSQL($sql);
        // var_dump($sql, $parameters);
        return $this->framework->query($sql, $parameters);
    }

    function getForm($formName){
        return $this->cache($this->forms, $formName, Form::class);
    }

    function getField($fieldName){
        return $this->cache($this->fields, $fieldName, Field::class);
    }

    private function cache(&$cache, $key, $class){
        $instance = @$cache[$key];
        if($instance === null){
            $instance = new $class($this, $key);
            $cache[$key] = $instance;
        }

        return $instance;
    }

    function getRepeatingForms($eventId = null){
        return $this->getFramework()->getRepeatingForms($eventId, $this->getProjectId());
    }
}