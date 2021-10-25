<?php



/**
 * UserRights Class
 * Contains methods used with regard to user privileges
 */
class UserRights
{
	// Map pages to user_rights table values to determine rights for a given page (e.g., PAGE=>field from user_rights table).
	// Also maps Route from query string (&route=Class:Method), if exists.
	public $page_rights = array(
		// Routes that need to be allowlisted but are not mappable to a $user_rights element.
		// Their format will be "Class/Method"=>"" (the value should stay as an empty string).
		"ProjectDashController:view"=>"",
		"ProjectDashController:viewpanel"=>"",
		"ProjectDashController:colorblind"=>"",
		"DataEntryController:saveShowInstrumentsToggle"=>"",
		"DataEntryController:renderInstancesTable"=>"",
		"DataEntryController:assignRecordToDag"=>"",
		"DataEntryController:passwordVerify"=>"",
		"DataEntryController:openSurveyValuesChanged"=>"",
		"DataEntryController:getResponseContributors"=>"",
		"DataEntryController:buildRecordListCache"=>"",
		"DataEntryController:clearRecordListCache"=>"",
		"UserRightsController:impersonateUser"=>"",
		"PdfController:index"=>"",
		"DataAccessGroupsController:switchDag"=>"",
        "DataAccessGroupsController:downloadDag"=>"",
        "DataAccessGroupsController:downloadUserDag"=>"",
        "DataAccessGroupsController:uploadDag"=>"",
        "DataAccessGroupsController:uploadUserDag"=>"",
		// Data Entry
		"DataEntryController:renameRecord"=>"record_rename",
		"DataEntryController:deleteRecord"=>"record_delete",
		"DataEntryController:deleteEventInstance"=>"record_delete",
		"DataEntryController:recordExists"=>"",
		// Export & Reports
		"DataExport/data_export_tool.php"=>"data_export_tool",
		"DataExport/data_export_csv.php"=>"data_export_tool",
		"DataExport/file_export_zip.php"=>"data_export_tool",
		"DataExport/data_export_ajax.php"=>"data_export_tool",
		"DataExport/report_order_ajax.php"=>"reports",
		"DataExport/report_edit_ajax.php"=>"reports",
		"DataExport/report_delete_ajax.php"=>"reports",
		"DataExport/report_user_access_list.php"=>"reports",
		"DataExport/report_copy_ajax.php"=>"reports",
		"DataExport/report_filter_ajax.php"=>"reports",
		"DataExport/report_public_enable.php"=>"reports",
		"ReportController:reportFoldersDialog"=>"reports",
		"ReportController:reportFolderCreate"=>"reports",
		"ReportController:reportFolderEdit"=>"reports",
		"ReportController:reportFolderDelete"=>"reports",
		"ReportController:reportFolderDisplayTable"=>"reports",
		"ReportController:reportFolderDisplayTableAssign"=>"reports",
		"ReportController:reportFolderDisplayDropdown"=>"reports",
		"ReportController:reportFolderAssign"=>"reports",
		"ReportController:reportFolderResort"=>"reports",
		"ReportController:reportSearch"=>"",
		// Import
		"DataImportController:index"=>"data_import_tool",
		"DataImportController:downloadTemplate"=>"data_import_tool",
		// Data Comparison Tool
		"DataComparisonController:index"=>"data_comparison_tool",
		// Logging
		"Logging/index.php"=>"data_logging",
		"Logging/csv_export.php"=>"data_logging",
		// File Repository
		"FileRepository/index.php"=>"file_repository",
		// User Rights
		"UserRights/index.php"=>"user_rights",
		"UserRights/search_user.php"=>"user_rights",
		"UserRights/assign_user.php"=>"user_rights",
		"UserRights/edit_user.php"=>"user_rights",
		"UserRights/user_account_exists.php"=>"user_rights",
		"UserRights/set_user_expiration.php"=>"user_rights",
		"UserRights/import_export_users.php"=>"user_rights",
		"UserRights/get_user_dag_role.php"=>"user_rights",
		"UserRightsController:displayRightsRolesTable"=>"user_rights",
		// DAGs
		"DataAccessGroupsController:index"=>"data_access_groups",
		"DataAccessGroupsController:ajax"=>"data_access_groups",
		"DataAccessGroupsController:saveUserDAG"=>"data_access_groups",
		"DataAccessGroupsController:getDagSwitcherTable"=>"data_access_groups",
		// Graphical & Stats
		"Graphical/index.php"=>"graphical",
		"Graphical/pdf.php"=>"graphical",
		"DataExport/plot_chart.php"=>"graphical",
		"DataExport/stats_highlowmiss.php"=>"graphical",
		"Graphical/image_base64_download.php"=>"graphical",
		// Calendar
		"Calendar/index.php"=>"calendar",
		"Calendar/calendar_popup.php"=>"calendar",
		"Calendar/calendar_popup_ajax.php"=>"calendar",
		"DataEntryController:renderUpcomingCalEvents"=>"calendar",
        "Calendar/scheduling.php"=>"calendar",
        "Calendar/scheduling_ajax.php"=>"calendar",
		// Locking records
		"Locking/locking_customization.php"=>"lock_record_customize",
		"Locking/esign_locking_management.php"=>"lock_record",
		"DataEntryController:lockWholeRecordPdfRender"=>"lock_record_multiform",
		// DTS
		"DtsController:adjudication"=>"dts",
		// Invite survey participants
		"Surveys/add_participants.php"=>"participants",
		"Surveys/invite_participants.php"=>"participants",
		"Surveys/delete_participant.php"=>"participants",
		"Surveys/edit_participant.php"=>"participants",
		"Surveys/participant_export.php"=>"participants",
		"Surveys/shorturl.php"=>"participants",
		"Surveys/shorturl_custom.php"=>"participants",
		"Surveys/participant_list.php"=>"participants",
		"Surveys/participant_list_enable.php"=>"participants",
		"Surveys/view_sent_email.php"=>"participants",
		"Surveys/get_access_code.php"=>"participants",
		"Surveys/invite_participant_popup.php"=>"participants",
		"Surveys/invitation_log_export.php"=>"participants",
		"SurveyController:changeLinkExpiration"=>"participants",
		"SurveyController:renderUpcomingScheduledInvites"=>"participants",
        "SurveyController:enableCaptcha"=>"participants",
		// Data Quality
		"DataQuality/execute_ajax.php"=>"data_quality_execute",
		"DataQuality/edit_rule_ajax.php"=>"data_quality_design",
		// Randomization
		"Randomization/index.php"=>"random_setup",
		"Randomization/upload_allocation_file.php"=>"random_setup",
		"Randomization/download_allocation_file.php"=>"random_setup",
		"Randomization/download_allocation_file_template.php"=>"random_setup",
		"Randomization/check_randomization_field_data.php"=>"random_setup",
		"Randomization/delete_allocation_file.php"=>"random_setup",
		"Randomization/save_randomization_setup.php"=>"random_setup",
		"Randomization/dashboard.php"=>"random_dashboard",
		"Randomization/dashboard_all.php"=>"random_dashboard",
		"Randomization/randomize_record.php"=>"random_perform",
		// Setup & Design
		"ProjectGeneral/copy_project_form.php"=>"design",
		"ProjectGeneral/change_project_status.php"=>"design",
		"Design/define_events.php"=>"design",
		"Design/edit_field_prefill.php"=>"design",
		"Design/edit_matrix_prefill.php"=>"design",
		"Design/define_events_ajax.php"=>"design",
		"Design/designate_forms.php"=>"design",
		"Design/designate_forms_ajax.php"=>"design",
		"Design/data_dictionary_upload.php"=>"design",
		"Design/data_dictionary_download.php"=>"design",
		"Design/data_dictionary_snapshot.php"=>"design",
		"RepeatInstanceController:renderSetup"=>"design",
		"RepeatInstanceController:saveSetup"=>"design",
		"ProjectGeneral/edit_project_settings.php"=>"design",
		"ProjectGeneral/modify_project_setting_ajax.php"=>"design",
		"ProjectGeneral/delete_project.php"=>"design",
		"Design/delete_form.php"=>"design",
		"ProjectGeneral/erase_project_data.php"=>"design",
		"ProjectSetup/other_functionality.php"=>"design",
		"ProjectSetup/project_revision_history.php"=>"design",
		"IdentifierCheckController:index"=>"design",
		"Design/online_designer.php"=>"design",
		"SharedLibrary/index.php"=>"design",
		"SharedLibrary/receiver.php"=>"design",
		"ProjectSetup/checkmark_ajax.php"=>"design",
		"ProjectSetup/export_project_odm.php"=>"design",
		"Surveys/edit_info.php"=>"design",
		"Surveys/create_survey.php"=>"design",
		"Surveys/survey_online.php"=>"design",
		"Surveys/delete_survey.php"=>"design",
		"Design/draft_mode_review.php"=>"design",
		"Design/draft_mode_enter.php"=>"design",
		"Design/draft_mode_notified.php"=>"design",
		"Design/draft_mode_cancel.php"=>"design",
		"ExternalLinks/index.php"=>"design",
		"ExternalLinks/edit_resource_ajax.php"=>"design",
		"ExternalLinks/save_resource_users_ajax.php"=>"design",
		"Design/calculation_equation_validate.php"=>"design",
		"Design/branching_logic_builder.php"=>"design",
		"Design/survey_login_setup.php"=>"design",
		"Design/existing_choices.php"=>"design",
		"Surveys/automated_invitations_setup.php"=>"design",
		"Surveys/survey_queue_setup.php"=>"design",
		"Design/zip_instrument_download.php"=>"design",
		"Design/zip_instrument_upload.php"=>"design",
		"Design/copy_instrument.php"=>"design",
		"Surveys/twilio_check_request_inspector.php"=>"design",
		"Surveys/theme_view.php"=>"design",
		"Surveys/theme_save.php"=>"design",
		"Surveys/theme_manage.php"=>"design",
		"Surveys/copy_design_settings.php"=>"design",
		"Design/arm_upload.php"=>"design",
		"Design/arm_download.php"=>"design",
		"Design/event_upload.php"=>"design",
		"Design/event_download.php"=>"design",
		"Design/instrument_event_mapping_upload.php"=>"design",
		"Design/instrument_event_mapping_download.php"=>"design",
		"RecordDashboardController:save"=>"design",
		"RecordDashboardController:delete"=>"design",
		"SurveyController:reevalAutoInvites"=>"design",
		"SurveyController:displayAutoInviteSurveyEventCheckboxList"=>"design",
		"Design/field_bank_search.php"=>"design",
		"Design/add_field_via_fieldbank.php"=>"design",
		"ProjectDashController:index"=>"design",
		"ProjectDashController:access"=>"design",
		"ProjectDashController:save"=>"design",
		"ProjectDashController:copy"=>"design",
		"ProjectDashController:delete"=>"design",
		"ProjectDashController:reorder"=>"design",
		"ProjectDashController:shorturl"=>"design",
		"ProjectDashController:remove_shorturl"=>"design",
		"ProjectDashController:reset_cache"=>"design",
		"ProjectDashController:request_public_enable"=>"design",
		"ProjectDashController:public_enable"=>"design",
		// Alerts & Notifications
        "AlertsController:setup"=>"design",
        "AlertsController:getEdocName"=>"design",
        "AlertsController:saveAlert"=>"design",
        "AlertsController:downloadAttachment"=>"design",
        "AlertsController:saveAttachment"=>"design",
        "AlertsController:deleteAttachment"=>"design",
        "AlertsController:copyAlert"=>"design",
        "AlertsController:deleteAlert"=>"design",
        "AlertsController:deleteAlertPermanent"=>"design",
        "AlertsController:displayRepeatingFormTextboxQueue"=>"design",
        "AlertsController:viewQueuedRecords"=>"design",
        "AlertsController:deleteQueuedRecord"=>"design",
        "AlertsController:previewAlertMessage"=>"design",
        "AlertsController:previewAlertMessageByRecordDialog"=>"design",
        "AlertsController:previewAlertMessageByRecord"=>"design",
        "AlertsController:addQueuedRecord"=>"design",
        "AlertsController:migrateEmailAlerts"=>"design",
		"AlertsController:reevalAlerts"=>"design",
        "AlertsController:moveAlert"=>"design",
        "AlertsController:downloadAlerts"=>"design",
        "AlertsController:uploadAlerts"=>"design",
        "AlertsController:downloadLogs"=>"design",
        "AlertsController:uploadDownloadHelp"=>"design",
		// Dynamic Data Pull (DDP)
		"DynamicDataPull/setup.php"=>"realtime_webservice_mapping",
		"DynamicDataPull/fetch.php"=>"realtime_webservice_adjudicate",
		"DynamicDataPull/save.php"=>"realtime_webservice_adjudicate",
		"DynamicDataPull/exclude.php"=>"realtime_webservice_adjudicate",
		"DynamicDataPull/purge_cache.php"=>"design",
		// DataMart
		"DataMartController:revisions"=>"",
		"DataMartController:getUser"=>"",
		"DataMartController:getSettings"=>"",
		"DataMartController:addRevision"=>"",
		"DataMartController:runRevision"=>"",
		"DataMartController:getRevisionProgress"=>"",
		"DataMartController:exportRevision"=>"",
		"DataMartController:importRevision"=>"",
        "DataMartController:sourceFields"=>"",
		"DataMartController:approveRevision"=>"design",
		"DataMartController:deleteRevision"=>"design",
		"DataMartController:index"=>"",
		"DataMartController:searchMrns"=>"",
		// FHIR Mapping Helper
		"FhirMappingHelperController:index"=>"",
		"FhirMappingHelperController:getSettings"=>"",
		"FhirMappingHelperController:getResource"=>"",
		"FhirMappingHelperController:getResources"=>"",
		"FhirMappingHelperController:getFhirRequest"=>"",
		// Mobile App page
		"MobileApp/index.php"=>"mobile_app",
		// Break the glass
		"GlassBreakerController:index"=>"",
		"GlassBreakerController:initialize"=>"",
		"GlassBreakerController:check"=>"",
		"GlassBreakerController:accept"=>"",
		"GlassBreakerController:cancel"=>"",
		"GlassBreakerController:getProtectedMrnList"=>"",
		"GlassBreakerController:clearProtectedMrnList"=>"",
		// Queue
		"QueueController:getMessages"=>"",
		// Clinical Data Interoperability Services
		"CdisController:showLogsPage"=>"",
		"CdisController:getCdpAutoAdjudicationLogs"=>"",
		// Clinical Data Pull Mapping
		"CdpController:getSettings"=>"",
		"CdpController:setSettings"=>"design",
		"CdpController:importMapping"=>"design",
		"CdpController:exportMapping"=>"design",
		"CdpController:download"=>"design",
		"CdpController:getPreviewData"=>"design",
		"CdpController:getDdpRecordsDataStats"=>"design",
		"CdpController:adjudicateCachedRecords"=>"design",
		"CdpController:adjudicateCachedRecord"=>"design",

	);

