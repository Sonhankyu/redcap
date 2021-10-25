<?php

use Vanderbilt\REDCap\Classes\Fhir\DataMart\DataMart;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\DataMartBackgroundRunner;
use Vanderbilt\REDCap\Classes\Queue\Worker;

/**
 * JOBS
 * This class will be instantiated by the Cron class.
 * All functions listed in this class correspond to a specific job to be run.
 */
class Jobs
{

	/**
	 * create a worker to process the messages in the queue
	 *
	 * @return void
	 */
	public function ProcessQueue()
	{
		try {
			$max_processing = 5; // overall maximum number of workers allowed
			$max_processing_per_type = 5; // maximum number of workers allowed for a specific task
			$worker = new Worker($max_processing, $max_processing_per_type);
			if($worker->hasMessages()) {
				$worker->process();
				$GLOBALS['redcapCronJobReturnMsg'] = "The queue has been processed.";
			}
		} catch (\Exception $e) {
			$GLOBALS['redcapCronJobReturnMsg'] = "error processing the queue. ".$e->getMessage();
		}
	}
    /**
     * Fetches EHR data for all Clinical Data Mart projects
	 * fetch data only if one of these conditions are true:
	 * - no revision date_max is specified
	 * - no overall (patients and revision) date_max is specified in a revision
	 * - overall date is specified, but is in the future (> NOW)
     */
    public function ClinicalDataMartDataFetch()
    {
		// Fetch the data for all Data Mart projects
		$dataMart = new DataMart(0); // do not provide any specific user ID
		$revisions = $dataMart->getCronEnabledRevisions();
		$scheduled_projects = 0;
		
		$bgRunner = new DataMartBackgroundRunner($dataMart);
		foreach ($revisions as $revision) {
			try {
				$bgRunner->schedule($revision->id, $mrn_list=[], $sendFeedback=false);
				$scheduled_projects++;
			} catch (\Exception $e) {
				\Logging::logEvent( $sql='', 'FHIR', "ERROR", $revision->id, "Error scheduling a DataMart revision.", $e->getMessage());
			}
		}

		if ($scheduled_projects > 0) {
				$GLOBALS['redcapCronJobReturnMsg'] = "The data retrieval process has been scheduled across $scheduled_projects Clinical Data Mart projects";
		}
	}

	/**
	 * Check if there is a newer REDCap version available
	 */
	public function CheckREDCapVersionUpdates()
	{
		$versions = Upgrade::fetchREDCapVersionUpdatesList();
		if (is_array($versions) && !empty($versions)) {
			$GLOBALS['redcapCronJobReturnMsg'] = (count($versions['lts'])+count($versions['std'])) . " REDCap versions are available for upgrading to: " . implode(", ", array_merge($versions['lts'], $versions['std']));
		}
	}
	
	/**
	 * Check if any installed External Modules have updates available on the REDCap Repo
	 */
	public function CheckREDCapRepoUpdates()
	{
	    global $allow_outbound_http;
		// Ensure that External Modules feature is installed
		if (!defined("APP_PATH_EXTMOD") || !$allow_outbound_http) return;
		// Obtain array of all downloaded modules
		$modules = \ExternalModules\ExternalModules::getModulesInModuleDirectories();
		// Obtain array of all bundled modules
		$bundled = array();
		if (method_exists('\ExternalModules\ExternalModules', 'getBundledModulePrefixes')) {
			$bundled = \ExternalModules\ExternalModules::getBundledModulePrefixes();
		}
		// Make POST request
		$postParams = array('bundled_modules'=>$bundled, 'downloaded_modules'=>$modules, 'redcap_version'=>REDCAP_VERSION, 'php_version'=>PHP_VERSION);
		$modulesToUpdateJson = http_post(APP_URL_EXTMOD_LIB . "download.php?updates=1", $postParams);
		if ($modulesToUpdateJson === false) return;
		$modulesToUpdate = json_decode($modulesToUpdateJson, true);
		if (!is_array($modulesToUpdate) || empty($modulesToUpdate)) return;
		// Add cron job output
		$modulesToUpdateNames = array();
		foreach ($modulesToUpdate as $thisModule) {
			$modulesToUpdateNames[] = $thisModule['name'];
		}
		$GLOBALS['redcapCronJobReturnMsg'] = count($modulesToUpdate) . " External Modules from the REDCap Repo have upgrades available: " . implode(", ", $modulesToUpdateNames);
		// Store the JSON string in config to display later in Control Center
		updateConfig('external_modules_updates_available', $modulesToUpdateJson);
		updateConfig('external_modules_updates_available_last_check', NOW);
	}

	/**
	 * PERFORM VARIOUS VALIDATION CHECKS ON EXTERNAL MODULES THAT ARE INSTALLED
	 */
	public function ExternalModuleValidation()
	{
		// Ensure that External Modules feature is installed
		if (!defined("APP_PATH_EXTMOD")) return;
		// Make sure the method exists that we need
		if (!method_exists('\ExternalModules\ExternalModules', 'validateAllModuleCronJobs')) return;
		// Validate all modules that have cron jobs
		$modulesFixed = \ExternalModules\ExternalModules::validateAllModuleCronJobs();
		if (!empty($modulesFixed)) {
			$GLOBALS['redcapCronJobReturnMsg'] = count($modulesFixed) . " cron jobs had their attributes fixed for the following External Modules: " . implode(", ", $modulesFixed);
		}
	}

	/**
	 * GENERATE THE STATS REPORTING URL AND STORE IT IN THE CONFIG TABLE
	 */
	public function CacheStatsReportingUrl()
	{
		$Stats = new Stats();
		if ($Stats->cacheStatsReportingUrl()) {
			$GLOBALS['redcapCronJobReturnMsg'] = "The stats reporting URL was cached in redcap_config";
		}
	}
	

	/**
	 * REMOVE ANY OUTDATED ROWS FROM THE RECORD COUNTS TABLE
	 * Delete any rows older than X days for projects that have had any activity in the past Y days.
	 * Doing this frequent refresh prevents any chance of the counts getting out of sync with reality.
	 */
	public function RemoveOutdatedRecordCounts()
	{
		// First, remove any that have been processing for more than 1 hour (this should not happen)
		$oneHourAgoEvent = date("Y-m-d H:i:s", mktime(date("H")-1,date("i"),date("s"),date("m"),date("d"),date("Y")));
		$sql = "delete from redcap_record_counts where time_of_list_cache is not null and time_of_list_cache < '$oneHourAgoEvent' and record_list_status = 'PROCESSING'";
		db_query($sql);
		// Remove old record counts for projects that have had some logged activity in the past week
		// if the time of last record count or time of last cache has been more than 5 days.
		$daysOldCounted = 5;
		$daysOldEvent = 7;
		$xDaysAgoCounted = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")-$daysOldCounted,date("Y")));
		$xDaysAgoEvent = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")-$daysOldEvent,date("Y")));
		$sql = "select c.project_id from redcap_record_counts c, redcap_projects p
				where c.project_id = p.project_id and p.last_logged_event is not null and p.last_logged_event > '$xDaysAgoEvent'
				and (c.time_of_count < '$xDaysAgoCounted' or (c.time_of_list_cache is not null and c.time_of_list_cache < '$xDaysAgoCounted'))";
		$q = db_query($sql);
		$pidsDelete = array();
		while ($row = db_fetch_assoc($q)) {
			$pidsDelete[] = $row['project_id'];
		}
		if (!empty($pidsDelete)) {
			// Delete the rows from the cache table
			$rowsDeleted = Records::resetRecordCountAndListCache($pidsDelete);
			// Set cron job message
			if ($rowsDeleted > 0) {
				$GLOBALS['redcapCronJobReturnMsg'] = "$rowsDeleted rows deleted from redcap_record_counts";
			}
		}
	}


