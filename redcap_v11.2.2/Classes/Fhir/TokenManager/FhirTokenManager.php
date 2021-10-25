<?php

namespace Vanderbilt\REDCap\Classes\Fhir\TokenManager;

use Exception;
use Logging;
use SplObserver;
use User;
use UserRights;
use Vanderbilt\REDCap\Classes\Fhir\FhirClient;
use Vanderbilt\REDCap\Classes\Fhir\FhirEhr;
use Vanderbilt\REDCap\Classes\Fhir\FhirServices;

class FhirTokenManager implements SplObserver
{
    /**
     * user ID to use when storing and retrieving tokens from the database
     *
     * @var int
     */
    private $user_id; // must be defined on instance creation

    /**
     * patient ID used to retrieve specific access tokens
     * associated to a patient
     *
     * @var string
     */
    private $patient_id;

    /**
     * maximum number of valid token to retrieve from database
     *
     * @var int
     */
    private $token_limit; // the maximum number of token to try when fetching data


    /**
     * the index of the active token
     *
     * @var FhirToken
     */
    private $activeToken;

    
    /**
     * list of available tokens
     * if a token is not valid, the next one in this list
     * will be used
     * 
     *
     * @var FhirToken[]
     */
    private $tokens = array();

    function __construct($user_id=null, $patient_id=null, $token_limit=10)
    {
        $this->user_id = $user_id;
        $this->patient_id = $patient_id;
        $this->token_limit = $token_limit;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * set the patient id so that can be used to retrieve specific access tokens
     *
     * @param string $patient_id
     * @return void
     */
    public function setPatientId($patient_id)
    {
        $this->patient_id = $patient_id;
    }

    /**
     * get FhirServices
     *
     * @return FhirServices
     */
    public static function getFhirServices($endpoint=null)
    {
        global $fhir_endpoint_base_url, $fhir_client_id, $fhir_client_secret;
        $endpoint = $endpoint ?: $fhir_endpoint_base_url;
        return new FhirServices($endpoint, $fhir_client_id, $fhir_client_secret);
    }

    /**
     * get a token using the client credentials flow
     *
     * @return FHIRToken|false a valid access token or false if not 
     */
    private function getTokenUsingClientCredentials()
    {
        try {
            $fhirServices = self::getFhirServices();
            $scopes = FhirServices::$client_credentials_scopes;
            $new_token = $fhirServices->getTokenWithClientCredentials($scopes);
            $token = self::storeToken($new_token, $this->user_id);
            return $token;
        } catch (\Exception $e) {
            $exception_code = $e->getCode();
            $exception_message = $e->getMessage();
            $exception_data = array();
            if($e instanceof \DataException) $exception_data = $e->getData();
            \Logging::logEvent( "", "FHIR", "MANAGE", json_encode($exception_data, JSON_PRETTY_PRINT), $exception_code, $exception_message );
            return false;
        }
    }

    /**
     * get a valid access token for a user
     * refresh the token if expired
     * if the refresh fails, try the next token
     *
     * @return FHIRToken|false a valid access token or false if not 
     */
    public function getToken()
    {
        global $fhir_standalone_authentication_flow;
        $token = $this->getActiveToken();
        if($token===false)
        {
            if($fhir_standalone_authentication_flow==FhirEhr::AUTHENTICATION_FLOW_CLIENT_CREDENTIALS)
            {
                // try to get a token using client credentials flow
                return $this->getTokenUsingClientCredentials();
            }else
            {
                return false; // stop if no tokens available
            }
        }
        

        if($token->isExpired())
        {
            // refresh if expired
            $fhirServices = self::getFhirServices();
            $token->refresh($fhirServices);
        }

        // check if token is valid
        if($token->isValid()) return $token;

        // if the token has not been refreshed try the next token
        $this->getNextActiveToken();
        return $this->getToken();
    }

    /**
     * get an access token
     * 
     * @throws Exception if access token is not available
     * @return string
     */
    public function getAccessToken()
    {
        global $lang;
        $token = $this->getToken();
        if(!is_a($token, FhirToken::class) || !($access_token = $token->getAccessToken()) )
		{
			throw new \Exception($message = $lang['data_entry_398'], 401); // 401 = unauthorized client
        }
        return $access_token;
    }

    /**
     * set and return the active token from the list of available tokens
     *
     * @return FhirToken
     */
    private function getActiveToken()
    {
        // the active token is not set: reset the pointer and get the first element
        $tokens = $this->getTokens($this->patient_id);
        if(!isset($this->activeToken)) reset($tokens);
        $this->activeToken = current($tokens);
        return $this->activeToken;
    }

    /**
     * move the pointer of the active tokens to the next element
     *
     * @return FhirToken
     */
    public function getNextActiveToken()
    {
        $tokens = $this->getTokens($this->patient_id);
        $this->activeToken = next($tokens);
        return $this->activeToken;
    }

    /**
     * no token available
     *
     * @throws Exception
     */
    private function throwNoTokensAvailableException()
    {
        throw new \Exception("Error: no tokens available.", 1);
    }

    /**
     * get all valid tokens for a specific user
     * an token is valid if
     *  - access_token is not expired
     *      OR
     *  - refresh_token is not older than 30 days
     * 
     * if a user is specified then only his tokens are selected
     * (usually we have no user when a cron job is running)
     * 
     * specific tokens are prioritized.
     * the priority order is:
     *  - patient
     *  - expiration
     *
     * @param integer $user_id
     * @param string $patient
     * @return FHIRToken[]
     */
    public function getTokens($patient=null)
    {
        $list = array();
        // get 
        $query_string = 'SELECT * FROM redcap_ehr_access_tokens';
        $query_string .= sprintf(' WHERE 
                        (
                            (access_token IS NOT NULL AND expiration > "%1$s")
                            OR
                            (refresh_token IS NOT NULL AND expiration > DATE_SUB("%1$s", INTERVAL 30 DAY))
                        )', NOW);
        // set constraint if user is sepcified
        if(isset($this->user_id)) $query_string .= sprintf(" AND token_owner = '%u'", $this->user_id);

        $order_by_clauses = array();               
        // if(isset($user_id)) $order_by_clauses[] = sprintf("FIELD (token_owner, %u) DESC", $user_id);
        if(isset($patient)) $order_by_clauses[] = sprintf("FIELD (patient, '%s') DESC", $patient);
        $order_by_clauses[] = 'expiration DESC';

        $order_by_string = " ORDER BY ".implode(', ', $order_by_clauses);

        $query_string .= $order_by_string;
        $query_string .= sprintf(" LIMIT %u", $this->token_limit);
        // query the DB
        $result = db_query($query_string);
        while($tokenInfo = db_fetch_object($result))
        {
            $list[] = new FHIRToken($tokenInfo);
        }
        // if there are no tokens throw an exception
        // if(empty($list)) $this->throwNoTokensAvailableException();

        return $list;
    }

    /**
     * persist a token to the database
     *
     * @param object|array $token_data
     * @param integer $user_id
     * @return FHIRToken
     */
    public static function storeToken($token_data, $user_id=null)
    {
        $token = new FHIRToken($token_data);
        if($user_id) $token->setOwner($user_id);
        $token->save();
        return $token;
    }

    // If there is an institution-specific MRN, then store in access token table to pair it with the patient id
    /**
     * Undocumented function
     *
     * @param string $patient
     * @param string $mrn
     * @return void
     */
	public function storePatientMrn($patient, $mrn)
	{
	    if (empty($mrn)) return false;
		$query_string = sprintf("UPDATE redcap_ehr_access_tokens SET mrn = %s
				        WHERE patient='%s'", checkNull($mrn), db_escape($patient));
		return db_query($query_string);
    }
    
    /**
     * cleanup MRN entries for a user
     * 
     * the table could contain orphaned MRNs 
     * if the FHIR ID changes for any reason (i.e. EHR updates)
     *
     * @param integer $user_id token owner
     * @param string $mrn
     * @return boolean
     */
    public function removeMrnDuplicates($mrn)
    {
        if(!$user_id = $this->user_id) return;
        $query_string = sprintf(
            "DELETE FROM redcap_ehr_access_tokens 
            WHERE mrn=%s AND token_owner=%u",
            checkNull($mrn),$user_id);
        return db_query($query_string);
    }

    /**
     * remove all entries of a FHIR id (patient)
     * used when we get a 404 error using the FHIR ID in a patient.read call
     *
     * @param string $patient_id
     * @return void
     */
    public function removeCachedPatient($patient_id)
    {
        if(!$user_id = $this->user_id) return;
        $query_string = sprintf(
            "DELETE FROM redcap_ehr_access_tokens 
            WHERE patient=%s",
            checkNull($patient_id),$user_id);
        return db_query($query_string);
    }

    /**
     * get a list of users with a valid access token (active or refreshable)
     * for a specific project
     *
     * @param int $project_id
     * @return array
     */
    public static function getUsersWithValidTokenInProject($project_id)
    {
        
        $projectsPrivileges = UserRights::getPrivileges($project_id);
        $usersInfo = @$projectsPrivileges[$project_id] ?: [];
        $usernames = array_keys($usersInfo);
        $user_ids = array_map(function($username) {
            return User::getUIIDByUsername($username);
        }, $usernames);

        $quotedUsers = sprintf("'%s'", implode("','", $user_ids ));
        $now = date('Y-m-d H:i:s');
        $query_string = sprintf(
            'SELECT `token_owner` FROM redcap_ehr_access_tokens
            WHERE 1
            AND (
                ( access_token IS NOT NULL AND expiration < %1$s )
                OR
                ( refresh_token IS NOT NULL AND expiration > DATE_SUB(%1$s, INTERVAL 30 DAY) )
            )
            AND `token_owner` IN (%2$s)
            ORDER BY `expiration` DESC', checkNull($now), $quotedUsers
        );
        $result = db_query($query_string);
        $userIds = [];
        while($row = db_fetch_assoc($result)) {
            $userIds[] = @$row['token_owner'];
        }
        return $userIds;
    }

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
            case FhirClient::NOTIFICATION_PATIENT_IDENTIFIED:
                $mrn = @$data['mrn'];
                $fhir_id = @$data['fhir_id'];
                $this->onPatientIdentified($mrn, $fhir_id);
                break;
            case FhirClient::NOTIFICATION_REQUEST_SENT:
                if(!($subject instanceof FhirClient)) break;
                break;
            case FhirClient::NOTIFICATION_REQUEST_ERROR:
                if(!($subject instanceof FhirClient)) break;
                $this->onFhirClientError($data);
                break;
			default:
				break;
		}
	}

    /**
     * cache the FHIR ID when a patient is identified
     *
     * @param string $mrn
     * @param string $fhir_id
     * @return void
     */
    private function onPatientIdentified($mrn, $fhir_id)
    {
        $query_string = sprintf('INSERT INTO `redcap_ehr_access_tokens` (`mrn`, `patient`) VALUES(%s, %s)', checkNull($mrn), checkNull($fhir_id));
        $result = db_query($query_string);
        if($result) Logging::logEvent($query_string, 'redcap_ehr_access_token', 'FHIR', '', json_encode(compact('fhir_id', 'mrn'), JSON_PRETTY_PRINT), 'Patient FHIR ID has been cached', 'Patient identified');
    }

    /**
     * perform actions when errors are detected
     * (e.g. delete access token if access is forbidden)
     * @param array $data
     * @return void
     */
    private function onFhirClientError($data)
    {
        $error = @$data['error'];
        if(!$error instanceof Exception) return;
        $code = $error->getCode();
        switch ($code) {
            // delete access token if access is forbidden
            case '401':
                $access_token = @$data['access_token'];
                // $mrn = @$data['mrn'];
                if($access_token) $this->deleteAccessToken($access_token);
                break;
            default:
                # code...
                break;
        }

    }

    private function deleteAccessToken($access_token) {
        $query_string = sprintf('DELETE FROM `redcap_ehr_access_tokens` WHERE `access_token`=%s', checkNull($access_token));
        $result = db_query($query_string);
        if($result) Logging::logEvent($query_string, 'redcap_ehr_access_token', 'FHIR', $access_token, '','Access token has been deleted', 'Permission denied');
    }

}