	// Double Data Entry (only): DDE Person will have no rights to certain pages that display data.
	// List the restricted pages in an array
	private $pagesRestrictedDDE = array(
		"Calendar/index.php", "DataExport/data_export_tool.php", "DataImportController:index",
		"DataComparisonController:index", "Logging/index.php", "FileRepository/index.php", "DataQuality/field_comment_log.php",
		"Locking/esign_locking_management.php", "Graphical/index.php", "DataQuality/index.php", "Reports/report.php"
	);

	// Constructor
	public function __construct($applyProjectPrivileges=false)
	{
		extract($GLOBALS);
		global $lang, $user_rights, $double_data_entry;
		// Automatically apply project-level user privileges
		if (!$applyProjectPrivileges) return;
		// Obtain the user's project-level user privileges
		$userAuthenticated = $this->checkPrivileges();
		if (!$userAuthenticated || ($userAuthenticated === '2' && !isset($_SESSION['impersonate_user'][PROJECT_ID]['impersonator'])))
		{
			if (!$GLOBALS['no_access']) {
				include APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
				renderPageTitle();
			}
			$noAccessMsg = ($userAuthenticated === '2') ? $lang['config_04'] . "<br><br>" : "";
			$noAccessMsg2 = ($userAuthenticated === '2') ? $lang['config_06'] : "<a href=\"mailto:{$GLOBALS['project_contact_email']}\">{$GLOBALS['project_contact_name']}</a> {$lang['config_03']}";
			print  "<div class='red'>
						<img src='" . APP_PATH_IMAGES . "exclamation.png'> <b>{$lang['global_05']}</b><br><br>$noAccessMsg {$lang['config_02']} $noAccessMsg2
					</div>";
			// Display special message if user has no access AND is a DDE user
			if ($double_data_entry && isset($user_rights) && $user_rights['double_data'] != 0) {
				print RCView::div(array('class'=>'yellow', 'style'=>'margin-top:20px;'), RCView::b($lang['global_02'].$lang['colon'])." ".$lang['rights_219']);
			}
			// Display link to My Projects page
			if ($GLOBALS['no_access']) {
				print RCView::div(array('style'=>'margin-top:20px;'), RCView::a(array('href'=>APP_PATH_WEBROOT_FULL.'index.php?action=myprojects'), $lang['bottom_69']) );
			} else {
				// Show left-hand menu unless it's been flagged to hide everything to prevent user from doing anything else
				include APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
			}
			exit;
		}
	}
	
	/**
	 * Set SUPER USER privileges in $user_rights array. Returns true always.
	 */
	private function getSuperUserPrivileges()
	{
		global $data_resolution_enabled, $Proj, $DDP, $mobile_app_enabled, $api_enabled;
		// Manually set $user_rights array
		$user_rights = array('username'=>(defined("USERID") ? USERID : ""), 'expiration'=>'', 'group_id'=>'', 'role_id'=>'',
							 'lock_record'=>2, 'lock_record_multiform'=>1, 'lock_record_customize'=>1,
							 'data_export_tool'=>1, 'data_import_tool'=>1, 'data_comparison_tool'=>1, 'data_logging'=>1, 'file_repository'=>1,
							 'user_rights'=>1, 'data_access_groups'=>1, 'design'=>1, 'calendar'=>1, 'reports'=>1, 'graphical'=>1,
							 'double_data'=>0, 'record_create'=>1, 'record_rename'=>1, 'record_delete'=>1, 'api_token'=>'', 'dts'=>1,
							 'participants'=>1, 'data_quality_design'=>1, 'data_quality_execute'=>1,
							 'data_quality_resolution'=>($data_resolution_enabled == '2' ? 3 : 0),
							 'api_export'=>1, 'api_import'=>1, 'mobile_app'=>(($mobile_app_enabled && $api_enabled) ? 1 : 0),
							 'mobile_app_download_data'=>(($mobile_app_enabled && $api_enabled) ? 1 : 0),
							 'random_setup'=>1, 'random_dashboard'=>1, 'random_perform'=>1,
							 'realtime_webservice_mapping'=>(is_object($DDP) && (($DDP->isEnabledInSystem() && $DDP->isEnabledInProject()) || ($DDP->isEnabledInSystemFhir() && $DDP->isEnabledInProjectFhir()))),
							 'realtime_webservice_adjudicate'=>(is_object($DDP) && (($DDP->isEnabledInSystem() && $DDP->isEnabledInProject()) || ($DDP->isEnabledInSystemFhir() && $DDP->isEnabledInProjectFhir()))),
							 'external_module_config'=>array()
							);

		// Set form-level rights
		foreach ($Proj->forms as $this_form=>$attr) {
			// If this form is used as a survey, give super user level 3 (survey response editing), else give level 1 for form-level edit rights
			$user_rights['forms'][$this_form] = (isset($attr['survey_id'])) ? '3' : '1';
		}

		// Put user_rights into global scope
		$GLOBALS['user_rights'] = $user_rights;

		// Return as true
		return true;
	}


	public static function addPrivileges($project_id, $rights)
	{
		$project_id = (int)$project_id;

		$cols_blank_defaults = array('expiration', 'data_entry');
		$keys = self::getApiUserPrivilegesAttr();

		$cols = $vals = array();
		foreach($keys as $k=>$v)
		{
			$cols[] = $backEndKey = is_numeric($k) ? $v : $k;
			$vals[] = ($rights[$v] == '' && !in_array($backEndKey, $cols_blank_defaults)) ? ($backEndKey == 'group_id' ? 'null' : 0) : checkNull($rights[$v]);
		}

		// If forms are missing for new user, then set all to 0
		if (!isset($rights['forms'])) {
			$formsRights = "";
			$Proj = new Project($project_id);
			foreach (array_keys($Proj->forms) as $this_form) {
				$formsRights .= "[$this_form,0]";
			}
			$vals[array_search('data_entry', $cols)] = checkNull($formsRights);
		}

		$sql = "INSERT INTO redcap_user_rights (project_id,	".implode(", ", $cols).") VALUES
				($project_id, ".implode(", ", $vals).")";
		$q = db_query($sql);

		return ($q && $q !== false);
	}


	public static function updatePrivileges($project_id, $rights)
	{
		$project_id = (int)$project_id;

		$cols_blank_defaults = array('expiration', 'data_entry');
		$keys = self::getApiUserPrivilegesAttr();
		$vals = array();
		foreach($keys as $k=>$v)
		{
			// If value was not sent, then do not update it
			if (!isset($rights[$v]) && $v != "data_access_group") continue;
			// Set update value
			$backEndKey = is_numeric($k) ? $v : $k;
			$vals[] = "$backEndKey = " . (($rights[$v] == '' && !in_array($backEndKey, $cols_blank_defaults) && $v != "data_access_group") ? 0 : checkNull($rights[$v]));
		}

		$sql = "UPDATE redcap_user_rights SET ".implode(", ", $vals)."
				WHERE project_id = $project_id AND username = '".db_escape($rights['username'])."'";
		$q = db_query($sql);
		return ($q && $q !== false);
	}


	/**
	 * Return array of attributes to be imported/export for users via API User Import/Export
	 */
	public static function getApiUserPrivilegesAttr($returnEmailAndName=false)
	{
		$attrInfo = array('email', 'firstname', 'lastname');
		$attr = array('username', 'expiration', 'group_id'=>'data_access_group', 'design', 'user_rights', 'data_access_groups',
				'data_export_tool'=>'data_export', 'reports', 'graphical'=>'stats_and_charts',
				'participants'=>'manage_survey_participants', 'calendar', 'data_import_tool',
				'data_comparison_tool', 'data_logging'=>'logging', 'file_repository',
				'data_quality_design'=>'data_quality_create', 'data_quality_execute',
				'api_export', 'api_import', 'mobile_app', 'mobile_app_download_data',
				'record_create', 'record_rename', 'record_delete',
				'lock_record_customize'=>'lock_records_customization',
				'lock_record'=>'lock_records', 'lock_record_multiform'=>'lock_records_all_forms',
				'data_entry'=>'forms');
		if ($returnEmailAndName) {
			unset($attr[0]);
			$attr = array_merge(array('username'), $attrInfo, $attr);
		}
		return $attr;
	}

	/**
	 * GET USER PRIVILEGES
	 *
	 */
	public static function getPrivileges($project_id=null, $userid=null)
	{
		// Put rights in array
		$user_rights = array();
		// Set subquery
		$sqlsub = "";
		if ($project_id != null || $userid != null) {
			$sqlsub = "where";
			if ($project_id != null) {
				$sqlsub .= " r.project_id = $project_id";
			}
			if ($project_id != null && $userid != null) {
				$sqlsub .= " and";
			}
			if ($userid != null) {
				$sqlsub .= " r.username = '" . db_escape($userid) . "'";
			}
		}
		// Check if a user for this project
		$sql = "select r.*, u.* from redcap_user_rights r left outer join redcap_user_roles u
				on r.role_id = u.role_id $sqlsub order by r.project_id, r.username";
		$q = db_query($sql);
		// Set $user_rights array, which will carry all rights for current user.
		while ($row = db_fetch_array($q, MYSQLI_NUM))
		{
			// Get current project_id and user to use as array keys
			$this_project_id = $row[0];
			$this_user = strtolower($row[1]); // Deal with case-sentivity issues
			// Loop through fields using numerical indexes so we don't overwrite user values with NULLs if not in a role.
			foreach ($row as $this_field_num=>$this_value) {
				// Get name of field
				$this_field = db_field_name($q, $this_field_num);
				// If we hit the project_id again (from user_roles table) and it is null, then stop here so we don't overwrite
				// users values with NULLs since they are not in a role.
				if (isset($user_rights[$this_project_id][$this_user][$this_field]) && $user_rights[$this_project_id][$this_user][$this_field] != null && $this_value == null) continue;
				// Make sure username is lower case, for consistency
				if ($this_field == 'username') $this_value = strtolower($this_value);
				// External Modules config permissions: Decode the JSON
				if ($this_field == 'external_module_config') {
					$this_value = json_decode($this_value, true);
					if (!is_array($this_value)) $this_value = array();
				}
				// Add value to array
				$user_rights[$this_project_id][$this_user][$this_field] = $this_value;
			}
		}
		// Return array
		return $user_rights;
	}