	/**
	 * FIX INVITATIONS STUCK IN 'SENDING' STATUS
	 * Set them back to 'QUEUED' status if been sending for more than X hours.
	 */
	public function FixStuckSurveyInvitations()
	{
		// Fix any invitations stuck for more than 2 hours but are not more than 7 days old
		$twoHoursAgo = date("Y-m-d H:i:s", mktime(date("H")-2,date("i"),date("s"),date("m"),date("d"),date("Y")));
		$sevenDaysAgo = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")-7,date("Y")));
		$sql = "update redcap_surveys_scheduler_queue set status = 'QUEUED' where status = 'SENDING'
				and scheduled_time_to_send < '$twoHoursAgo'and scheduled_time_to_send > '$sevenDaysAgo'";
		db_query($sql);
		$rowsAffected = db_affected_rows();
		// Set cron job message
		if ($rowsAffected > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$rowsAffected survey invitations stuck in 'SENDING' status set back to 'QUEUED' status";
		}
	}


	/**
	 * DB USAGE
	 * Record the daily space usage of the database tables and the uploaded files stored on the server.
	 */
	public function DbUsage()
	{
		$sql = "replace into redcap_history_size (`date`, size_db, size_files)
				values ('".TODAY."', '".db_escape(round(getDbSpaceUsage()/1024/1024,1))."',
				'".db_escape(round(Files::getEdocSpaceUsage()/1024/1024,1))."')";
		db_query($sql);
		// Set cron job message
		if (db_affected_rows() > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "1 row added to redcap_history_size";
		}
	}


	/**
	 * DB HEALTH CHECK
	 * 1) Kill any MySQL queries running longer than X minutes.
	 * 2) Check % of MySQL connections used. If more than 2/3 are being used, then send email to admin immediately.
	 */
	public function DbHealthCheck()
	{
		$GLOBALS['redcapCronJobReturnMsg'] = "";

		## 1) Kill any MySQL queries running longer than X minutes
		$max_query_time = 30; // Max query time (minutes)
		$max_script_time = 60; // No REDCap script should run longer than this (minutes)
		$killedQueries = [];
		$sql = "SHOW FULL PROCESSLIST";
		$result = db_query($sql);
		$numConnections = db_num_rows($result);
		while ($row = db_fetch_assoc($result))
		{
			// Ignore ourself running the process list AND if the query text is empty AND if query time < 60 minutes
			if (($row['Info'] == $sql || trim($row['Info']) == '') && $row['Time'] < $max_script_time*60) continue;
			// Ignore queries running less than our limit time
			if ($row['Time'] < $max_query_time*60) continue;
			// Kill has been running too long, so kill it
			if (db_query("KILL ".$row['Id'])) {
				$killedQueries[] = $row['Info'];
			}
		}
		$kills = count($killedQueries);
		if ($kills > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] .= "Killed $kills slow queries in ".db_get_server_type()." that were running longer than $max_query_time minutes\n\n";
			// Send email (Vanderbilt only)
			if (isVanderbilt()) {
				$msg = "Killed $kills slow queries in " . db_get_server_type() . " that were running longer than $max_query_time minutes:<br><pre>"
					 . implode("</pre><pre>", $killedQueries) . "</pre>";
				REDCap::email("rob.taylor@vumc.org", $GLOBALS['project_contact_email'], "[REDCap] $kills slow queries were killed", $msg);
			}
		}

		## 2) Check % of connections used. If more than 2/3 are being used, then send email to admin immediately
		$connectionLimit = 2/3;
		$sql = "show variables like 'max_connections'";
		$result = db_query($sql);
		$row = db_fetch_assoc($result);
		$maxConnections = $row['Value'];
		if ($numConnections > floor($maxConnections*$connectionLimit)) {
			$GLOBALS['redcapCronJobReturnMsg'] .= "Too many ".db_get_server_type()." connections (currently using $numConnections of $maxConnections connections)\n\n";
			// Send email
			$msg = "REDCap is currently using $numConnections of $maxConnections connections for ".db_get_server_type().". "
				 . "If REDCap uses all the database connections, the application will be longer be accessible by users and survey respondents. "
				 . "You might consider increasing the \"max_connections\" setting in your MY.CNF (or MY.INI) configuration file for ".db_get_server_type()." to avoid "
				 . "using up all your database connections. If increasing your max connections, you should also proportionally increase the RAM for your database server accordingly. "
				 . "For example, if you increase \"max_connections\" by 50%, you might consider increasing your RAM by 50% as well. "
				 . "Once changed, remember to restart the ".db_get_server_type()." service so that it will take effect.";
			REDCap::email($GLOBALS['project_contact_email'], $GLOBALS['project_contact_email'], "[REDCap Cron Job] Number of ".db_get_server_type()." connections is high ($numConnections of $maxConnections)", $msg);
		}