	/**
	 * CHECK USER PRIVILEGES IN A GIVEN PROJECT
	 * Checks if user has rights to see this page
	 */
	public function checkPrivileges()
	{
		global $data_resolution_enabled, $data_locked, $status;

		// Initialize $user_rights as global variable as array
		global $user_rights;
		$user_rights = array();
		$this_project_id = PROJECT_ID;

		// If a SUPER USER, then manually set rights to full/max for all things
		if ((!defined("SUPER_USER") || SUPER_USER) && !self::isImpersonatingUser()) {
			return $this->getSuperUserPrivileges();
		} elseif ((!defined("SUPER_USER") || SUPER_USER)&& self::isImpersonatingUser()) {
			$this_user = self::getUsernameImpersonating();
		} else {
			$this_user = USERID;
		}

		## NORMAL USERS
		// Check if a user for this project
		$user_rights_proj_user = $this->getPrivileges($this_project_id, $this_user);
		$user_rights = (isset($user_rights_proj_user[$this_project_id]) && isset($user_rights_proj_user[$this_project_id][strtolower($this_user)])) ? $user_rights_proj_user[$this_project_id][strtolower($this_user)] : [];
		unset($user_rights_proj_user);
		// Kick out if not a user and not a Super User
		if (count($user_rights) < 1) {
			//Still show menu if a user from a child/linked project
			$GLOBALS['no_access'] = 1;
			return false;
		}

		// Check user's expiration date (if exists)
		if ($user_rights['expiration'] != "" && $user_rights['expiration'] <= TODAY)
		{
			$GLOBALS['no_access'] = 1;
			// Instead of returning 'false', return '2' specifically so we can note to user that the password has expired
			return '2';
		}

		// Data resolution workflow: disable rights if module is disabled
		if ($data_resolution_enabled != '2') $user_rights['data_quality_resolution'] = '0';

		// SET FORM-LEVEL RIGHTS: Loop through data entry listings and add each form as a new sub-array element
		$this->setFormLevelPrivileges();

		// If project has Data Locked while in Analysis/Cleanup status, then
		if ($status == '2') {
			// Whether data is locked or not, prevent from creating new records (not allowed for this status)
			$user_rights['record_create'] = '0';
			// Further limit user rights if Data Locked is enabled
			if ($data_locked == '1') {
				// Disable the user's ability to create, rename, or delete records
				$user_rights['record_rename'] = '0';
				$user_rights['record_delete'] = '0';
				// If user has API access, then ensure that api_import is disabled
				$user_rights['api_import'] = '0';
				// Prevent ability to import data via Data Import Tool
				$user_rights['data_import_tool'] = '0';
				// If project has Data Locked, then remove edit form-level privileges and set to read-only
				foreach ($user_rights['forms'] as $this_form=>$this_form_rights) {
					$user_rights['forms'][$this_form] = ($this_form_rights > 0 ? 2 : $this_form_rights);
				}
				// Disable locking privileges
				$user_rights['lock_record'] = '0';
				$user_rights['lock_record_multiform'] = '0';
			}
		}

		// Remove array elements no longer needed
		unset($user_rights['data_entry'], $user_rights['project_id']);

		// Chec page-level privileges: Return true if has access to page, else false.
		return $this->checkPageLevelPrivileges();
	}


	/**
	 * OBTAIN USER RIGHTS INFORMATION FOR ALL USERS IN THIS PROJECT
	 * Also includes users' first and last name and email address
	 * Return array with username as key (sorted by username)
	 */
	public static function getRightsAllUsers($enableDagLimiting=true)
	{
		global $Proj, $lang, $user_rights;
		if (!defined("PROJECT_ID")) return array();
		// Pull all user/role info for this project
		$users = array();
		$group_sql = ($enableDagLimiting && $user_rights['group_id'] != "") ? "and u.group_id = '".$user_rights['group_id']."'" : "";
		$sql = "select u.*, i.user_firstname, i.user_lastname, trim(concat(i.user_firstname, ' ', i.user_lastname)) as user_fullname
				from redcap_user_rights u left outer join redcap_user_information i on i.username = u.username
				where u.project_id = " . PROJECT_ID . " $group_sql order by u.username";
		$q = db_query($sql);
		while ($row = db_fetch_assoc($q)) {
			// Set username so we can set as key and remove from array values
			$username = $row['username'];
			unset($row['username']);
			// Add to array
			$users[$username] = $row;
		}
		// Return array
		return $users;
	}


	/**
	 * OBTAIN ALL USER ROLES INFORMATION FOR THIS PROJECT (INCLUDES SYSTEM-LEVEL ROLES)
	 * Return array with role_id as key (sorted with project-level roles first, then system-level roles)
	 */
	public static function getRoles()
	{
		if (!defined("PROJECT_ID")) return array();
		// Pull all user/role info for this project
		$roles = array();
		$sql = "select * from redcap_user_roles where project_id = " . PROJECT_ID . "
				order by project_id desc, role_name";
		$q = db_query($sql);
		while ($row = db_fetch_assoc($q)) {
			// Set role_id so we can set as key and remove from array values
			$role_id = $row['role_id'];
			unset($row['role_id']);
            if ($row['unique_role_name'] == '') {
                $row['unique_role_name'] = self::addUniqueUserRoleName($role_id);
            }
			// Add to array
			$roles[$role_id] = $row;
		}
		// Return array
		return $roles;
	}


	/**
	 * SET FORM-LEVEL PRIVILEGES
	 * Loop through data entry listings and add each form as a new sub-array element
	 * Does not return anything
	 */
	public function setFormLevelPrivileges()
	{
		global $user_rights;

		// User is NOT in a system-level role (i.e. user is either not in a role OR is in project-level role)
		$allForms = explode("][", substr(trim($user_rights['data_entry']), 1, -1));
		foreach ($allForms as $forminfo)
		{
			if (strpos($forminfo, ",")) {
				list($this_form, $this_form_rights) = explode(",", $forminfo, 2);
			} else {
				$this_form = $forminfo;
				$this_form_rights = 0;
			}
			$user_rights['forms'][$this_form] = $this_form_rights;
		}

		// AUTO FIX FORM-LEVEL RIGHTS: Double check to make sure that the form-level rights are all there
		$this->autoFixFormLevelPrivileges(PROJECT_ID);
	}


	/**
	 * AUTO FIX FORM-LEVEL PRIVILEGES (IF NEEDED)
	 * Double check to make sure that the form-level rights are all there (old bug would sometimes cause
	 * them to go missing, thus disrupting things).
	 * Does not return anything
	 */
	private function autoFixFormLevelPrivileges()
	{
		global $Proj, $user_rights;
		// Loop through all forms and check user rights for each
		foreach (array_keys($Proj->forms) as $this_form)
		{
			if (!isset($user_rights['forms'][$this_form])) {
				// Add to user_rights table (give user Full Edit rights to the form as default, if missing)
				if ($user_rights['role_id'] == '') {
					$sql = "update redcap_user_rights set data_entry = concat(data_entry,'[$this_form,1]')
							where project_id = ".PROJECT_ID." and username = '" . USERID . "'";
				} else {
					$sql = "update redcap_user_roles set data_entry = concat(data_entry,'[$this_form,1]')
							where role_id = ".$user_rights['role_id'];
				}
				$q = db_query($sql);
				if (db_affected_rows() < 1) {
					// Must have a NULL as data_entry value, so fix it
					if ($user_rights['role_id'] == '') {
						$sql = "update redcap_user_rights set data_entry = '[$this_form,1]'
								where project_id = ".PROJECT_ID." and username = '" . USERID . "'";
					} else {
						$sql = "update redcap_user_roles set data_entry = '[$this_form,1]'
								where role_id = ".$user_rights['role_id'];
					}
					$q = db_query($sql);
				}
				// Also add to $user_rights array
				$user_rights['forms'][$this_form] = '1';
			}
		}
	}


	/**
	 * CHECK A USER'S PAGE-LEVEL USER PRIVILEGES
	 * Return true if they have access to the current page, else return false if they do not.
	 */
	private function checkPageLevelPrivileges()
	{
		global $user_rights, $double_data_entry, $Proj;

		// Check Data Entry page rights (edit/read-only/none), if we're on that page
		if (defined("PAGE") && PAGE == 'DataEntry/index.php')
		{
			// If 'page' is not a valid form, then redirect to home page
			if (isset($_GET['page']) && !isset($Proj->forms[$_GET['page']])) {
				redirect(APP_PATH_WEBROOT . "index.php?pid=" . PROJECT_ID);
			}
			// If user does not have rights to this form, then return false
			if (!isset($user_rights['forms'][$_GET['page']])) {
				return false;
			}
			// If user has no access to form, kick out; otherwise set as full access or disabled
			if (isset($user_rights['forms'][$_GET['page']])) {
				return ($user_rights['forms'][$_GET['page']] != "0");
			}
		}

		// DDE Person will have no rights to certain pages or routes that display data
		if ($double_data_entry && $user_rights['double_data'] != 0 && defined("PAGE") && in_array(PAGE, $this->pagesRestrictedDDE)) {
			return false;
		}

		// Determine if user has rights to current page
		if (defined("PAGE") && isset($this->page_rights[PAGE]) && isset($user_rights[$this->page_rights[PAGE]]))
		{
			// Does user have access to this page (>0)?
			return ($user_rights[$this->page_rights[PAGE]] > 0);
		}

		// If you got here, then you're on a page not dictated by rights in the $user_rights array, so allow access
		return true;
	}


	/**
	 * RENDER COMPREHENSIVE USER RIGHTS/ROLES TABLE
	 * Return true if they have access to the current page, else return false if they do not.
	 */
	public static function renderUserRightsRolesTable()
	{
		global  $user_rights, $lang, $Proj, $double_data_entry, $dts_enabled_global, $dts_enabled, $mobile_app_enabled,
				$api_enabled, $randomization, $enable_plotting, $data_resolution_enabled, $DDP, $scheduling;

		// Check if DAGs exist and retrieve as array
		$dags = $Proj->getGroups();

		// Set image variables
		$imgYes = RCView::img(array('src' => 'tick.png'));
		$imgNo = RCView::img(array('src' => 'cross.png'));
		$imgShield = RCView::img(array('src' => 'tick_shield.png'));

		// Set up array of all possible headers for the table (some columns will be hidden depending on project or system settings)
		$rightsHdrs = array(
			'role_name' => array('hdr' => RCView::span(array('style'=>'font-weight:bold;font-size:13px;'), $lang['rights_148']).RCView::div(array('style'=>'line-height:1.2;padding-top:3px;color:#888;'), $lang['rights_206']), 'enabled' => true, 'width'=>150, 'align'=>'left'),
			'username' => array('hdr' => RCView::span(array('style'=>'font-weight:bold;font-size:13px;'), $lang['global_11'])." ".$lang['rights_150'].RCView::div(array('style'=>'line-height:1.2;padding-top:3px;color:#888;'), $lang['rights_174']), 'enabled' => true, 'width'=>250, 'align'=>'left'),
			'expiration' => array('hdr' => RCView::span(array('style'=>'font-weight:bold;font-size:12px;'), $lang['rights_95']).RCView::div(array('style'=>'line-height:1.2;padding-top:3px;color:#888;'), $lang['rights_209']), 'enabled' => true, 'width'=>80),
			'group_id' => array('hdr' => RCView::span(array('style'=>'font-weight:bold;font-size:12px;'), $lang['global_78']).
				($user_rights['group_id'] != '' ? '' : RCView::div(array('style'=>'line-height:1.2;padding-top:3px;color:#888;'), $lang['rights_210'])),
				'enabled' => !empty($dags), 'width'=>130),
			'design' => array('hdr' => RCView::b($lang['rights_135']), 'enabled' => true, 'width'=>60),
			'user_rights' => array('hdr' => RCView::b($lang['app_05']), 'enabled' => true, 'width'=>40),
			'data_access_groups' => array('hdr' => RCView::b($lang['global_22']), 'enabled' => true),
			'data_export_tool' => array('hdr' => RCView::b($lang['app_03']), 'enabled' => true, 'width'=>75),
			'reports' => array('hdr' => RCView::b($lang['rights_96']), 'enabled' => true),
			'graphical' => array('hdr' => RCView::b($lang['app_13']), 'enabled' => $enable_plotting > 0),
			'participants' => array('hdr' => RCView::b($lang['app_24']), 'enabled' => !empty($Proj->surveys), 'width'=>65),
			'calendar' => array('hdr' => RCView::b($lang['app_08'] . ($scheduling ? " ".$lang['rights_357'] : "")), 'enabled' => true, 'width'=>60),
			'data_import_tool' => array('hdr' => RCView::b($lang['app_01']), 'enabled' => true, 'width'=>60),
			'data_comparison_tool' => array('hdr' => RCView::b($lang['app_02']), 'enabled' => true, 'width'=>70),
			'data_logging' => array('hdr' => RCView::b($lang['app_07']), 'enabled' => true, 'width'=>45),
			'file_repository' => array('hdr' => RCView::b($lang['app_04']), 'enabled' => true, 'width'=>60),
			'double_data' => array('hdr' => RCView::b($lang['rights_50']), 'enabled' => $double_data_entry),
			'lock_record_customize' => array('hdr' => RCView::b($lang['app_11']), 'enabled' => true, 'width'=>90),
			'lock_record' => array('hdr' => RCView::b($lang['rights_97']), 'enabled' => true, 'width'=>70),
			'randomization' => array('hdr' => RCView::b($lang['app_21']), 'enabled' => $randomization, 'width'=>90),
			'data_quality_design' => array('hdr' => RCView::b($lang['dataqueries_38']), 'enabled' => true),
			'data_quality_execute' => array('hdr' => RCView::b($lang['dataqueries_39']), 'enabled' => true),
			'data_quality_resolution' => array('hdr' => RCView::b($lang['dataqueries_137']), 'enabled' => ($data_resolution_enabled == '2')),
			'api' => array('hdr' => RCView::b($lang['setup_77']), 'enabled' => $api_enabled, 'width'=>40),
			'mobile_app' => array('hdr' => RCView::b($lang['global_118']), 'enabled' => ($mobile_app_enabled && $api_enabled), 'width'=>50),
			'realtime_webservice_mapping' => array('hdr' => RCView::b(($DDP->isEnabledInSystemFhir() ? $lang['ws_210'] : $lang['ws_51'])." {$DDP->getSourceSystemName()}<div style='font-weight:normal;'>({$lang['ws_19']})</div>"), 'enabled' => (is_object($DDP) && (($DDP->isEnabledInSystem() && $DDP->isEnabledInProject()) || ($DDP->isEnabledInSystemFhir() && $DDP->isEnabledInProjectFhir())))),
			'realtime_webservice_adjudicate' => array('hdr' => RCView::b(($DDP->isEnabledInSystemFhir() ? $lang['ws_210'] : $lang['ws_51'])." {$DDP->getSourceSystemName()}<div style='font-weight:normal;'>({$lang['ws_20']})</div>"), 'enabled' => (is_object($DDP) && (($DDP->isEnabledInSystem() && $DDP->isEnabledInProject()) || ($DDP->isEnabledInSystemFhir() && $DDP->isEnabledInProjectFhir())))),
			'dts' => array('hdr' => RCView::b($lang['rights_132']), 'enabled' => $dts_enabled_global && $dts_enabled),
			'record_create' => array('hdr' => RCView::b($lang['rights_99']), 'enabled' => true, 'width'=>45),
			'record_rename' => array('hdr' => RCView::b($lang['rights_100']), 'enabled' => true, 'width'=>45),
			'record_delete' => array('hdr' => RCView::b($lang['rights_101']), 'enabled' => true, 'width'=>45),
            'role_id' => array('hdr' => RCView::span(array('style'=>'font-weight:bold;font-size:13px;'), $lang['rights_403']).RCView::div(array('style'=>'line-height:1.2;padding-top:3px;color:#888;'), $lang['define_events_66']), 'enabled' => true, 'width'=>80, 'align'=>'center', 'sort_type'=>'int'),
            'unique_role_name' => array('hdr' => RCView::span(array('style'=>'font-weight:bold;font-size:13px;'), $lang['rights_404']).RCView::div(array('style'=>'line-height:1.2;padding-top:3px;color:#888;'), $lang['define_events_66']), 'enabled' => true, 'width'=>95, 'align'=>'center')
		);

		// Get all user rights as array
		$rightsAllUsers = self::getRightsAllUsers();

		// Get all suspended users in project (so we can note which are currently suspended)
		$suspendedUsers = User::getSuspendedUsers();

		// Get all user roles as array
		$roles = self::getRoles();

		// Loop through $roles and add a sub-array of users to each role that are assigned to it
		foreach ($rightsAllUsers as $this_username=>$attr) {
			// If has role_id value, then add username to that role in $roles
			if (is_numeric($attr['role_id'])) {
				$roles[$attr['role_id']]['role_users_assigned'][] = $this_username;
			}
		}
		//print_array($rightsAllUsers);
		//print_array($roles);

		// Set default column width in table
		$defaultColWidth = 70;

		// Set table width (loop through headers and calculate)
		$tableColPadding = 13;
		$tableWidth = 0;

		// Set up the table headers
		$hdrs = array();
		foreach ($rightsHdrs as $this_colname=>$attr) {
			// If this column is not enabled, skip it
			if (!$attr['enabled']) continue;
			// Determine col width
			$this_width = (isset($attr['width'])) ? $attr['width'] : $defaultColWidth;
			// Increment the table width
			$tableWidth += ($this_width + $tableColPadding);
			// Determine col alignment
			$this_align = (isset($attr['align'])) ? $attr['align'] : 'center';
            $this_sort_type = (isset($attr['sort_type'])) ? $attr['sort_type'] : 'string';
			// Add to $hdrs array to be displayed
			$hdrs[] = array($this_width, RCView::span(array('class'=>'wrap','style'=>'line-height:10px;'), $attr['hdr']), $this_align, $this_sort_type);
		}

		## ADD TABLE ROWS
		// Add rows of users/roles (start with users not in a role, then go role by role listing users in each role)
		$rows = array();
		$rowkey = 0;
		foreach ($rightsAllUsers as $this_username=>$row) {
			// If has role_id value, then skip. We'll handle users in roles later.
			if (is_numeric($row['role_id'])) continue;
			// Add to $rows array
			$rows[$rowkey] = array();
			// Loop through each column
			foreach ($rightsHdrs as $rightsKey => $r)
			{
				// If this column is not enabled, skip it
				if (!$r['enabled']) continue;
				// Initialize vars
				$cellContent = '';
				// Output column's content (depending on which column we're on)
				if ($rightsKey == 'username') {
					// Set icon if has API token
					$apiIcon = ($row['api_token'] == '' ? '' :
							RCView::span(array('class'=>'nowrap', 'style'=>'color:#A86700;font-size:11px;margin-left:8px;'),
								RCView::img(array('src'=>'coin.png', 'style'=>'vertical-align:middle;')) .
								RCView::span(array('style'=>'vertical-align:middle;'),
									$lang['control_center_333']
								)
							)
						);
					// Set text if user's account is suspended
					$suspendedText = (in_array(strtolower($this_username), $suspendedUsers))
									? RCView::span(array('class'=>'nowrap', 'style'=>'color:red;font-size:11px;margin-left:8px;'),
										$lang['rights_281']
									  )
									: "";
					$this_username_name = RCView::b(RCView::escape($this_username)) . ($row['user_fullname'] == '' ? '' : " ({$row['user_fullname']})");
					$cellContent = 	RCView::div(array('class'=>'userNameLinkDiv'),
										RCView::a(array('href'=>'javascript:;', 'style'=>'vertical-align:middle;font-size:12px;', 'title'=>$lang['rights_178'],
											'class'=>'userLinkInTable', 'inrole'=>'0', 'userid'=>$this_username), $this_username_name) .
										$suspendedText . $apiIcon
									);
				}
				elseif (in_array($rightsKey, array('role_name', 'role_id', 'unique_role_name'))) {
					$cellContent = RCView::div(array('style'=>'color:#999;'), "&mdash;");
				}
				elseif ($rightsKey == 'expiration') {
					$this_class = ($row['expiration'] == "" ? 'userRightsExpireN'
						: (str_replace("-","",$row['expiration']) < date('Ymd') ? 'userRightsExpired' : 'userRightsExpire'));
					$cellContent = 	RCView::div(array('class'=>'expireLinkDiv'),
										RCView::a(array('href'=>'javascript:;', 'class'=>$this_class, 'title'=>$lang['rights_201'],
											'userid'=>$this_username,
											'expire'=>($row['expiration'] == "" ? "" : DateTimeRC::format_ts_from_ymd($row['expiration']))),
											($row['expiration'] == "" ? $lang['rights_171'] : DateTimeRC::format_ts_from_ymd($row['expiration']))
										)
									);
				}
				elseif ($rightsKey == 'group_id') {
					// Display the DAG of this user
					if ($row['group_id'] == '') {
						$this_link_label = '&mdash;';
						$this_link_style = 'color:#999;';
					} else {
						$this_link_label = $dags[$row['group_id']];
						$this_link_style = 'color:#008000;';
					}
					if ($user_rights['group_id'] == '') {
						$cellContent = 	RCView::div(array('class'=>'dagNameLinkDiv'),
											RCView::a(array('href'=>'javascript:;', 'style'=>$this_link_style, 'title'=>$lang['rights_149'],
												'gid'=>$row['group_id'], 'uid'=>$this_username), $this_link_label)
										);
					} else {
						$cellContent = 	RCView::div(array('class'=>'dagNameLinkDiv', 'style'=>$this_link_style), $this_link_label);
					}
				}
				elseif ($rightsKey == 'realtime_webservice_mapping') {
					$cellContent = ($row[$rightsKey] > 0) ? $imgYes : $imgNo;
				}
				elseif ($rightsKey == 'realtime_webservice_adjudicate') {
					$cellContent = ($row[$rightsKey] > 0) ? $imgYes : $imgNo;
				}
				elseif ($rightsKey == 'data_export_tool') {
					if ($row[$rightsKey] == "0") $cellContent = $imgNo;
					elseif ($row[$rightsKey] == "1") $cellContent = $lang['rights_49'];
					elseif ($row[$rightsKey] == "3") $cellContent = $lang['data_export_tool_182'];
					else $cellContent = $lang['rights_48'];
				}
				elseif ($rightsKey == 'data_quality_resolution') {
					if ($row[$rightsKey] == "0") $cellContent = $imgNo;
					elseif ($row[$rightsKey] == "1") $cellContent = $lang['dataqueries_143'];
					elseif ($row[$rightsKey] == "4") $cellContent = $lang['dataqueries_289'];
					elseif ($row[$rightsKey] == "5") $cellContent = $lang['dataqueries_290'];
					elseif ($row[$rightsKey] == "2") $cellContent = $lang['dataqueries_138'];
					elseif ($row[$rightsKey] == "3") $cellContent = $lang['dataqueries_139'];
				}
				elseif ($rightsKey == 'double_data') {
					$cellContent = ($row[$rightsKey] > 0) ? 'DDE Person #'.$row[$rightsKey] : $lang['rights_51'];
				}
				elseif ($rightsKey == 'lock_record_customize') {
					$cellContent = ($row[$rightsKey] > 0) ? $imgYes : $imgNo;
				}
				elseif ($rightsKey == 'lock_record') {
					$cellContent = ($row[$rightsKey] > 0) ? (($row[$rightsKey] == 1) ? $imgYes : $imgShield) : $imgNo;
				}
				elseif ($rightsKey == 'api') {
					// Set text
					if ($row['api_export'] == 1 && $row['api_import'] == 1)
						$cellContent = $lang['global_71'] . RCView::br() . $lang['global_72'];
					elseif ($row['api_export'] == 1) $cellContent = $lang['global_71'];
					elseif ($row['api_import'] == 1) $cellContent = $lang['global_72'];
					else $cellContent = $imgNo;

				}
				elseif ($rightsKey == 'randomization') {
					if ($row['random_setup'] == 1) $cellContent .= $lang['rights_142'] . RCView::br();
					if ($row['random_dashboard'] == 1) $cellContent .= $lang['rights_143'] . RCView::br();
					if ($row['random_perform'] == 1) $cellContent .= $lang['rights_144'];
					if ($cellContent == '') $cellContent = $imgNo;
				}
				else {
					$cellContent = ($row[$rightsKey] == 1) ? $imgYes : $imgNo;
				}
				// Render table cell for this column
				$rows[$rowkey][] = RCView::div(array('class'=>'wrap'), $cellContent);
			}
			// Increment rowkey
			$rowkey++;
		}
		// Now add roles
		foreach ($roles as $role_id=>$row) {
			// Add to $rows array
			$rows[$rowkey] = array();
			// Loop through each column
			foreach ($rightsHdrs as $rightsKey => $r)
			{
				// If this column is not enabled, skip it
				if (!$r['enabled']) continue;
				// Initialize vars
				$cellContent = '';
				// Output column's content (depending on which column we're on)
				if ($rightsKey == 'username') {
					if (empty($row['role_users_assigned'])) {
						$this_role_userlist = RCView::div(array('style'=>'color:#aaa;font-size:11px;'),
												(($rightsAllUsers[USERID]['group_id'] == '' || SUPER_USER) ? $lang['rights_151'] : $lang['rights_222'])
											  );
					} else {
						$these_username_names = array();
						$i = 0;
						foreach ($row['role_users_assigned'] as $this_user_assigned)
						{
							// Set icon if has API token
							$apiIcon = ($rightsAllUsers[$this_user_assigned]['api_token'] == '' ? '' :
									RCView::span(array('class'=>'nowrap', 'style'=>'color:#A86700;font-size:11px;margin-left:8px;'),
										RCView::img(array('src'=>'coin.png', 'style'=>'vertical-align:middle;')) .
										RCView::span(array('style'=>'vertical-align:middle;'),
											$lang['control_center_333']
										)
									)
								);
							// Set text if user's account is suspended
							$suspendedText = (in_array(strtolower($this_user_assigned), $suspendedUsers))
											? RCView::span(array('class'=>'nowrap', 'style'=>'color:red;font-size:11px;margin-left:8px;'),
												$lang['rights_281']
											  )
											: "";
							$this_username_name = RCView::b(RCView::escape($this_user_assigned)) . ($rightsAllUsers[$this_user_assigned]['user_fullname'] == '' ? '' : " ({$rightsAllUsers[$this_user_assigned]['user_fullname']})");
							$these_username_names[] =
								RCView::div(array('class'=>'userNameLinkDiv', 'style'=>($i==0 ? '' : 'border-top:1px solid #eee;')),
									RCView::a(array('href'=>'javascript:;', 'style'=>'vertical-align:middle;font-size:12px;', 'title'=>$lang['rights_217'],
										'class'=>'userLinkInTable', 'inrole'=>'1', 'userid'=>$this_user_assigned), $this_username_name) .
									$suspendedText . $apiIcon
								);
							$i++;
						}
						$this_role_userlist = implode("", $these_username_names);
					}
					$cellContent = 	RCView::div(array('style'=>'color:#800000;'),
										$this_role_userlist
									);
				}
				elseif ($rightsKey == 'role_name') {
					// Set different color for system-level roles
					$cellContent = RCView::a(array('href'=>'javascript:;', 'style'=>'color:#800000;font-weight:bold;font-size:12px;',
										'title'=>$lang['rights_152'], 'id'=>'rightsTableUserLinkId_' . $role_id),
										RCView::escape($row['role_name'])
									);
				}
				elseif ($rightsKey == 'expiration') {
					$these_rows = array();
					$i = 0;
					if(isset($row['role_users_assigned']))
					{
						foreach ($row['role_users_assigned'] as $this_user_assigned) {
							$this_expiration = $rightsAllUsers[$this_user_assigned]['expiration'];
							$this_class = ($this_expiration == ""
							? 'userRightsExpireN'
							: (str_replace("-","",$this_expiration) < date('Ymd')
								? 'userRightsExpired'
								: 'userRightsExpire'));
							$these_rows[] =
								RCView::div(array('class'=>'expireLinkDiv', 'style'=>($i==0 ? '' : 'border-top:1px solid #eee;')),
									RCView::a(array('href'=>'javascript:;', 'class'=>$this_class, 'title'=>$lang['rights_201'],
									'userid'=>$this_user_assigned,
										'expire'=>($this_expiration == "" ? "" : DateTimeRC::format_ts_from_ymd($this_expiration))),
										($this_expiration == "" ? $lang['rights_171'] : DateTimeRC::format_ts_from_ymd($this_expiration))
									)
								);
							$i++;
						}
					}
					$cellContent = implode("", $these_rows);
				}
				elseif ($rightsKey == 'group_id') {
					// Display the DAGs of all users in this role
					$these_dagnames = array();
					$i = 0;
					if(isset($row['role_users_assigned']))
					{
						foreach ($row['role_users_assigned'] as $this_user_assigned) {
							$this_group_id = $rightsAllUsers[$this_user_assigned]['group_id'];
							if ($rightsAllUsers[$this_user_assigned]['group_id'] == '') {
								$this_link_label = '&mdash;';
								$this_link_style = 'color:#999;';
							} else {
								$this_link_label = $dags[$this_group_id];
								$this_link_style = 'color:#008000;';
							}
							if ($user_rights['group_id'] == '') {
								$these_dagnames[] = RCView::div(array('class'=>'dagNameLinkDiv', 'style'=>($i==0 ? '' : 'border-top:1px solid #eee;')),
														RCView::a(array('href'=>'javascript:;', 'style'=>$this_link_style, 'title'=>$lang['rights_149'],
														'gid'=>$this_group_id, 'uid'=>$this_user_assigned), $this_link_label)
								);
							} else {
								$these_dagnames[] = RCView::div(array('class'=>'dagNameLinkDiv', 'style'=>$this_link_style.($i==0 ? '' : 'border-top:1px solid #eee;')),
								$this_link_label
								);
							}
							$i++;
						}
					}
					$cellContent = implode("", $these_dagnames);
				}
				elseif ($rightsKey == 'realtime_webservice_mapping') {
					$cellContent = ($row[$rightsKey] > 0) ? $imgYes : $imgNo;
				}
				elseif ($rightsKey == 'realtime_webservice_adjudicate') {
					$cellContent = ($row[$rightsKey] > 0) ? $imgYes : $imgNo;
				}
				elseif ($rightsKey == 'data_export_tool') {
					if ($row[$rightsKey] == "0") $cellContent = $imgNo;
					elseif ($row[$rightsKey] == "1") $cellContent = $lang['rights_49'];
					elseif ($row[$rightsKey] == "3") $cellContent = $lang['data_export_tool_182'];
					else $cellContent = $lang['rights_48'];
				}
				elseif ($rightsKey == 'data_quality_resolution') {
					if ($row[$rightsKey] == "0") $cellContent = $imgNo;
					elseif ($row[$rightsKey] == "1") $cellContent = $lang['dataqueries_143'];
					elseif ($row[$rightsKey] == "4") $cellContent = $lang['dataqueries_289'];
					elseif ($row[$rightsKey] == "5") $cellContent = $lang['dataqueries_290'];
					elseif ($row[$rightsKey] == "2") $cellContent = $lang['dataqueries_138'];
					elseif ($row[$rightsKey] == "3") $cellContent = $lang['dataqueries_139'];
				}
				elseif ($rightsKey == 'double_data') {
					$cellContent = ($row[$rightsKey] > 0) ? 'DDE Person #'.$row[$rightsKey] : $lang['rights_51'];
				}
				elseif ($rightsKey == 'lock_record_customize') {
					$cellContent = ($row[$rightsKey] > 0) ? $imgYes : $imgNo;
				}
				elseif ($rightsKey == 'lock_record') {
					$cellContent = ($row[$rightsKey] > 0) ? (($row[$rightsKey] == 1) ? $imgYes : $imgShield) : $imgNo;
				}
				elseif ($rightsKey == 'api') {
					if ($row['api_export'] == 1 && $row['api_import'] == 1)
						$cellContent = $lang['global_71'] . RCView::br() . $lang['global_72'];
					elseif ($row['api_export'] == 1) $cellContent = $lang['global_71'];
					elseif ($row['api_import'] == 1) $cellContent = $lang['global_72'];
					else $cellContent = $imgNo;
				}
				elseif ($rightsKey == 'randomization') {
					if ($row['random_setup'] == 1) $cellContent .= $lang['rights_142'] . RCView::br();
					if ($row['random_dashboard'] == 1) $cellContent .= $lang['rights_143'] . RCView::br();
					if ($row['random_perform'] == 1) $cellContent .= $lang['rights_144'];
					if ($cellContent == '') $cellContent = $imgNo;
				}
                elseif ($rightsKey == 'role_id') {
                    $cellContent = $role_id;
                }
                elseif ($rightsKey == 'unique_role_name') {
                    $cellContent = $row['unique_role_name'];
                }
				else {
					$cellContent = ($row[$rightsKey] == 1) ? $imgYes : $imgNo;
				}
				// Render table cell for this column
				$rows[$rowkey][] = RCView::div(array('class'=>'wrap'), $cellContent);
			}
			// Increment rowkey
			$rowkey++;
		}
		
		// Set disabled attribute for input and button for adding new users if current user is in a DAG
		$addUserDisabled = ($user_rights['group_id'] == '') ? '' : 'disabled';

		// Create "add new user" text box
		$usernameTextboxJsFocus = "$('#new_username_assign').val('".js_escape($lang['rights_160'])."').css('color','#999');
									if ($(this).val() == '".js_escape($lang['rights_154'])."') {
									$(this).val(''); $(this).css('color','#000');
								  }";
		$usernameTextboxJsBlur = "$(this).val( trim($(this).val()) );
								  if ($(this).val() == '') {
									$(this).val('".js_escape($lang['rights_154'])."'); $(this).css('color','#999');
								  }";
		$usernameTextbox = RCView::text(array('id'=>'new_username', $addUserDisabled=>$addUserDisabled, 'class'=>'x-form-text x-form-field', 'maxlength'=>'255',
							'style'=>'margin-left:4px;width:200px;color:#999;font-size:13px;padding-top:0;','value'=>$lang['rights_154'],
							'onkeydown'=>"if(event.keyCode==13) $('#addUserBtn').click();",
							'onfocus'=>$usernameTextboxJsFocus,'onblur'=>$usernameTextboxJsBlur));

		// Create "assign new user" text box
		$usernameTextboxJsFocusAssign = "$('#new_username').val('".js_escape($lang['rights_154'])."').css('color','#999');
										 if ($(this).val() == '".js_escape($lang['rights_160'])."') {
											$(this).val(''); $(this).css('color','#000');
										  }";
		$usernameTextboxJsBlurAssign =  "$(this).val( trim($(this).val()) );
										  if ($(this).val() == '') {
											$(this).val('".js_escape($lang['rights_160'])."'); $(this).css('color','#999');
										  } else {
											userAccountExists($(this).val());
										  }";
		$usernameTextboxAssign = RCView::text(array('id'=>'new_username_assign', $addUserDisabled=>$addUserDisabled, 'class'=>'x-form-text x-form-field', 'maxlength'=>'255',
							'style'=>'margin-left:4px;width:200px;color:#999;font-size:13px;padding-top:0;','value'=>$lang['rights_160'],
							'onkeydown'=>"if(event.keyCode==13) { $('#assignUserBtn').click(); userAccountExists($(this).val()); }",
							'onfocus'=>$usernameTextboxJsFocusAssign,'onblur'=>$usernameTextboxJsBlurAssign));

		// Create "new role name" text box
		$userroleTextboxJsFocus = "if ($(this).val() == '".js_escape($lang['rights_155'])."') {
									$(this).val(''); $(this).css('color','#000');
								  }";
		$userroleTextboxJsBlur = "$(this).val( trim($(this).val()) );
								  if ($(this).val() == '') {
									$(this).val('".js_escape($lang['rights_155'])."'); $(this).css('color','#999');
								  }";
		$userroleTextbox = RCView::text(array('id'=>'new_rolename', 'class'=>'x-form-text x-form-field', 'maxlength'=>'150',
							'style'=>'margin-left:4px;width:200px;color:#999;font-size:13px;padding-top:0;font-weight:normal;','value'=>$lang['rights_155'],
							'onkeydown'=>"if(event.keyCode==13) $('#createRoleBtn').click();",
							'onfocus'=>$userroleTextboxJsFocus,'onblur'=>$userroleTextboxJsBlur));