		// Trim the message to store in cron history
		$GLOBALS['redcapCronJobReturnMsg'] = trim($GLOBALS['redcapCronJobReturnMsg']);
	}


	/**
	 * CLEAR IP CACHE
	 * Clear all IP addresses older than 15 minutes from the redcap_ip_cache table
	 */
	public function ClearIPCache()
	{
		// Delete any rows older than 15 minutes
		$fifteenMinAgo = date("Y-m-d H:i:s", mktime(date("H"),date("i")-15,date("s"),date("m"),date("d"),date("Y")));
		db_query("delete from redcap_ip_cache where timestamp < '$fifteenMinAgo'");
		$rowsDeleted = db_affected_rows();
		// Set cron job message
		if ($rowsDeleted > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$rowsDeleted rows deleted from redcap_ip_cache";
		}
	}


	/**
	 * CLEAR NEW RECORD CACHE
	 * Clear all items from redcap_new_record_cache table older than X days
	 */
	public function ClearNewRecordCache()
	{
		// Delete any rows older than 3 days
		$xDaysAgo = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")-3,date("Y")));
		db_query("delete from redcap_new_record_cache where creation_time < '$xDaysAgo'");
		$rowsDeleted = db_affected_rows();
		// Set cron job message
		if ($rowsDeleted > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$rowsDeleted rows deleted from redcap_new_record_cache";
		}

		// As an extra item, also delete rows in redcap_queue than are more than 1 day old
		$xDaysAgo = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")-1,date("Y")));
		db_query("delete from redcap_queue where updated_at is not null and updated_at < '$xDaysAgo'");
	}


	/**
	 * ERASE TWILIO CALL/SMS LOGS FROM THE TWILIO ACCOUNT
	 * Clear all items from redcap_surveys_erase_twilio_log table.
	 */
	public function EraseTwilioLog()
	{
		// Delete logs
		$rowsDeleted = TwilioRC::EraseTwilioWebsiteLog();
		// Set cron job message
		if ($rowsDeleted > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$rowsDeleted rows deleted from redcap_surveys_erase_twilio_log";
		}
	}


	/**
	 * CLEAR LOG VIEW REQUESTS
	 * Clear all items from redcap_log_view_requests table older than X hours.
	 */
	public function ClearLogViewRequests()
	{
		// Delete any rows older than 24 hours
		$xHoursAgo = date("Y-m-d H:i:s", mktime(date("H")-24,date("i"),date("s"),date("m"),date("d"),date("Y")));
		$sql = "select max(r.lvr_id) from redcap_log_view_requests r, redcap_log_view v
				where v.log_view_id = r.log_view_id and v.ts < '$xHoursAgo'";
		$q = db_query($sql);
		if (db_num_rows($q)) {
			$max_lvr_id = db_result($q, 0);
			$sql = "delete from redcap_log_view_requests where lvr_id <= $max_lvr_id";
			db_query($sql);
			$rowsDeleted = db_affected_rows();
			// Set cron job message
			if ($rowsDeleted > 0) {
				$GLOBALS['redcapCronJobReturnMsg'] = "$rowsDeleted rows deleted from redcap_log_view_requests";
			}
		}
	}


	/**
	 * CLEAR SURVEY SHORT CODES
	 * Clear all survey short codes older than X minutes from the redcap_surveys_short_codes table
	 */
	public function ClearSurveyShortCodes()
	{
		// Delete any rows older than X minutes
		$xMinAgo = date("Y-m-d H:i:s", mktime(date("H"),date("i")-Survey::SHORT_CODE_EXPIRE,date("s"),date("m"),date("d"),date("Y")));
		db_query("delete from redcap_surveys_short_codes where ts < '$xMinAgo'");
		$rowsDeleted = db_affected_rows();
		// Set cron job message
		if ($rowsDeleted > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$rowsDeleted rows deleted from redcap_surveys_short_codes";
		}
	}


	/**
	 * REMOVE TEMP/DELETED FILES
	 * Removes any old files in /temp directory and removes from server any files marked for deletion
	 */
	public function RemoveTempAndDeletedFiles()
	{
		// Delete edocs and REDCap temp files
		$docsDeleted = Files::remove_temp_deleted_files(true);
		// Set cron job message
		if ($docsDeleted > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$docsDeleted documents deleted";
		}
	}

	/**
	 * PUBMED AUTHOR
	 * Send web service request to PubMed to get PubMed IDs for an author within a time period
	 */
	public function PubMed()
	{
		// Determine if this functionality is enabled
		global $pub_matching_enabled, $pub_matching_emails;
		if (!$pub_matching_enabled) return;
		// Instantiate the class to interface with PubMed
		$PubMed = new PubMedRedcap();
		// Query PubMed for all project PIs in REDCap
		$PubMed->searchPubMedByAuthors();
		// Fill in article details/authors for articles that are missing such things
		$PubMed->updateArticleDetails();
		// Update MeSH terms for *all* articles
		$PubMed->updateAllMeshTerms();
		// Update the last time this publication source was crawled
		$db = new RedCapDB();
		$db->updatePubCrawlTime(RedCapDB::PUBSRC_PUBMED);
		// If enabled, email the PIs about their publications
		if ($pub_matching_emails) $PubMed->emailPIs();
		// Set cron job message
		$GLOBALS['redcapCronJobReturnMsg'] =
			"Added {$PubMed->articlesAdded} new pubs; " .
			"Added {$PubMed->matchesAdded} new project-pub matches; " .
			"Added {$PubMed->meshTermsAdded} new MeSH terms.";
		// Output details of job execution
		print $GLOBALS['redcapCronJobReturnMsg'];
	}

	/**
	 * EXPIRE SURVEYS
	 * For any surveys where an expiration timestamp is set, if the timestamp <= NOW, then make the survey inactive.
	 */
	public function ExpireSurveys()
	{
		$sql = "update redcap_surveys set survey_enabled = 0, survey_expiration = null where survey_enabled = 1
				and survey_expiration is not null and timestamp(survey_expiration) <= '" . date('Y-m-d H:i:s') . "'";
		$q = db_query($sql);
		$numSurveysExpired = db_affected_rows();
		db_free_result($q);
		// Set cron job message
		if ($numSurveysExpired > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$numSurveysExpired surveys were expired";
		}
	}


	/**
	 * REMIND USERS VIA EMAIL TO VISIT THE USER ACCESS DASHBOARD
	 * On the first weekday of every month, email all users to remind them to visit the User Access Dashboard page.
	 */
	public function ReminderUserAccessDashboard()
	{
		global $project_contact_email, $lang, $user_access_dashboard_enable;
		// If feature is not enabled for sending emails, then return
		if ($user_access_dashboard_enable != '3') return;
		// Get the first weekday of the current month. Loop through all weekdays and compare their dates to determine.
		$weekdays = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
		$weekdays_dates = array();
		foreach ($weekdays as $this_weekday) {
			$weekdays_dates[$this_weekday] = date("Y-m-d", strtotime(date("Y-m")." $this_weekday"));
		}
		$firstWeekday = min($weekdays_dates);
		// Only continue if TODAY is the first weekday of the month
		if (TODAY != $firstWeekday) return;
		// Reset the queued status for all users (just in case)
		$sql = "update redcap_user_information set user_access_dashboard_email_queued = null";
		$q = db_query($sql);
		// Queue the email reminder for all users with access to the User Rights page in at least one project
		// (exclude suspended users and users w/o email addresses)
		$sql = "update redcap_user_information i2, (select min(i.ui_id) as ui_id, i.user_email
				from redcap_user_information i, redcap_user_rights u
				left join redcap_user_roles r on r.role_id = u.role_id
				where i.username = u.username and ((u.user_rights = 1 and r.user_rights is null) or r.user_rights = 1)
				and i.user_email is not null and i.user_email != '' and i.user_suspended_time is null
				and (select count(*) from redcap_user_rights u2 where u2.project_id = u.project_id) > 1 group by i.user_email) x
				set i2.user_access_dashboard_email_queued = 'QUEUED' where x.ui_id = i2.ui_id";
		$q = db_query($sql);
		$numUsersReminded = db_affected_rows();
		// Now enable the email cron to send the emails to all the queued users. It will disable itself when finished.
		$sql = "update redcap_crons set cron_enabled = 'ENABLED' where cron_name = 'ReminderUserAccessDashboardEmail'";
		$q = db_query($sql);
		// Set cron job message
		if ($numUsersReminded > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$numUsersReminded users reminded to visit the User Access Dashboard";
		}
	}


	/**
	 * REMIND USERS VIA EMAIL BATCHES TO VISIT THE USER ACCESS DASHBOARD (enabled by ReminderUserAccessDashboard cron job)
	 * Email all users in batches to remind them to visit the User Access Dashboard page. Will disable itself when done.
	 */
	public function ReminderUserAccessDashboardEmail()
	{
		global $project_contact_email, $lang, $user_access_dashboard_enable;
		// If feature is not enabled for sending emails, then return
		if ($user_access_dashboard_enable != '3') return;
		// Determine number of emails to send in this batch (use SurveyScheduler function)
		$sqllimit = SurveyScheduler::determineEmailsPerBatch();
		// Get all queued users
		$sql = "select ui_id, user_email from redcap_user_information where user_access_dashboard_email_queued = 'QUEUED'
				order by ui_id limit $sqllimit";
		$q = db_query($sql);
		$numEmailsToSend = db_num_rows($q);
		if ($numEmailsToSend > 0)
		{
			## EMAILS TO SEND
			// Initialize email
			$email = new Message();
			$email->setFrom($project_contact_email);
			$email->setFromName($GLOBALS['project_contact_name']);
			$email->setSubject("[REDCap] {$lang['cron_08']}");
			$emailContents = "{$lang['cron_02']}<br><br>{$lang['cron_12']}<br><br>
							 <a href=\"".APP_PATH_WEBROOT_FULL."index.php?action=user_access_dashboard\">".$lang['cron_21']."</a>";
			$email->setBody($emailContents, true);
			// Get all ui_id's and put in array
			$ui_ids = array();
			while ($row = db_fetch_assoc($q)) {
				$ui_ids[$row['ui_id']] = $row['user_email'];
			}
			// Set all those ui_id's status as SENDING
			$sql = "update redcap_user_information set user_access_dashboard_email_queued = 'SENDING'
					where ui_id in (" . prep_implode(array_keys($ui_ids)) . ")";
			db_query($sql);
			// Loop through users and send emails
			foreach ($ui_ids as $ui_id=>$user_email)
			{
				// Send the email
				$email->setTo($user_email);
				$email->send();
				// Remove user from the email queue
				$sql = "update redcap_user_information set user_access_dashboard_email_queued = null where ui_id = $ui_id";
				db_query($sql);
			}
			// Now check if there are any more emails to send in next cron. If not, then shut off the cron.
			$sql = "select count(1) from redcap_user_information where user_access_dashboard_email_queued = 'QUEUED'";
			$q = db_query($sql);
			$numEmailsToSendNext = db_result($q, 0);
			if ($numEmailsToSendNext < 1) {
				// DONE SENDING EMAILS, SO DISABLE THIS CRON JOB
				$sql = "update redcap_crons set cron_enabled = 'DISABLED' where cron_name = 'ReminderUserAccessDashboardEmail'";
				$q = db_query($sql);
			}
			// Set cron job message
			$GLOBALS['redcapCronJobReturnMsg'] = "$numEmailsToSend users reminded via email (in this batch)";
		}
	}


	/**
	 * SUSPEND INACTIVE USERS
	 * For any users whose last login time or last API activity exceeds the defined max days of inactivity,
	 * auto-suspend their account (if setting enabled).
	 */
	public function SuspendInactiveUsers()
	{
		global $project_contact_email, $lang, $auth_meth_global, $suspend_users_inactive_type, $suspend_users_inactive_days,
			   $suspend_users_inactive_send_email, $user_sponsor_dashboard_enable;
		// If feature is not enabled, then return
		if ($suspend_users_inactive_type == '' || !is_numeric($suspend_users_inactive_days) || $suspend_users_inactive_days < 1) return;
		// Instantiate email object
		$email = new Message();
		$email->setFrom($project_contact_email);
		$email->setFromName($GLOBALS['project_contact_name']);
		// Set current time for this batch
		$local_now = date('Y-m-d H:i:s');
		// Set date of x days ago
		$x_days_ago = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")-$suspend_users_inactive_days,date("Y")));
		// Query users that we need to suspend (if never logged in, then use their user_creation time, else use last login time)
		if (($auth_meth_global == 'ldap_table' || strpos($auth_meth_global,'aaf')>-1 ) && $suspend_users_inactive_type == 'table') { //***<AAF Modification>***
			// Table-based users only
			$sql = "select i.ui_id, i.username, i.user_email, i.user_sponsor, i.user_firstname, i.user_lastname
					from redcap_user_information i, redcap_auth a
					where i.username = a.username and i.user_suspended_time is null
					and (
						(i.user_lastactivity is not null and i.user_lastactivity <= '$x_days_ago' and i.user_lastactivity > i.user_lastlogin)
						or ((i.user_lastactivity is null or i.user_lastactivity < i.user_lastlogin) and i.user_lastlogin is not null and i.user_lastlogin <= '$x_days_ago')
						or (i.user_lastactivity is null and i.user_lastlogin is null and i.user_creation is not null and i.user_creation <= '$x_days_ago')
					)";
		} else {
			// All users
			$sql = "select i.ui_id, i.username, i.user_email, i.user_sponsor, i.user_firstname, i.user_lastname
					from redcap_user_information i where i.user_suspended_time is null
					and (
						(i.user_lastactivity is not null and i.user_lastactivity <= '$x_days_ago' and i.user_lastactivity > i.user_lastlogin)
						or ((i.user_lastactivity is null or i.user_lastactivity < i.user_lastlogin) and i.user_lastlogin is not null and i.user_lastlogin <= '$x_days_ago')
						or (i.user_lastactivity is null and i.user_lastlogin is null and i.user_creation is not null and i.user_creation <= '$x_days_ago')
					)";
		}
		$q = db_query($sql);
		$numUsersInactive = 0;
		while ($row = db_fetch_assoc($q))
		{
			// Set user values
			$user = $row['username'];
			$ui_id = $row['ui_id'];
			$user_email = $row['user_email'];
			// Make sure user hasn't been UNsuspended in past X days (if so, then give them X more days before they get suspended).
			// This ensures that an unsuspended user doesn't get suspended again due to inactivity just because they didn't log into REDCap
			// within 24 hours of being unsuspended (since the suspension cron runs every 24 hours).
			$sql = "SELECT 1 FROM redcap_log_event
					where description in ('Administrator multiple user action: unsuspend', 'Administrator approve: Sponsor request - unsuspend', 'Sponsor request - unsuspend', 'Unsuspend user from REDCap')
					and event = 'MANAGE' and ts > ".str_replace(array(' ',':','-'), array('','',''), $x_days_ago)."
					and (
					    pk = '".db_escape($user)."' 
					    or data_values = 'ui_id in ($ui_id)' or data_values like 'ui_id in ($ui_id,%)'
					    	or data_values like 'ui_id in (%,$ui_id,%)'  or data_values like 'ui_id in (%,$ui_id)'
					        or data_values like 'ui_id in (%, $ui_id,%)' or data_values like 'ui_id in (%, $ui_id)' 
					    or sql_log like '%ui_id in (\'$ui_id\')%' or sql_log like '%ui_id in (%\'$ui_id\',%' 
					        or sql_log like '%ui_id in (%\'$ui_id\')%' or sql_log like '%ui_id in (\'$ui_id\',%)%'
					)
					order by log_event_id desc limit 1";
			$q2 = db_query($sql);
			$user_unsuspended_in_past_x_days = db_num_rows($q2);
			if ($user_unsuspended_in_past_x_days) continue;
			// Set expiration to NULL and set suspended time to NOW
			$sql = "update redcap_user_information set user_suspended_time = '$local_now' where ui_id = $ui_id";
			db_query($sql);
			// Logging
			Logging::logEvent($sql, "redcap_user_information", "MANAGE", $user, "username = '$user'", "Suspend user (via user inactivity)", "", "SYSTEM");
			// Email the user to let them know
			if ($user_email != '' && $suspend_users_inactive_send_email)
			{
				// Determine if user has a sponsor with a valid email address
				$hasSponsor = false;
				if ($row['user_sponsor'] != '') {
					// Get sponsor's email address
					$sponsorUserInfo = User::getUserInfo($row['user_sponsor']);
					if ($sponsorUserInfo !== false && $sponsorUserInfo['user_email'] != '') {
						$hasSponsor = true;
					}
				}
				// Send email to user and/or user+sponsor
				if (!$hasSponsor) {
					// EMAIL USER ONLY
					$email->setCc("");
					$emailContents = 	"{$lang['cron_02']}<br><br>{$lang['cron_03']} \"<b>$user</b>\"
										(<b>{$row['user_firstname']} {$row['user_lastname']}</b>) {$lang['cron_09']}
										$suspend_users_inactive_days {$lang['cron_10']}
										<a href=\"".APP_PATH_WEBROOT_FULL."\">".APP_PATH_WEBROOT_FULL."</a>{$lang['period']} {$lang['cron_11']}";
				} else {
					// EMAIL USER AND CC SPONSOR
					$email->setCc($sponsorUserInfo['user_email']);
					$emailContents =   "{$lang['cron_02']}<br><br>{$lang['cron_13']} \"<b>{$row['username']}</b>\"
										(<b>{$row['user_firstname']} {$row['user_lastname']}</b>) {$lang['cron_20']}
										$suspend_users_inactive_days {$lang['scheduling_25']}{$lang['period']}
										{$lang['cron_38']} \"<b>{$sponsorUserInfo['username']}</b>\"
										(<b>{$sponsorUserInfo['user_firstname']} {$sponsorUserInfo['user_lastname']}</b>){$lang['cron_18']}
										<a href=\"".APP_PATH_WEBROOT_FULL."\">".APP_PATH_WEBROOT_FULL."</a>{$lang['period']}";
					if ($user_sponsor_dashboard_enable) {
						$emailContents .= "<br><br>{$lang['cron_40']} <a href=\"".APP_PATH_WEBROOT_FULL."index.php?action=user_sponsor_dashboard\">{$lang['rights_330']}</a>{$lang['period']}";
					}
					$emailContents .= "<br><br>{$lang['cron_11']}";
				}
				// Send the email
				$email->setTo($user_email);
				$institution_subject = (trim($GLOBALS['institution']) == '') ? '' : " (".$GLOBALS['institution'].")";
				$email->setSubject("[REDCap] {$row['username']}{$lang['cron_22']}".$institution_subject);
				$email->setBody($emailContents, true);
				$email->send();
			}
			// Increment counter
			$numUsersInactive++;
		}
		// Set cron job message
		if ($numUsersInactive > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$numUsersInactive user accounts were suspended (via user inactivity)";
		}
	}


	/**
	 * EXPIRE USERS
	 * For any users whose expiration timestamp is set, if the timestamp <= NOW, then suspend the user's
	 * account and set expiration time back to NULL.
	 */
	public function ExpireUsers()
	{
		global $project_contact_email, $lang, $user_sponsor_dashboard_enable;
		// Instantiate email object
		$email = new Message();
		$email->setFrom($project_contact_email);
		$email->setFromName($GLOBALS['project_contact_name']);
		// Set current time for this batch
		$local_now = date('Y-m-d H:i:s');
		// Query users that we need to expire
		$sql = "select ui_id, username, user_email, user_sponsor, user_firstname, user_lastname
				from redcap_user_information where user_suspended_time is null and
				user_expiration is not null and user_expiration <= '$local_now'";
		$q = db_query($sql);
		$numUsersExpired = db_num_rows($q);
		while ($row = db_fetch_assoc($q)) {
			// Set user values
			$user = $row['username'];
			$ui_id = $row['ui_id'];
			$user_email = $row['user_email'];
			// Set expiration to NULL and set suspended time to NOW
			$sql = "update redcap_user_information set user_expiration = null, user_suspended_time = '$local_now' where ui_id = $ui_id";
			db_query($sql);
			// Logging
			Logging::logEvent($sql, "redcap_user_information", "MANAGE", $user, "username = '$user'", "Suspend user (via user expiration)", "", "SYSTEM");
			// Email the user to let them know
			if ($user_email != '')
			{
				// Determine if user has a sponsor with a valid email address
				$hasSponsor = false;
				if ($row['user_sponsor'] != '') {
					// Get sponsor's email address
					$sponsorUserInfo = User::getUserInfo($row['user_sponsor']);
					if ($sponsorUserInfo !== false && $sponsorUserInfo['user_email'] != '') {
						$hasSponsor = true;
					}
				}
				// Send email to user and/or user+sponsor
				if (!$hasSponsor) {
					// EMAIL USER ONLY
					$email->setCc("");
					$emailContents =   "{$lang['cron_02']}<br><br>{$lang['cron_03']} \"<b>$user</b>\"
										(<b>{$row['user_firstname']} {$row['user_lastname']}</b>) {$lang['cron_04']}
										<a href=\"".APP_PATH_WEBROOT_FULL."\">".APP_PATH_WEBROOT_FULL."</a> {$lang['cron_39']}";
				} else {
					// EMAIL USER AND CC SPONSOR
					$email->setCc($sponsorUserInfo['user_email']);
					$emailContents =   "{$lang['cron_02']}<br><br>{$lang['cron_13']} \"<b>{$row['username']}</b>\"
										(<b>{$row['user_firstname']} {$row['user_lastname']}</b>) {$lang['cron_17']}
										{$lang['cron_38']} \"<b>{$sponsorUserInfo['username']}</b>\"
										(<b>{$sponsorUserInfo['user_firstname']} {$sponsorUserInfo['user_lastname']}</b>){$lang['cron_18']}
										<a href=\"".APP_PATH_WEBROOT_FULL."\">".APP_PATH_WEBROOT_FULL."</a> {$lang['cron_39']}";
					if ($user_sponsor_dashboard_enable) {
						$emailContents .= "<br><br>{$lang['cron_40']} <a href=\"".APP_PATH_WEBROOT_FULL."index.php?action=user_sponsor_dashboard\">{$lang['rights_330']}</a>{$lang['period']}";
					}
				}
				// Send the email
				$email->setTo($user_email);
				$institution_subject = (trim($GLOBALS['institution']) == '') ? '' : " (".$GLOBALS['institution'].")";
				$email->setSubject("[REDCap] {$row['username']}{$lang['cron_19']}".$institution_subject);
				$email->setBody($emailContents, true);
				$email->send();
			}
		}
		// Set cron job message
		if ($numUsersExpired > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$numUsersExpired user accounts were suspended (via user expiration)";
		}
	}


	/**
	 * EMAIL USERS ABOUT UPCOMING ACCOUNT EXPIRATION
	 * For any users whose expiration timestamp is set, if the expiration time is less than X days from now,
	 * then email the user to warn them of their impending account expiration.
	 */
	public function WarnUsersAccountExpiration()
	{
		global $project_contact_email, $lang, $user_sponsor_dashboard_enable;
		// Static number of days before expiration occurs to warn them (first warning, then second warning)
		$warning_days = array(User::USER_EXPIRE_FIRST_WARNING_DAYS, User::USER_EXPIRE_SECOND_WARNING_DAYS); // e.g. 14 days, then 2 days
		// Initialize count
		$numUsersEmailed = 0;
		// Loop through each warning cycle (first/second) and send warning emails for each
		foreach ($warning_days as $days_before_expiration)
		{
			// Set date of x days from now
			$x_days_from_now = date("Y-m-d", mktime(date("H"),date("i"),date("s"),date("m"),date("d")+$days_before_expiration,date("Y")));
			// Instantiate email object
			$email = new Message();
			$email->setFrom($project_contact_email);
			$email->setFromName($GLOBALS['project_contact_name']);
			// Query users that wille expire *exactly* x days from today (since this will only run once per day)
			$sql = "select username, user_email, user_expiration, user_sponsor, user_firstname, user_lastname
					from redcap_user_information where user_expiration is not null and user_suspended_time is null
					and left(user_expiration, 10) = '$x_days_from_now'";
			$q = db_query($sql);
			$numUsersEmailed += db_num_rows($q);
			while ($row = db_fetch_assoc($q))
			{
				// Email the user to warn them
				if ($row['user_email'] != '')
				{
					// Set date and time x days from now
					$mktime = strtotime($row['user_expiration']);
					$x_days_from_now_friendly = date("l, F j, Y", $mktime);
					$x_time_from_now_friendly = date("g:i A", $mktime);
					// Determine if user has a sponsor with a valid email address
					$hasSponsor = false;
					if ($row['user_sponsor'] != '') {
						// Get sponsor's email address
						$sponsorUserInfo = User::getUserInfo($row['user_sponsor']);
						if ($sponsorUserInfo !== false && $sponsorUserInfo['user_email'] != '') {
							$hasSponsor = true;
						}
					}
					// Send email to user and/or user+sponsor
					if (!$hasSponsor) {
						// EMAIL USER ONLY
						$email->setCc("");
						$emailContents =   "{$lang['cron_02']}<br><br>{$lang['cron_03']} \"<b>{$row['username']}</b>\"
											(<b>{$row['user_firstname']} {$row['user_lastname']}</b>) {$lang['cron_06']}
											<b>$x_days_from_now_friendly ($x_time_from_now_friendly)</b>{$lang['period']}
											{$lang['cron_37']} {$lang['cron_24']} <a href=\"".APP_PATH_WEBROOT_FULL."\">".APP_PATH_WEBROOT_FULL."</a> {$lang['cron_39']}";
					} else {
						// EMAIL USER AND CC SPONSOR
						$email->setCc($sponsorUserInfo['user_email']);
						$emailContents =   "{$lang['cron_02']}<br><br>{$lang['cron_13']} \"<b>{$row['username']}</b>\"
											(<b>{$row['user_firstname']} {$row['user_lastname']}</b>) {$lang['cron_06']}
											<b>$x_days_from_now_friendly ($x_time_from_now_friendly)</b>{$lang['period']}
											{$lang['cron_37']} {$lang['cron_38']} \"<b>{$sponsorUserInfo['username']}</b>\"
											(<b>{$sponsorUserInfo['user_firstname']} {$sponsorUserInfo['user_lastname']}</b>){$lang['cron_15']}
											<a href=\"".APP_PATH_WEBROOT_FULL."\">".APP_PATH_WEBROOT_FULL."</a> {$lang['cron_39']}";
						if ($user_sponsor_dashboard_enable) {
							$emailContents .= "<br><br>{$lang['cron_40']} <a href=\"".APP_PATH_WEBROOT_FULL."index.php?action=user_sponsor_dashboard\">{$lang['rights_330']}</a>{$lang['period']}";
						}
					}
					// Send the email
					$email->setTo($row['user_email']);
					$institution_subject = (trim($GLOBALS['institution']) == '') ? '' : " (".$GLOBALS['institution'].")";
					$email->setSubject("[REDCap] {$row['username']}{$lang['cron_16']} $days_before_expiration {$lang['scheduling_25']}".$institution_subject);
					$email->setBody($emailContents, true);
					$email->send();
				}
			}
		}
		// Set cron job message
		if ($numUsersEmailed > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$numUsersEmailed users were emailed to warn them of their upcoming account expiration";
		}
	}

	/**
	 * SURVEY INVITATION EMAILER
	 * For any surveys having survey invitations that have been scheduled, send any invitations that are ready to be sent.
	 */
	public function SurveyInvitationEmailer()
	{
		list ($emailCountSuccess, $emailCountFail) = SurveyScheduler::emailInvitations();
		// Set email-sending success/fail count message
		if ($emailCountSuccess + $emailCountFail > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$emailCountSuccess survey invitations sent successfully, " .
												 "\n$emailCountFail survey invitations failed to send";
		}
	}

    /**
     * ALERTS & NOTIFICATIONS SENDER
     */
    public function AlertsNotificationsSender()
    {
        $eta = new Alerts();
        list ($emailCountSuccess, $emailCountFail) = $eta->sendNotificationsViaCron();
        // Set email-sending success/fail count message
        if ($emailCountSuccess + $emailCountFail > 0) {
            $GLOBALS['redcapCronJobReturnMsg'] = "$emailCountSuccess alert notifications sent successfully, " .
                "\n$emailCountFail alert notifications failed to send";
        }
    }

    /**
     * ALERTS & NOTIFICATIONS DATEDIFF CHECKER
     */
    public function AlertsNotificationsDatediffChecker()
    {
        $eta = new Alerts();
        list ($num_scheduled_total, $num_removed_total, $num_records_affected) = $eta->checkAlertsWithDatediffViaCron();
        // Set cron job message
        if ($num_scheduled_total > 0) {
            $GLOBALS['redcapCronJobReturnMsg'] = "$num_scheduled_total alert notifications were successfully scheduled via datediff(...today...) function.";
        }
    }

	/**
	 * DELETE PROJECTS
	 * Permanently delete projects that were "deleted" by users X days ago
	 */
	public function DeleteProjects()
	{
		// Get timestamp of Project::DELETE_PROJECT_DAY_LAG days ago
		$thirtyDaysAgo = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")-Project::DELETE_PROJECT_DAY_LAG,date("Y")));
		// Get all projects scheduled for deletion
		$sql = "select project_id from redcap_projects where date_deleted is not null
				and date_deleted != '0000-00-00 00:00:00' and date_deleted <= '$thirtyDaysAgo'";
		$q = db_query($sql);
		$numProjDeleted = db_num_rows($q);
		while ($row = db_fetch_assoc($q))
		{
			// Permanently delete the project from all db tables right now (as opposed to flagging it for deletion later)
			deleteProjectNow($row['project_id']);
		}
		db_free_result($q);
		// Set cron job message
		if ($numProjDeleted > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$numProjDeleted projects were deleted";
		}
	}


	/**
	 * DDP DATA IMPORT
	 * Seed mr_id's for all records in all projects utilizing DDP service and also queue records that
	 * are ready to be fetched from the source system (excludes archived/inactive projects).
	 */
	public function DDPQueueRecordsAllProjects()
	{
		// Don't do anything here unless DDP is enabled AND OpenSSL is installed
		$DDP = new DynamicDataPull(0, 'FHIR');
		if (!($DDP->isEnabledInSystem() || $DDP->isEnabledInSystemFhir()) || !openssl_loaded()) return;
		// Perform the seeding
		$recordsSeeded = DynamicDataPull::seedMrIdsAllProjects();
		// Set records as queued for those ready to be fetched from the source system
		$recordsQueued = DynamicDataPull::setQueuedFetchStatusAllProjects();
		// Set cron job message
		if ($recordsSeeded + $recordsQueued > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "DDP - $recordsSeeded records were seeded and $recordsQueued records were queued";
		}
	}


	/**
	 * DDP DATA IMPORT
	 * Fetch source system data for records in all projects utilizing DDP service.
	 * Perform fetch one project at a time (via HTTP Get request to DynamicDataPull/cron.php due to limitations with project-level methods
	 * used in the fetch method).
	 */
	public function DDPFetchRecordsAllProjects()
	{
		// Don't do anything here unless DDP is enabled AND OpenSSL is installed
		$DDP = new DynamicDataPull(0);
		if (!($DDP->isEnabledInSystem() || $DDP->isEnabledInSystemFhir()) || !openssl_loaded()) return;
		// Fetch data for queued records
		$num_records_fetched = DynamicDataPull::fetchQueuedRecordsFromSource();
		// Set cron job message
		if ($num_records_fetched > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "DDP - $num_records_fetched records had data fetched from the external source system";
		}
	}


	/**
	 * DDP RE-ENCRYPT DATA
	 * Due to Mcrypt PHP extension being deprecated in PHP 7.1, re-encrypt all the cached DDP data values
	 */
	public function DDPReencryptData()
	{
		// Don't do anything here unless DDP is enabled AND OpenSSL is installed
		$DDP = new DynamicDataPull(0);
		if (!$DDP->isEnabledInSystem() || !openssl_loaded()) return;
		// Re-encrypt all the cached DDP data values in batches
		$num_values_encrypted = DynamicDataPull::reencryptCachedData();
		// Set cron job message
		if ($num_values_encrypted > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "DDP - $num_values_encrypted values from the external source system were re-encrypted";
		} elseif ($num_values_encrypted == 0) {
			// Since we're completed done, disable this job, and re-enabled the DDPFetchRecordsAllProjects job
			db_query("update redcap_crons set cron_enabled = 'DISABLED' where cron_name = 'DDPReencryptData'");
			db_query("update redcap_crons set cron_enabled = 'ENABLED'  where cron_name = 'DDPFetchRecordsAllProjects'");
		}
	}


	/**
	 * PURGE CRON HISTORY
	 * Purges all rows from the cron history table that are older than one week.
	 */
	public function PurgeCronHistory()
	{
		// Get timestamp of 7 days ago
		$sevenDaysAgo = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")-7,date("Y")));
		// Delete all rows older than 7 days old
		$sql = "delete from redcap_crons_history where (cron_run_end is not null and cron_run_end < '$sevenDaysAgo')
				or (cron_run_end is null and cron_run_start < '$sevenDaysAgo')";
		$q = db_query($sql);
		$num_rows_deleted = db_affected_rows();
		// Set cron job message
		if ($num_rows_deleted > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$num_rows_deleted rows were deleted from the crons history table";
		}
	}


	/**
	 * SEND EMAIL TO ALL TABLE-BASED USERS TELLING THEM TO LOG IN FOR THE PURPOSE OF UPGRADING THEIR PASSWORD SECURITY (ONE TIME ONLY)
	 */
	public function UpdateUserPasswordAlgo()
	{
		global $lang, $homepage_contact_email;
		// Initialize email object
		$email = new Message();
		$email->setFrom($homepage_contact_email);
		$email->setFromName($GLOBALS['homepage_contact']);
		// Now loop through ALL table-based users and reset their password
		$sql = "select a.username, i.user_email from redcap_auth a, redcap_user_information i
				where a.username = i.username and a.legacy_hash = 1 and i.user_suspended_time is null
				and i.user_email is not null order by a.username";
		$q = db_query($sql);
		$num_emailed = db_num_rows($q);
		while ($row = db_fetch_assoc($q))
		{
			// Send email to user notifying them of their password reset (if user has an associate primary email address listed
			// AND if they are not a suspended user).
			$email->setTo($row['user_email']);
			$email->setSubject($lang['rights_282']);
			$emailContents = "{$lang['cron_02']}<br /><br />{$lang['rights_283']} \"<b>{$row['username']}</b>\"{$lang['period']}
				{$lang['rights_284']}<br /><br />{$lang['rights_285']}
				<a href=\"mailto:$homepage_contact_email\">$homepage_contact_email</a>{$lang['period']}<br /><br />
				<b>REDCap</b> - <a href=\"".APP_PATH_WEBROOT_FULL."\">".APP_PATH_WEBROOT_FULL."</a>";
			$email->setBody($emailContents, true);
			$email->send();
		}
		// When done, disable the cron job
		$sql = "update redcap_crons set cron_enabled = 'DISABLED' where cron_name = 'UpdateUserPasswordAlgo'";
		$q = db_query($sql);
		// Set cron job message
		if ($num_emailed > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$num_emailed users were sent an email to tell them to log in, which will upgrade the security standard of their account.";
		}
	}


	/**
	 * CHECK ALL DATEDIFF CONDITION LOGIC IN AUTOMATED SURVEYS INVITATIONS
	 * If any project uses "today" variable inside a datediff() for ASI conditional logic,
	 * then check EVERY record in the project to see if it need invitations to be scheduled.
	 * This is done separately from the regular scheduler because using datediff() with "today" means that
	 * the data can change every day without a person trigger the change, so it needs to be triggered automatically
	 * by the system each day to check.
	 */
	public function AutomatedSurveyInvitationsDatediffChecker()
	{
		global $Proj;
		// Keep count of all invitations that get scheduled
		$num_scheduled_total = 0;
		// Get a list of all projects that are using active, time-based conditional logic for automated notifications
		$sql = "SELECT distinct s.project_id FROM redcap_surveys_scheduler ss, redcap_surveys s, redcap_projects p
				WHERE ss.active = 1 AND p.status <= 1 AND s.survey_id = ss.survey_id AND p.project_id = s.project_id
				AND (ss.condition_logic like '%datediff%(%today%,%)%' or ss.condition_logic like '%datediff%(%now%,%)%')
				order by s.project_id";
		$q = db_query($sql);
		while ($row = db_fetch_assoc($q))
		{
			// Instantiate Project object for this project and make sure it gets set as global (so that we don't have to recreate the object for EACH RECORD fetched)
			$Proj = new Project($row['project_id']);
			// Get a list of all records for the project
			$data = Records::getData($row['project_id'], 'array', null, $Proj->table_pk);
			// If project has no records, then go to next project
			if (empty($data)) continue;
			// Instantiate SurveyScheduler object for this project
			$surveyScheduler = new SurveyScheduler($row['project_id']);
			// Go through each record and check if each has any invitations that need to be scheduled
			foreach (array_keys($data) as $id) {
				// Check if record needs any schedulings done, and increment count of invitations scheduled, if any
				list ($this_num_scheduled_total, $this_num_deleted_total, $numRecordsAffected) = $surveyScheduler->checkToScheduleParticipantInvitation($id);
				$num_scheduled_total += $this_num_scheduled_total;
			}
		}
		// Free up memory
		unset($data, $Proj);
		// Set cron job message
		if ($num_scheduled_total > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$num_scheduled_total survey invitations were successfully scheduled via datediff(...today...) function.";
		}
	}
	
	/**
	 * CHECK ALL DATEDIFF CONDITION LOGIC IN AUTOMATED SURVEYS INVITATIONS 
	 * (optimized version of AutomatedSurveyInvitationsDatediffChecker)
	 * If any project uses "today" variable inside a datediff() for ASI conditional logic,
	 * then check EVERY record in the project to see if it need invitations to be scheduled.
	 * This is done separately from the regular scheduler because using datediff() with "today" means that
	 * the data can change every day without a person trigger the change, so it needs to be triggered automatically
	 * by the system each day to check.
	 */
	public function AutomatedSurveyInvitationsDatediffChecker2()
	{
        // Keep count of all invitations that get scheduled
        $num_scheduled_total = 0;

        // Get a list of all projects that are using active, time-based conditional logic for automated notifications
        $sql = "SELECT distinct s.project_id FROM redcap_surveys_scheduler ss, redcap_surveys s, redcap_projects p
				WHERE ss.active = 1 AND p.status <= 1 AND p.date_deleted is null AND p.completed_time is null AND s.survey_id = ss.survey_id AND p.project_id = s.project_id
				AND (ss.condition_logic like '%datediff%(%today%,%)%' or ss.condition_logic like '%datediff%(%now%,%)%')
				order by s.project_id desc";
        $q = db_query($sql);

        // TODO: ABM METRICS FOR ENTIRE CRON
        $num_rows = db_num_rows($q);
        $current_row = 0;
        $script_time_start = microtime(true);
        $max_execution_time = ini_get('max_execution_time');
        $script_memory_peak = 0;
        System::increaseMemory(2048); // Increase memory to 2GB to prevent timeout
		
        // TODO: Optional debug log file - if set to empty nothing will happen
        $debug_log_file = null;
       //  $debug_log_file = APP_PATH_TEMP . date('YmdHis') . "_" . __FUNCTION__ . ".log";

        // Loop through each project with datediff+today
        while ($row = db_fetch_assoc($q))
        {
            // TODO: ABM METRICS FOR CURRENT PROJECT
            $project_time_start     = microtime(true);
            $project_memory_start   = memory_get_usage();
            $current_row            = $current_row + 1;

            // Instantiate SurveyScheduler object for this project
            $surveyScheduler = new SurveyScheduler($row['project_id']);

            // Tell SurveyScheduler that this is a 'datediff+today' check for scheduler optimizations
            $surveyScheduler->datediff_today_check = true;

            // Loop through each survey-event and process all records
            $surveyScheduler->checkAutomatedSurveyInvitationsBulk();

            $num_scheduled_total += $surveyScheduler->num_scheduled_total;

            // OPTIONAL DEBUG OF PROGRESS
			if (!empty($debug_log_file)) 
			{
				// ABM: A NICE DEBUG TABLE TO SHOW PROGRESS IN EVALUATING PROJECTS
				$script_time_total      = microtime(true) - $script_time_start;
				$project_time_total     = microtime(true) - $project_time_start;
				$project_memory_total   = memory_get_usage() - $project_memory_start;
				$script_memory_peak     = max($script_memory_peak, memory_get_usage());

				$msg = "[" . date("Y-m-d H:i:s") . "]\t" .
					str_pad($current_row,4) . " of $num_rows\t" .
					$row['project_id'] . "\t" .
					sprintf("%.2f", $project_time_total) . " sec\t" .
					"Total: "   . sprintf("%.2f", $script_time_total) . " sec\t" .
					"MET: $max_execution_time\t" .
					"Mem: "     . sprintf("%.2f", $project_memory_total / 1024 / 1024) . " MB\t" .
					"Total: "   . sprintf("%.2f", memory_get_usage()    / 1024 / 1024) . " MB\t" .
					$surveyScheduler->num_scheduled_total . " scheduled from " . count($surveyScheduler->record_data) . " records" . "\t" .
					"\n";
				file_put_contents($debug_log_file, $msg, FILE_APPEND);
            }

            // Clean up SurveyScheduler
            unset($surveyScheduler);
        }

       // Set cron job message
		if ($num_scheduled_total > 0) {
			// $GLOBALS['redcapCronJobReturnMsg'] = "$num_scheduled_total survey invitations scheduled in " .
				// sprintf("%.2f", microtime(true) - $script_time_start ) . " seconds using " .
				// sprintf("%.2f", $script_memory_peak / 1024 / 1024) . " MB memory in datediff(...today...) function.";
			$GLOBALS['redcapCronJobReturnMsg'] = "$num_scheduled_total survey invitations were successfully scheduled via datediff(...today...) function.";
		}
    }
	

	/**
	 * Send a email notification about new messages in REDCapMC to
	 * logged out users
	 */
	public function UserMessagingEmailNotifications()
	{
		global $project_contact_email, $lang, $user_messaging_enabled, $autologout_timer, $redcap_version;
		// If not enabled, then do nothing
		if (!$user_messaging_enabled) return;
		// Check auto-logout timer
		$this_autologout_timer = empty($autologout_timer) ? 30 : $autologout_timer;
		$users = array();
		$minAgo5 = date("Y-m-d H:i:s", mktime(date("H"),date("i")-5,date("s"),date("m"),date("d"),date("Y")));
		$hoursAgo2 = date("Y-m-d H:i:s", mktime(date("H")-2,date("i"),date("s"),date("m"),date("d"),date("Y")));
		$hoursAgo4 = date("Y-m-d H:i:s", mktime(date("H")-4,date("i"),date("s"),date("m"),date("d"),date("Y")));
		$hoursAgo6 = date("Y-m-d H:i:s", mktime(date("H")-6,date("i"),date("s"),date("m"),date("d"),date("Y")));
		$hoursAgo8 = date("Y-m-d H:i:s", mktime(date("H")-8,date("i"),date("s"),date("m"),date("d"),date("Y")));
		$hoursAgo12 = date("Y-m-d H:i:s", mktime(date("H")-12,date("i"),date("s"),date("m"),date("d"),date("Y")));
		$hoursAgo24 = date("Y-m-d H:i:s", mktime(date("H")-24,date("i"),date("s"),date("m"),date("d"),date("Y")));
		$autoLogoutWindow = date("Y-m-d H:i:s", mktime(date("H"),date("i")-$this_autologout_timer,date("s"),date("m"),date("d"),date("Y")));
		// For users receiving general/system notifications, only send them to users who have been active in past 6 months
		$activeInPast6Months = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m")-6,date("d"),date("Y")));
		//list of all users with unread messages (ignore the System Notifications and one-way admin notifications)
		$sql = "select distinct trim(x.username) as username
				from (
					select u.username, u.ui_id, u.messaging_email_preference, u.messaging_email_ts, max(m.sent_time) as last_unread_message_ts, 
						u.messaging_email_urgent_all, if (u.messaging_email_urgent_all='1', max(s.urgent), 0) as has_urgent_messages,
						u.messaging_email_general_system, max(r.log_view_id) as log_view_id 
					from redcap_messages_threads t, redcap_messages m, redcap_messages_status s, redcap_user_information u
					left join redcap_log_view_requests r on r.ui_id = u.ui_id
					where s.message_id = m.message_id and m.thread_id = t.thread_id and t.invisible = 0 and t.archived = 0
					  	and s.recipient_user_id = u.ui_id and u.username != '' and u.username != 'site_admin' and isnull(u.user_suspended_time) 
					  	and u.user_email != '' and u.user_email is not null
						and (isnull(u.messaging_email_ts) or m.sent_time > u.messaging_email_ts) 
						and ((u.messaging_email_general_system = 1 and u.user_lastlogin > '$activeInPast6Months') 
							or (u.messaging_email_general_system = 0 and m.thread_id not in (1, 3)))
						and (u.messaging_email_preference != 'NONE' or u.messaging_email_urgent_all = '1')
						and !(u.messaging_email_urgent_all = '0' and
						    (u.messaging_email_preference = 'NONE'
								or (u.messaging_email_preference = '2_HOURS'  and u.messaging_email_ts >= '$hoursAgo2')
								or (u.messaging_email_preference = '4_HOURS'  and u.messaging_email_ts >= '$hoursAgo4')
								or (u.messaging_email_preference = '6_HOURS'  and u.messaging_email_ts >= '$hoursAgo6')
								or (u.messaging_email_preference = '8_HOURS'  and u.messaging_email_ts >= '$hoursAgo8')
								or (u.messaging_email_preference = '12_HOURS' and u.messaging_email_ts >= '$hoursAgo12')
								or (u.messaging_email_preference = 'DAILY'    and u.messaging_email_ts >= '$hoursAgo24')
							)
						)
					group by u.username
				) x 
				left join redcap_log_view l on l.log_view_id = x.log_view_id
				where (isnull(l.page) or l.page != 'api/index.php')
					and (isnull(l.ts) or l.ts <= '$autoLogoutWindow' or (l.ts > '$autoLogoutWindow' and l.event = 'LOGOUT' and l.ts < '$minAgo5'))
					and !(x.messaging_email_preference = 'NONE' and x.messaging_email_urgent_all = '1' and x.has_urgent_messages = 0)
					and (isnull(x.messaging_email_ts) or (x.last_unread_message_ts > x.messaging_email_ts))
				order by trim(x.username)";
		$q = db_query($sql);
		while ($row = db_fetch_assoc($q))
		{
			$users[] = strtolower($row['username']);
		}

		// Instantiate email object
		$email = new Message();
		$email->setFrom($project_contact_email);
		$email->setFromName($GLOBALS['project_contact_name']);
		// Set current time
		$numUsers = count($users);
		if ($numUsers > 0) {
			foreach ($users as $user) {
				//send an email to all users in $users array
				$userinfo = User::getUserInfo($user);
				$user = $userinfo['username'];
				$ui_id = $userinfo['ui_id'];
				$user_email = $userinfo['user_email'];
				// If user was just emailed in past 2 hours, don't send again (only for users that have NOT enabled "instant notifications for important messages and @mentions")
				if ($userinfo['messaging_email_urgent_all'] == '0' && $userinfo['messaging_email_ts'] != '' && (strtotime("now")-strtotime($userinfo['messaging_email_ts'])) < 7200) {
					continue;
				}
				// Email the user to let them know of the unread messages in REDCapMC
				$unread_convs = Messenger::findSingleConvUnread($ui_id);
				$list = '';
				$unread_count_total = 0;
				foreach ($unread_convs as $thread_id=>$item) {
					if ($userinfo['messaging_email_general_system'] == '0' && ($thread_id == '1' || $thread_id == '3')) continue; // Skip notifications if user is set not to receive them
					$channel_name = $item['channel_name'];
					$unread_count_total += $item['unread'];
					$list .= '<br> - '.$item['unread'].' '.($item['unread'] > 1 ? $lang['cron_32'] : $lang['cron_31']).' "'.strip_tags($channel_name).'"';
				}
				// Don't send email if nothing to report
				if ($unread_count_total == 0) continue;
				// Set timestamp of sending this email to this user (so it doesn't get sent again)
				Messenger::setMessagingEmailTs($user);
				// Send the email
				$emailContents = ($unread_count_total > 1 ? $lang['cron_25'] : $lang['cron_35'])."
					$unread_count_total " . ($unread_count_total > 1 ? $lang['cron_29'] : $lang['cron_34'])." <b>$user</b>{$lang['period']}
					{$lang['cron_26']} <a href=\"".APP_PATH_WEBROOT_FULL."?__messenger=open\">{$lang['cron_30']}</a>{$lang['period']}<br>
					$list<br><br>-----------------------------------------------<br>{$lang['messaging_15']}
					<a href=\"".APP_PATH_WEBROOT_FULL."redcap_v{$redcap_version}/Profile/user_profile.php\">{$lang['config_functions_50']}</a>
					{$lang['global_14']}{$lang['period']}";
				$email->setTo($user_email);
				$email->setSubject("{$lang['messaging_16']} $unread_count_total " . ($unread_count_total > 1 ? $lang['cron_27'] : $lang['cron_33']));
				$email->setBody($emailContents, true);
				$email->send();
			}
		}

		// Set cron job message
		if ($numUsers > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$numUsers users were notified via email about receiving a user message or notification via REDCap Messenger.";
		}
	}


	/**
	 * DELETE DATA EXPORT FILES IN THE FILE REPOSITORY OF SPECIFIC PROJECTS
	 * For projects with this feature enabled, delete all archived data export files older than X days.
	 */
	public function DeleteFileRepositoryExportFiles()
	{
		$sql = "select d.docs_id, e.doc_id
				from redcap_projects p, redcap_docs d, redcap_docs_to_edocs t, redcap_edocs_metadata e
				where d.project_id = p.project_id and d.export_file = 1 and d.docs_id = t.docs_id and t.doc_id = e.doc_id and e.delete_date is null
				and p.delete_file_repository_export_files is not null and p.delete_file_repository_export_files > 0
				and DATE_ADD(e.stored_date, INTERVAL delete_file_repository_export_files DAY) <= '".NOW."'";
		$q = db_query($sql);
		$filesDeleted = 0;
		while ($row = db_fetch_assoc($q)) 
		{
			// Remove each from file tables
			$sql = "delete from redcap_docs where docs_id = {$row['docs_id']} and export_file = 1";
			if (db_query($sql)) {
				$filesDeleted += db_affected_rows();
				$sql = "update redcap_edocs_metadata set delete_date = '".NOW."' where doc_id = {$row['doc_id']}";
				db_query($sql);
			}
		}
		// Set cron job message
		if ($filesDeleted > 0) {
			$GLOBALS['redcapCronJobReturnMsg'] = "$filesDeleted data export files deleted from projects' File Repository";
		}
	}
}