		$csrf_token = System::getCsrfToken();
        // Import/Export buttons divs
        $buttons = RCView::div(array('style'=>'text-align:right; font-size:12px;font-weight:normal;max-width:900px; '),
            RCView::button(array('onclick'=>"showBtnDropdownList(this,event,'downloadUploadUsersDropdownDiv');", 'class'=>'jqbuttonmed'),
                RCView::img(array('src'=>'xls.gif', 'style'=>'vertical-align:middle;position:relative;top:-1px;')) .
                RCView::span(array('style'=>'vertical-align:middle;'), $lang['rights_376']) .
                RCView::img(array('src'=>'arrow_state_grey_expanded.png', 'style'=>'margin-left:2px;vertical-align:middle;position:relative;top:-1px;'))
            ) .
            RCView::a(array('href'=>'javascript:;','class'=>'help', 'style'=>'margin-left:0px;', 'title'=>$lang['global_58'],'onclick'=>"simpleDialog(null,null,'useDownloadUploadDialog', 900);"), $lang['questionmark']) .
            // Button/drop-down options (initially hidden)
            RCView::div(array('id'=>'downloadUploadUsersDropdownDiv', 'style'=>'text-align:left;display:none;position:absolute;z-index:1000;'),
                RCView::ul(array('id'=>'downloadUploadUsersDropdown'),
                    RCView::li(array(),
                        RCView::a(array('href'=>'javascript:;', 'style'=>'color:#8A5502;', 'onclick'=>"simpleDialog(null,null,'importUsersDialog',500,null,'".js_escape($lang['calendar_popup_01'])."',\"$('#importUserForm').submit();\",'".js_escape($lang['design_530'])."');$('.ui-dialog-buttonpane button:eq(1)',$('#importUsersDialog').parent()).css('font-weight','bold');"),
                            RCView::img(array('src'=>'arrow_up_sm_orange.gif')) .
                            RCView::SP . $lang['rights_377']
                        )
                    ) .
                    RCView::li(array(),
                        RCView::a(array('href'=>'javascript:;', 'style'=>'color:#8A5502;', 'onclick'=>"window.location.href = app_path_webroot+'UserRights/import_export_users.php?action=download&pid='+pid;"),
                            RCView::img(array('src'=>'arrow_down_sm_orange.gif')) .
                            RCView::SP . $lang['rights_378']
                        )
                    )
                )
            )
        );

        $notify_email_html = "<img src='".APP_PATH_IMAGES."email.png'>&nbsp;&nbsp;{$lang['rights_112']}
									&nbsp;<input type='checkbox' name='notify_email' value='1' checked>";
        // Hidden import dialog divs
        $hiddenImportDialog = RCView::div(array('id'=>'importUsersDialog', 'class'=>'simpleDialog', 'title'=>$lang['rights_377']),
            RCView::div(array(), $lang['rights_379']) .
            RCView::div(array('style'=>'margin-top:15px;margin-bottom:5px;font-weight:bold;'), $lang['rights_380']) .
            RCView::form(array('id'=>'importUserForm', 'enctype'=>'multipart/form-data', 'method'=>'post', 'action'=>APP_PATH_WEBROOT . 'UserRights/import_export_users.php?pid=' . PROJECT_ID),
                RCView::input(array('type'=>'hidden', 'name'=>'redcap_csrf_token', 'value'=>$csrf_token)) .
                RCView::input(array('type'=>'file', 'name'=>'file'))
            )
        );

        $hiddenImportDialog .= RCView::div(array('id' => 'importUsersDialog2', 'class' => 'simpleDialog', 'title' => $lang['rights_377'] . " - " . $lang['design_654']),
            RCView::div(array(), $lang['api_125']) .
            RCView::form(array('id' => 'importUsersForm2', 'enctype' => 'multipart/form-data', 'method' => 'post', 'action' => APP_PATH_WEBROOT . 'UserRights/import_export_users.php?pid=' . PROJECT_ID),
                RCView::input(array('type' => 'hidden', 'name' => 'redcap_csrf_token', 'value' => $csrf_token)) .
                RCView::div(array('id' => 'notifyUsers', 'style' => 'display: none; margin:15px 0;'), $notify_email_html) .
                RCView::textarea(array('name' => 'csv_content', 'style' => 'display:none;'), (isset($_SESSION['csv_content']) ? htmlspecialchars($_SESSION['csv_content'], ENT_QUOTES) : ""))
            ) .
            RCView::div(array('id' => 'user_preview', 'style' => 'margin:15px 0'), '')
        );
		// Set html before the table
		$html = RCView::div(array('id'=>'addUsersRolesDiv', 'style'=>'margin:20px 0;font-size:12px;font-weight:normal;padding:10px;border:1px solid #ccc;background-color:#eee;max-width:630px;'),
                RCView::div(array('style'=>'color:#444;'),
                        $buttons
                    ) .
                    // Add new user with custom rights
					RCView::div(array('style'=>($user_rights['group_id'] == '' ? 'color:#444;' : 'color:#aaa;').'padding-top:10px;'),
						//If user is in DAG, only show info from that DAG and give note of that
						($user_rights['group_id'] == "" ? '' : 
							RCView::div(array('style'=>'color:#C00000;margin-bottom:10px;'), "{$lang['global_02']}{$lang['colon']} {$lang['rights_92']}")
						) .
						RCView::span(array('style'=>($user_rights['group_id'] == '' ? 'color:#000;' : 'color:#aaa;').'font-weight:bold;font-size:13px;margin-right:5px;'), $lang['rights_168']) .
						" " .$lang['rights_162']
					) .
					RCView::div(array('style'=>'margin:8px 0 0 29px;'),
						RCView::img(array('src'=>'user_add2.png', 'class'=>($user_rights['group_id'] == '' ? '' : 'opacity35'))) .
						$usernameTextbox .
						// Add User button
						RCView::button(array('id'=>'addUserBtn', $addUserDisabled=>$addUserDisabled, 'class'=>'jqbuttonmed'), $lang['rights_165'])
					) .
					// - OR -
					RCView::div(array('style'=>'margin:2px 0 1px 60px;color:#999;'),
						"&#8212; {$lang['global_46']} &#8212;"
					) .
					// Add new user - assign to role
					RCView::div(array('style'=>'margin:0 0 0 10px;'),
						RCView::img(array('src'=>'user_add2.png', 'class'=>($user_rights['group_id'] == '' ? '' : 'opacity35'))) .
						RCView::img(array('src'=>'vcard.png', 'class'=>($user_rights['group_id'] == '' ? '' : 'opacity35'))) .
						$usernameTextboxAssign .
						// Assign User button
						RCView::button(array('id'=>'assignUserBtn', $addUserDisabled=>$addUserDisabled, 'class'=>'jqbuttonmed', 'style'=>'margin-top:2px;'),
							RCView::span(array('style'=>'vertical-align:middle;'), $lang['rights_156']) .
							RCView::img(array('src'=>'arrow_state_grey_expanded.png', 'style'=>'margin-left:5px;vertical-align:middle;position:relative;top:-1px;'))
						)
					) .
					// Create new user role
					RCView::div(array('style'=>'margin:20px 0 0;color:#444;'),
						RCView::span(array('style'=>'font-weight:bold;font-size:13px;color:#000;margin-right:5px;'), $lang['rights_170']) .
						" " .$lang['rights_169']
					) .
					RCView::div(array('style'=>'margin:8px 0 0 27px;font-weight:bold;color:#2C5178;'),
						RCView::img(array('src'=>'vcard_add.png', 'style'=>'')) .
						$userroleTextbox .
						RCView::button(array('id'=>'createRoleBtn', 'class'=>'jqbuttonmed'), $lang['rights_158'])
					) .
					RCView::div(array('style'=>'margin:2px 0 0 52px;font-size:11px;color:#888;'),
						$lang['rights_218']
					)
				);
        $html .= $hiddenImportDialog;

        // Add popup for help - Upload or download Users CSV
        $br = RCView::br();
        $helpText = RCView::div(array('style'=>'font-weight:bold;color: #A00000;font-size:15px;'), $lang['rights_378']) .
            RCView::div(array('style'=>'margin-top:5px;'), $lang['rights_390']) .
            RCView::div(array('class'=>'attributes-list'), implode(", ", UserRights::getApiUserPrivilegesAttr())) .
            RCView::b($lang['api_docs_227']) . $br . $lang['api_docs_177'] . $br . $lang['api_docs_178'] . $br . $lang['api_docs_229'] .
            RCView::div(array('style'=>'margin-top:35px;font-weight:bold;color: #A00000;font-size:15px;'), $lang['rights_377']) .
            RCView::div(array('style'=>'margin-top:5px;'), $lang['rights_379']. " " . $lang['rights_391']) .
            RCView::div(array('style'=>'margin-top:5px;'), $lang['rights_384']) ;
        $html .= RCView::simpleDialog($helpText, $lang['rights_376'], 'useDownloadUploadDialog');

		// Create SELECT BOX OF USER ROLES to choose from
        $all_roles = array();
        foreach ($roles as $role_id=>$attr) {
            $all_roles[$role_id] = $attr['role_name'];
        }

        $roles_options = (!empty($all_roles)) ? RCView::select(array('id'=>'user_role', 'class'=>'x-form-text x-form-field', 'style'=>'margin:0 10px 0 6px;'),
                                            (array(''=>"{$lang['rights_399']}") + $all_roles), '')
                                            : "";

        $groups = $Proj->getGroups();
		$dags_options = (!empty($groups)) ? RCView::select(array('id'=>'user_dag', 'class'=>'x-form-text x-form-field', 'style'=>'margin:0 10px 0 6px;'),
                                        (array(''=>"[{$lang['data_access_groups_ajax_16']}]") + $groups), '')
                                          : "";
		$html .= RCView::div(array('id'=>'assignUserDropdownDiv', 'style'=>'display:none;position:absolute;z-index:22;'),
					RCView::div(array('id'=>'notify_email_role_option', 'style'=>'color:#555;font-size:11px;padding:3px;border:1px solid #aaa;border-bottom:0;background-color:#eee;', 'ignore'=>'1'),
						"<img src='".APP_PATH_IMAGES."mail_small2.png' style='vertical-align:middle;position:relative;top:-2px;'> {$lang['rights_315']}
						&nbsp;<input type='checkbox' id='notify_email_role' name='notify_email_role' checked>"
					) .
                    (($dags_options != '') ? RCView::div(array('id'=>'dag_option', 'style'=>'display:none;color:#555;font-size:11px;padding:3px;border:1px solid #aaa;border-bottom:0;background-color:#eee;', 'ignore'=>'1', 'title'=>$lang['rights_397']),
                        "<i class='fas fa-user-tag mr-1'></i> {$lang['rights_398']}
						&nbsp;".$dags_options
                    ) : '') .
                    (($roles_options != '') ? RCView::div(array('id'=>'roles_option', 'style'=>'color:#555;font-size:11px;padding:3px;border:1px solid #aaa;border-bottom:0;background-color:#eee;', 'ignore'=>'1'),
                        "<i class='fas fa-user-plus mr-1'></i> {$lang['data_access_groups_ajax_33']}:
						&nbsp;".$roles_options
                    ) : '').
                    RCView::div(array('style'=>'padding:3px; border:1px solid #aaa;background-color:#eee; text-align:right;'),
                        // Assign DAG/Role
                        RCView::button(array('id'=>'assignDagRoleBtn', 'class'=>'jqbuttonmed'), $lang['rights_181']) .
                        RCView::a(array('id'=>'tooltipRoleCancel', 'href'=>'javascript:;', 'style'=>'margin-left:2px;color:#333;font-size:11px;text-decoration:underline;', 'onclick'=>"$('#userClickDagName').hide();"), $lang['global_53'])
                    )
				);


		// TOOLTIP div when CLICK USERNAME IN TABLE
		$html .= RCView::div(array('id'=>'userClickTooltip', 'class'=>'tooltip4left','style'=>'position:absolute;padding-left:30px;'),
					RCView::div(array('style'=>'padding-bottom:5px;font-weight:bold;font-size:13px;'), $lang['rights_172']) .
					// Set custom rights button
					RCView::div(array('id'=>'tooltipBtnSetCustom', 'style'=>'clear:both;padding-bottom:2px;', 'onclick'=>"openAddUserPopup( $('#tooltipHiddenUsername').val());"),
						RCView::button(array('class'=>'jqbuttonmed'), $lang['rights_153'])
					) .
					// Remove from Role button
					RCView::div(array('id'=>'tooltipBtnRemoveRole', 'style'=>'padding-bottom:2px;', 'onclick'=>"assignUserRole( $('#tooltipHiddenUsername').val(),0)"),
						RCView::button(array('class'=>'jqbuttonmed'), $lang['rights_175'])
					) .
					// Assign User button
					RCView::div(array('id'=>'tooltipBtnAssignRole'),
						RCView::button(array('id'=>'assignUserBtn2', 'class'=>'jqbuttonmed'),
							RCView::span(array('style'=>'vertical-align:middle;'), $lang['rights_156']) .
							RCView::img(array('src'=>'arrow_state_grey_expanded.png', 'style'=>'margin-left:5px;vertical-align:middle;position:relative;top:-1px;'))
						)
					) .
					// Re-assign User button
					RCView::div(array('id'=>'tooltipBtnReassignRole'),
						RCView::button(array('id'=>'assignUserBtn3', 'class'=>'jqbuttonmed nowrap'),
							RCView::span(array('style'=>'vertical-align:middle;'), $lang['rights_173']) .
							RCView::img(array('src'=>'arrow_state_grey_expanded.png', 'style'=>'margin-left:5px;vertical-align:middle;position:relative;top:-1px;'))
						)
					) .
					// Hidden input where username is store for the user just clicked, which opened this tooltip (so we know which was clicked)
					RCView::hidden(array('id'=>'tooltipHiddenUsername'))
				);

        Design::alertRecentImportStatus();

        $html .= "<script type='text/javascript'>
						$(function(){
						    $('#downloadUploadUsersDropdown').menu();
                            $('#downloadUploadUsersDropdownDiv ul li a').click(function(){
                                $('#downloadUploadUsersDropdownDiv').hide();
                            });
						});
				  </script>";
		// Return the html for displaying the table
		return $html . renderGrid("user_rights_roles_table", '', $tableWidth, "auto", $hdrs, $rows, true, true, false);
	}

	// Detect if a single user has User Rights privileges in *any* project (i.e. is a project owner) - includes roles that user is in
	public static function hasUserRightsPrivileges($user)
	{
		// Query to see if have User Rights privileges in at least one project (consider roles rights in this)
		$sql = "select 1 from redcap_user_rights u left join redcap_user_roles r
				on r.role_id = u.role_id where u.username = '".db_escape($user)."'
				and ((u.user_rights = 1 and r.user_rights is null) or r.user_rights = 1) limit 1";
		$q = db_query($sql);
		return ($q && db_num_rows($q) > 0);
	}

	// Detect if a single user's privileges have expired in a projecxt
	public static function hasUserRightsExpired($project_id, $user)
	{
		// Query to see if have User Rights privileges in at least one project (consider roles rights in this)
		$sql = "select 1 from redcap_user_rights where project_id = $project_id and username = '".db_escape($user)."' 
				and expiration is not null and expiration != '' and expiration <= '".TODAY."' limit 1";
		$q = db_query($sql);
		return ($q && db_num_rows($q) > 0);
	}
	
	// External Modules: Display project menu link only to super users or to users with Design Setup 
	// rights *if* one or more modules are already enabled *or* if at least one module has been set as "discoverable" in the system
	public static function displayExternalModulesMenuLink()
	{
		global $user_rights, $status;
		// If Ext Mods not enabled, do not display
		if (!defined("APP_PATH_EXTMOD")) return false;
		// Always show the link to admins
		if (UserRights::isSuperUserNotImpersonator()) return true;
		// If project is not in dev or prod (archived/inactive, except for super users), do not display
		if ($status > 1) return false;
		// Check if project has any modules enabled or if any modules are discoverable
		$systemHasDiscoverableModules = (method_exists('\ExternalModules\ExternalModules', 'hasDiscoverableModules') 
										&& \ExternalModules\ExternalModules::hasDiscoverableModules());
		$enabledModules = \ExternalModules\ExternalModules::getEnabledModules(PROJECT_ID);		
		$projectHasModulesEnabled = !empty($enabledModules);
		// If the project doesn't have modules enabled AND system doesn't have any discoverable modules, then don't show
		if (!$projectHasModulesEnabled && !$systemHasDiscoverableModules) {
			return false;
		}
		// If user has Design/Setup rights AND project has modules enabled or modules are discoverable, then show
		if ($user_rights['design'] == '1') {
			return true;
		}
		// Determine if user has permission to configure at least one module in this project
		foreach ($enabledModules as $moduleDirectoryPrefix=>$moduleVersion) {
			$thisConfigUserPerm = \ExternalModules\ExternalModules::getSystemSetting($moduleDirectoryPrefix, \ExternalModules\ExternalModules::KEY_CONFIG_USER_PERMISSION);
			$userHasConfigPermissions = ($thisConfigUserPerm != '' && $thisConfigUserPerm != false);
			// User has permission to configure module
			if ($userHasConfigPermissions && in_array($moduleDirectoryPrefix, $user_rights['external_module_config'])) {
				return true;
			}
		}
		// Return false if we got this far
		return false;
	}
	
	// External Modules: Display checkbox for each enabled module in a project in the Edit User dialog on the User Rights page
	public static function getExternalModulesUserRightsCheckboxes()
	{
		// If Ext Mods not enabled, do not display
		if (!defined("APP_PATH_EXTMOD")) return false;
		if (!method_exists('\ExternalModules\ExternalModules', 'getModulesWithCustomUserRights')) return false;
		// Get array of all enabled modules with attributes
		return \ExternalModules\ExternalModules::getModulesWithCustomUserRights(PROJECT_ID);
	}

	// Render the Impersonate User drop-down for admins
	public static function renderImpersonateUserDropDown()
	{
		global $lang;
		if (!self::isSuperUserOrImpersonator()) return '';
		$selected = '';
		if (defined("PROJECT_ID") && isset($_SESSION['impersonate_user'][PROJECT_ID])) {
			$selected = $_SESSION['impersonate_user'][PROJECT_ID]['impersonating'];
		}
		// Get the current user's username
		$currentUser = defined("PROJECT_ID") && isset($_SESSION['impersonate_user'][PROJECT_ID]['impersonator']) ? $_SESSION['impersonate_user'][PROJECT_ID]['impersonator'] : USERID;
		// Remove the current user from this list of users so that they cannot choose themselves
		$options = UserRights::getUsersRoles();
		foreach ($options as $role=>$users) {
			foreach ($users as $key=>$val) {
				if ($key == $currentUser) {
					unset($options[$role][$key]);
				}
			}
			if (empty($options[$role])) {
				unset($options[$role]);
			}
		}
		if (count($options) == 1 && isset($options[$lang['rights_361']])) {
			$options = $options[$lang['rights_361']];
		}
		$blankValText = defined("PROJECT_ID") && isset($_SESSION['impersonate_user'][PROJECT_ID]['impersonator']) ? $lang['rights_368'] : $lang['rights_363'];
		$options = array(''=>$blankValText)+$options;
		// Render drop-down
		$dd = RCView::select(array('id'=>'impersonate-user-select', 'class'=>'x-form-text x-form-field fs11 py-0 ml-1', 'style'=>'max-width:150px;'), $options, $selected);
		$div = 	RCView::div(array('class'=>'fs11 nowrap boldish', 'style'=>'margin: 5px 0 2px;'),
					'<span style="position:relative;top:1px;"><i class="fas fa-user-tie mr-1"></i>'.$lang['rights_362'].'</span>'.$dd
				);
		return $div;
	}

	// Get all roles and users in the project in an associative array with role name as array key
	public static function getUsersRoles()
	{
		global $lang;
		$all_users_roles = array();
		$roles = UserRights::getRoles();
		$proj_users = UserRights::getRightsAllUsers(false);
		foreach ($proj_users as $this_user=>$attr) {
			if ($this_user == '') continue;
			if (is_numeric($attr['role_id'])) {
				$attr['role_id'] = $roles[$attr['role_id']]['role_name'];
			} else {
				$attr['role_id'] = $lang['rights_361'];
			}
			$all_users_roles[$attr['role_id']][$this_user] = $this_user . ($attr['user_fullname'] == '' ? '' : " ({$attr['user_fullname']})");
		}
		natcaseksort($all_users_roles);
		foreach ($all_users_roles as &$these_users) {
			natcaseksort($these_users);
		}
		return $all_users_roles;
	}

	// Is the current user an admin (including possibly impersonating a non-super user)?
	public static function isSuperUserOrImpersonator()
	{
		return (defined("SUPER_USER") && SUPER_USER || self::isImpersonatingUser());
	}

	// Is the current user an admin and is NOT currently impersonating a non-super user?
	public static function isSuperUserNotImpersonator()
	{
		return (defined("SUPER_USER") && SUPER_USER && !self::isImpersonatingUser());
	}

	// Is the current user impersonating another user in this project?
	public static function isImpersonatingUser()
	{
		return (defined("PROJECT_ID") && isset($_SESSION['impersonate_user'][PROJECT_ID]));
	}

	// Get the name of the user being impersonated by an admin
	public static function getUsernameImpersonating()
	{
		return self::isImpersonatingUser() ? $_SESSION['impersonate_user'][PROJECT_ID]['impersonating'] : '';
	}

	// Impersonate a user (admins only)
	public static function impersonateUser()
	{
		global $lang;
		if (!isset($_POST['user']) || !self::isSuperUserOrImpersonator()) {
			exit('0');
		}
		// Verify that user is a project user
		$proj_users = UserRights::getRightsAllUsers(false);
		if (!isset($proj_users[$_POST['user']]) && $_POST['user'] != '') exit('0');
		// Add to session or remove it if blank
		if ($_POST['user'] == '') {
			$msg =  $lang['rights_369'];
			$log = "(Admin only) Stop viewing project as user \"{$_SESSION['impersonate_user'][PROJECT_ID]['impersonating']}\"";
			unset($_SESSION['impersonate_user'][PROJECT_ID]);
		} else {
			$msg = $lang['rights_364'] . " \"" . RCView::b($_POST['user']) . "\"" . $lang['period'] . " " . $lang['rights_365'];
			$log = "(Admin only) View project as user \"{$_POST['user']}\"";
			$_SESSION['impersonate_user'][PROJECT_ID] = array('impersonator'=>USERID, 'impersonating'=>$_POST['user']);
		}
		// Log the event
		Logging::logEvent("","redcap_user_rights","MANAGE",$_POST['user'],"user = '{$_POST['user']}'", $log);
		// Return success
		print RCView::div(array('class'=>'green'),
			'<i class="fas fa-check"></i> ' . $msg
		);
	}

	// If impersonating another user in this project, display banner as reminder
	public static function renderImpersonatingUserBanner()
	{
		global $lang;
		if (!self::isImpersonatingUser()) return '';
		$impersonating = $_SESSION['impersonate_user'][PROJECT_ID]['impersonating'];
		$userInfo = User::getUserInfo($impersonating);
		$impersonatingName = trim($userInfo['user_firstname']." ".$userInfo['user_lastname']);
		if ($impersonatingName != '') $impersonatingName = " ($impersonatingName)";
		return "<div class='green fs13 py-2 pr-1' style='margin-left:-20px;max-width:100%;text-indent:-11px;padding-left:30px;'>
				<i class=\"fas fa-user-tie mr-2\"></i>{$lang['rights_366']} <b>\"$impersonating\"$impersonatingName</b>{$lang['rights_367']}</div>";
	}

	public static function removePrivileges($project_id, $user, $ExtRes = null)
	{
		// Delete user from project rights table
		$sql = "DELETE FROM redcap_user_rights WHERE project_id = $project_id and username = '".db_escape($user)."'";
		
		$result = db_query($sql);
		if ($result)
		{
			if($ExtRes){
				// Also delete from project bookmarks users table as well
				$sql2 = "DELETE FROM redcap_external_links_users WHERE username = '".db_escape($user)."' and ext_id in
				(" . implode(",", array_keys($ExtRes->getResources())) . ")";
				db_query($sql2);
			}
			
			// Also delete from redcap_reports_access_users table
			$sql3 = "DELETE FROM redcap_reports_access_users WHERE username = '".db_escape($user)."' and report_id in
					(select report_id from redcap_reports where project_id = $project_id)";
			db_query($sql3);
			
			// Remove from any linked conversations in Messenger.
			Messenger::removeUserFromLinkedProjectConversation($project_id, $user);
			
			// Logging
			Logging::logEvent("$sql;\n$sql2;\n$sql3","redcap_user_rights","delete",$user,"user = '".db_escape($user)."'","Delete user");
		}

		return $result;
	}

	public static function addRole($Proj, $role_name, $user)
	{
		//Insert user into user rights table
		$fields = "project_id, role_name, data_export_tool, data_import_tool, data_comparison_tool, data_logging, file_repository, double_data, " .
		"user_rights, design, lock_record, lock_record_multiform, lock_record_customize, data_access_groups, graphical, reports, calendar, " .
		"record_create, record_rename, record_delete, dts, participants, data_quality_design, data_quality_execute, data_quality_resolution,
		api_export, api_import, mobile_app, mobile_app_download_data,
		random_setup, random_dashboard, random_perform, realtime_webservice_mapping, realtime_webservice_adjudicate, external_module_config,
		data_entry";
		$values =  "{$Proj->project_id}, '".db_escape($role_name)."', '{$_POST['data_export_tool']}', '{$_POST['data_import_tool']}', '{$_POST['data_comparison_tool']}',
		'{$_POST['data_logging']}', '{$_POST['file_repository']}', '{$_POST['double_data']}', '{$_POST['user_rights']}',
		'{$_POST['design']}', '{$_POST['lock_record']}', '{$_POST['lock_record_multiform']}',
		'{$_POST['lock_record_customize']}', '{$_POST['data_access_groups']}', '{$_POST['graphical']}', '{$_POST['reports']}',
		'{$_POST['calendar']}', '{$_POST['record_create']}', '{$_POST['record_rename']}', '{$_POST['record_delete']}',
		'{$_POST['dts']}', '{$_POST['participants']}', '{$_POST['data_quality_design']}', '{$_POST['data_quality_execute']}', '{$_POST['data_quality_resolution']}',
		'{$_POST['api_export']}', '{$_POST['api_import']}', '{$_POST['mobile_app']}', '{$_POST['mobile_app_download_data']}', '{$_POST['random_setup']}', '{$_POST['random_dashboard']}',
		'{$_POST['random_perform']}', '{$_POST['realtime_webservice_mapping']}', '{$_POST['realtime_webservice_adjudicate']}', ".checkNull($_POST['external_module_config']).", '";
		foreach (array_keys($Proj->forms) as $form_name)
		{
		// Process each form's radio button value
		$this_field = "form-" . $form_name;
		$this_value = ($_POST[$this_field] == '') ? 0 : $_POST[$this_field];
		// If set survey responses to be editable, then set to value 3
		$editresp_chkbox_name = "form-editresp-" . $form_name;
		if ($this_value == '1' && isset($_POST[$editresp_chkbox_name]) && $_POST[$editresp_chkbox_name])
		{
		$this_value = 3;
		}
		$values .= "[$form_name,$this_value]";
		}
		$values .= "'";
		// Insert user into user_rights table
		$sql = "INSERT INTO redcap_user_roles ($fields) VALUES ($values)";
		$result = db_query($sql);
		if ($result) {
			// Logging
			Logging::logEvent($sql,"redcap_user_rights","insert",$user,"role = '$role_name'","Add role");
		}

		return $result;
	}

	public static function removeRole($project_id, $role_id, $role_name){
		// Delete user from project rights table
		$sql = "DELETE FROM redcap_user_roles WHERE project_id = $project_id and role_id = '".db_escape($role_id)."'";
		$result = db_query($sql);
		if ($result)
		{
			/*
			// For ALL users in role, set role_id to NULL and give the user the exact same rights as the role deleted in order to maintain continuity of privileges
			$this_role_rights = $roles[$user];
			$this_role_users = $this_role_rights['role_users_assigned'];
			// Set role_id to NULL and give the user the exact same rights as the role they were removed from in order to maintain continuity of privileges
			unset($this_role_rights['role_name'], $this_role_rights['project_id'], $this_role_rights['role_users_assigned']);
			// Loop through each user that was in the role
			$sql_all = $sqla = array();
			foreach ($this_role_rights as $key=>$val) $sqla[] = "$key = ".checkNull($val);
			foreach ($this_role_users as $this_role_user) {
				$sql_all[] = $sql = "update redcap_user_rights set role_id = null, " . implode(", ", $sqla) . "
									 where project_id = $project_id and username = '".db_escape($this_role_user)."'";
				db_query($sql);
			}
			*/
			// Logging
			Logging::logEvent($sql,"redcap_user_rights","delete",$role_id,"role = '$role_name'","Delete role");
		}

		return $result;
	}

	// Get User Details for export users functionality
	public static function getUserDetails($projectId, $mobile_app_enabled=false)
	{
        $Proj = new Project();

        // Get all user's rights (includes role's rights if they are in a role)
        $user_priv = UserRights::getPrivileges($projectId);
        $user_priv = $user_priv[$projectId];

        # get user information (does NOT include role-based rights for user)
        $sql = "SELECT ur.*, ui.user_email, ui.user_firstname, ui.user_lastname, ui.super_user
			FROM redcap_user_rights ur
			LEFT JOIN redcap_user_information ui ON ur.username = ui.username
			WHERE ur.project_id = ".PROJECT_ID;
        $users = db_query($sql);
        $result = array();
        $r = 0;
        while ($row = db_fetch_assoc($users))
        {
            // Decode and set any nulls to ""
            foreach ($row as &$val) {
                if (is_array($val)) continue;
                if ($val == null) $val = '';
                $val = html_entity_decode($val, ENT_QUOTES);
            }

            // Convert username to lower case to prevent case sensitivity issues with arrays
            $row["username"] = strtolower($row["username"]);

            // Parse data entry rights
//            if ($row["super_user"]) {
//                foreach ($Proj->forms as $this_form=>$attr) {
//                    $forms[$this_form] = (isset($attr['survey_id'])) ? 3 : 1;
//                }
//            } else {
                // Regular user
                $dataEntryArr = explode("][", substr(trim($user_priv[$row["username"]]['data_entry']), 1, -1));
                $forms = array();
                foreach ($dataEntryArr as $keyval)
                {
					if ($keyval == '') continue;
                    list($key, $value) = explode(",", $keyval, 2);
                    if ($key == '') continue;
                    $forms[$key] = isinteger($value) ? (int)$value : $value;
                }
//            }

            // Check group_id
            $unique_group_name = "";
            if (is_numeric($row['group_id'])) {
                $unique_group_name = $Proj->getUniqueGroupNames($row['group_id']);
                if (empty($unique_group_name)) {
                    $unique_group_name = $row['group_id'] = "";
                }
            }

            // Set array entry for this user
            $result[$r] = array(
                'username'					=> $row['username'],
                'email'						=> $row['user_email'],
                'firstname'					=> $row['user_firstname'],
                'lastname'					=> $row['user_lastname'],
                'expiration'				=> $row['expiration'],
                'data_access_group'			=> $unique_group_name,
                'data_access_group_id'		=> (isinteger($row['group_id']) ? (int)$row['group_id'] : $row['group_id'])
            );

            // Rights that might be governed by roles
            $rights = array(
                'design', 'user_rights', 'data_access_groups', 'data_export_tool'=>'data_export',
                'reports', 'graphical'=>'stats_and_charts',
                'participants'=>'manage_survey_participants', 'calendar',
                'data_import_tool', 'data_comparison_tool', 'data_logging'=>'logging',
                'file_repository', 'data_quality_design'=>'data_quality_create', 'data_quality_execute',
                'api_export', 'api_import',
                'mobile_app', 'mobile_app_download_data',
                'record_create', 'record_rename', 'record_delete', 'record_create',
                'lock_record_multiform'=>'lock_records_all_forms',
                'lock_record'=>'lock_records',
                'lock_record_customize'=>'lock_records_customization'
            );

            foreach($rights as $right=>$right_formatted)
            {
                $thisPriv = $user_priv[$row['username']][(is_numeric($right) ? $right_formatted : $right)];
                $result[$r][$right_formatted] = (isinteger($thisPriv) ? (int)$thisPriv : $thisPriv);
            }

            // Add form rights at end
            $result[$r]['forms'] = $forms;

            // If mobile app is not enabled, then remove the mobile_app user privilege attributes
            if (!$mobile_app_enabled) {
                unset($result[$r]['mobile_app'], $result[$r]['mobile_app_download_data']);
            }

            // Set for next loop
            $r++;
        }
        return $result;
    }

    // Update Users for a given project
    // Return array with count of users updated and array of errors, if any
    public static function uploadUsers($project_id, $data) {
        global $lang;

        $rights_fields = array('design','user_rights','data_access_groups','reports','stats_and_charts','manage_survey_participants',
                                'calendar','data_import_tool','data_comparison_tool','logging','file_repository','data_quality_create',
                                'data_quality_execute','api_export','api_import','mobile_app','mobile_app_download_data','record_create',
                                'record_rename','record_delete','lock_records_all_forms','lock_records','lock_records_customization');

        $count = 0;
        $errors = array();

        $Proj = new Project($project_id);
        $dags = $Proj->getUniqueGroupNames();
        // Check for basic attributes needed
        foreach ($data as $key=>&$this_user) {
            $this_user['username'] = trim($this_user['username']);
            // If username is missing
            if (!isset($this_user['username']) || $this_user['username'] == '') {
                $errors[] = $lang['api_118'] . ($key+1) . " " . $lang['api_119'];
                continue;
            }
            // Validation username format
            if (!preg_match("/^([a-zA-Z0-9'_\s\.\-\@])+$/", $this_user['username'])) {
                $errors[] = "username \"{$this_user['username']}\" " . $lang['api_151'] . " " . $lang['rights_354'];
            }
            // Validate DAG (if provided)
            if ($this_user['data_access_group'] != '' && !array_search($this_user['data_access_group'], $dags)) {
                $errors[] = "data_access_group \"{$this_user['data_access_group']}\" " . $lang['api_111'];
            }

            // Check Other attribute rights values
            foreach ($rights_fields as $field) {
                if (isset($this_user[$field]) && !empty($this_user[$field])) {
                    if (!is_numeric($this_user[$field]) || !in_array($this_user[$field], array(0,1))) {
                        $errors[] = $lang['rights_385']. " \"{$field}\" ".$lang['rights_386']." \"{$this_user['username']}\"  ".$lang['api_116']." \"{$this_user[$field]}\" ".$lang['rights_387']." ".$lang['rights_388'];
                    }
                }
            }
            // Check Data Export rights value
            if (isset($this_user['data_export']) && !empty($this_user['data_export'])) {
                if (!in_array($this_user['data_export'], array(0,1,2))) {
                    $errors[] = $lang['rights_385']. " \"data_export\" ".$lang['rights_386']." \"{$this_user['username']}\"  ".$lang['api_116']." \"{$this_user['data_export']}\" ".$lang['rights_387']." ".$lang['rights_389'];
                }
            }
            // Check form-level rights
            if (isset($this_user['forms']) && !empty($this_user['forms'])) {
                // Parse the forms
                $these_forms = array();
                foreach ($this_user['forms'] as $this_form=>$this_right) {
                    // Is valid form and right level value?
                    if (!isset($Proj->forms[$this_form])) {
                        $errors[] = $lang['api_113'] . " \"$this_form\" " . $lang['api_114'] . " \"{$this_user['username']}\" " . $lang['api_115'];
                    } elseif (!is_numeric($this_right) || !($this_right >= 0 && $this_right <=3)) {
                        $errors[] = $lang['api_113'] . " \"$this_form\" " . $lang['api_116'] . " \"$this_right\" " . $lang['api_117'];
                    } else {
                        $these_forms[] = $this_form;
                    }
                }
                // If some forms are not provided, then by default set their rights to 0.
                $missing_forms = array_diff(array_keys($Proj->forms), $these_forms);
                foreach ($missing_forms as $this_form) {
                    $this_user['forms'][$this_form] = 0;
                }
                // Reformat form-level rights to back-end format
                $data_entry = "";
                foreach ($this_user['forms'] as $this_form=>$this_val) {
                    $data_entry .= "[$this_form,$this_val]";
                }
                $this_user['forms'] = $data_entry;
            }
            // Convert unique DAG name to group_id
            if ($this_user['data_access_group'] != '' && array_search($this_user['data_access_group'], $dags)) {
                $this_user['data_access_group'] = array_search($this_user['data_access_group'], $dags);
            }
            // Remove email, first name, and last name (if included)
            unset($this_user['email'], $this_user['firstname'], $this_user['lastname']);
        }

        unset($this_user);

        if (empty($errors)) {
            foreach($data as $ur)
            {
                $ur['expiration'] = ($ur['expiration'] != '') ? date("Y-m-d",strtotime($ur['expiration'])) : "";
                $privileges = UserRights::getPrivileges(PROJECT_ID, $ur['username']);
                if(empty($privileges))
                {
                    if(UserRights::addPrivileges(PROJECT_ID, $ur))
                    {
                        $count++;
                    }
                }
                else
                {
                    // If user is in a role, then return an error
                    if (is_numeric($privileges[PROJECT_ID][strtolower($ur['username'])]['role_id'])) {
                        if (PAGE == "UserRights/import_export_users.php") {
                            $errors[] = "The user \"{$ur['username']}\" " . $lang['rights_396'];
                        } else {
                            $errors[] = "The user \"{$ur['username']}\" " . $lang['api_112'];
                        }
                        continue;
                    }

                    // Update
                    if ($ur['data_access_group'] == 0) {
                        $ur['data_access_group'] = NULL;
                    }
                    if(self::updatePrivileges(PROJECT_ID, $ur))
                    {
                        $count++;
                    }
                }
            }
        }

        // Return count and array of errors
        return array($count, $errors);
    }

    public static function isFormRightsUpdated($csvDataEntry, $dataEntry, $isSuperUser = 0) {
        // Parse data entry rights
        if ($isSuperUser == 1) {
            return false;
        } else {
            // Regular user
            $allForms = explode("][", substr(trim($dataEntry), 1, -1));
            $formsArr = array();
            foreach ($allForms as $form)
            {
                list($this_form, $this_form_rights) = explode(",", $form, 2);
                $formsArr[$this_form] = $this_form_rights;
                if ($csvDataEntry[$this_form] != $this_form_rights) {
                    return true;
                }
            }
            if(!empty(array_diff_key($csvDataEntry, $formsArr))) {
                return true;
            }
            return false;
        }
    }

    // Add unique user role name for role
    private static function addUniqueUserRoleName($role_id)
    {
        // Prefix
        $prefix = "U-"; // User Role prefix
        $success = false;
        while (!$success) {
            // Generate new unique name (start with 3 digit number followed by 7 alphanumeric chars) - do not allow zeros
            $unique_name = $prefix . str_replace("0", mt_rand(1, 9), str_pad(mt_rand(0, 999), 3, 0, STR_PAD_LEFT)) . generateRandomHash(7, false, true);
            // Update the table
            $sql = "UPDATE redcap_user_roles SET unique_role_name = '" . db_escape($unique_name) . "' WHERE role_id = $role_id";
            $success = db_query($sql);
        }
        return $unique_name;
    }
}