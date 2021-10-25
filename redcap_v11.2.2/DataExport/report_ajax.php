<?php


require_once dirname(dirname(__FILE__)) . '/Config/init_project.php';

// Get report info
$report_id = $_POST['report_id'];
if ($report_id != 'ALL' && $report_id != 'SELECTED') {
	$report_id = (int)$report_id;
}
$report = DataExport::getReports($report_id);

// Checks for public reports
if (DataExport::isPublicReport())
{
	DataExport::checkReportHash($report_id);
	$report = DataExport::getReports($report_id);
	// Make sure user has access to this report if viewing inside a project
	$noAccess = false;
	if ($report['is_public'] != '1') {
		// If viewing a public report link that is no longer set as "public", return error message
		$type = 'error';
		$noAccess = true;
		$errorMsg = $lang['report_builder_184'];
	} else {
		$reports = DataExport::getReports($report_id);
		// List of fields to verify if field is phi
		$fields = $reports['fields'];
		foreach ($fields as $field_name) {
			if ($Proj->metadata[$field_name]['field_phi'] == '1') {
				$type = 'notice';
				$noAccess = true;
				$errorMsg = $lang['report_builder_188'];
				break;
			}
		}
		// If using Custom Record Label/Secondary Unique Field, return error if any of those fields are identifiers
		if ($noAccess == false && in_array($Proj->table_pk, $fields) && trim($secondary_pk.$custom_record_label) != '') {
			if ($Proj->metadata[$secondary_pk]['field_phi'] == '1') {
				// Secondary Unique Field is an Identifier
				$type = 'notice';
				$noAccess = true;
				$errorMsg = $lang['report_builder_217'];
			} elseif (trim($custom_record_label) != '') {
				// Get the variables in $custom_record_label and then check if any are Identifiers
				$custom_record_label_fields = array_unique(array_keys(getBracketedFields($custom_record_label, true, true, true)));
				foreach ($custom_record_label_fields as $field_name) {
					if ($Proj->metadata[$field_name]['field_phi'] == '1') {
						$type = 'notice';
						$noAccess = true;
						$errorMsg = $lang['report_builder_218'];
						break;
					}
				}
			}
		}
	}
	// Display the error if necessary
	if ($noAccess) {
		print RCView::div(array('class' => 'red my-5'), RCView::b(($type == 'error') ? $lang['global_01'] : $lang['global_03'] . $lang['colon']) . " " . $errorMsg);
		exit;
	}
}

$script_time_start = microtime(true);

// Build dynamic filter options (if any)
$dynamic_filters = DataExport::displayReportDynamicFilterOptions($report_id);
// Obtain any dynamic filters selected from query string params
list ($liveFilterLogic, $liveFilterGroupId, $liveFilterEventId) = DataExport::buildReportDynamicFilterLogic($report_id);
// Total number of records queried
$totalRecordsQueried = Records::getCountRecordEventPairs();
// For report A and B, the number of results returned will always be the same as total records queried
if (($report_id == 'ALL' || ($report_id == 'SELECTED' && (!isset($_GET['events']) || empty($_GET['events']))))
	&& !isset($_GET['lf1']) && !isset($_GET['lf2']) && !isset($_GET['lf3']))
{
	if ($user_rights['group_id'] == '') {
		$num_results_returned = $totalRecordsQueried;
	} else {
		$num_results_returned = Records::getCountRecordEventPairs($user_rights['group_id']);
	}
}
// Get html report table
list ($report_table, $num_results_returned) = DataExport::doReport($report_id, 'report', 'html', false, false, false, false,
												false, false, false, false, false, false, false,
												(isset($_GET['instruments']) ? explode(',', $_GET['instruments']) : array()),
												(isset($_GET['events']) ? explode(',', $_GET['events']) : array()),
												false, false, false, true, true, $liveFilterLogic, $liveFilterGroupId, $liveFilterEventId);
// Report B only: If repeating instruments exist, and we're filtering using a repeating instrument, then the row counts can get skewed and be incorrect. This fixes it.
if ($report_id == 'SELECTED' && (!isset($_GET['events']) || empty($_GET['events'])) && !isset($_GET['lf1']) && !isset($_GET['lf2']) && !isset($_GET['lf3'])) {
	$totalRecordsQueried = $num_results_returned;
}
// Check report edit permission
$report_edit_access = defined("SUPER_USER") ? SUPER_USER : 0;
if ((!defined("SUPER_USER") || !SUPER_USER) && is_numeric($report_id)) {
    $reports_edit_access = DataExport::getReportsEditAccess(USERID, $user_rights['role_id'], $user_rights['group_id'], $report_id);
    $report_edit_access = in_array($report_id, $reports_edit_access);
}
$script_time_total = round(microtime(true) - $script_time_start, 1);

// Display report and title and other text
print  	"<div id='report_div' style='margin:0 0 20px;'>" .
			RCView::div(array('style'=>''),
				RCView::div(array('class'=>'d-print-none', 'style'=>'float:left;width:350px;padding-bottom:5px;'),
					(isset($_GET['__report'])
						? RCView::div(array('class'=>'text-secondary fs12'),
							$lang['custom_reports_02'] .
							RCView::b(array('style'=>'margin-left:5px;'),
								User::number_format_user($num_results_returned)
							)
						)
						: RCView::div(array('class'=>'font-weight-bold'),
							$lang['custom_reports_02'] .
							RCView::span(array('style'=>'margin-left:5px;color:#800000;font-size:15px;'),
								User::number_format_user($num_results_returned)
							)
						)
					).
					(isset($_GET['__report']) ? "" :
						RCView::div(array(),
							$lang['custom_reports_03'] .
							RCView::span(array('id'=>'records_queried_count', 'style'=>'margin-left:5px;'),
								User::number_format_user($totalRecordsQueried)
							) .
							(!$longitudinal ? "" :
								RCView::div(array('class'=>'fs11 mt-1', 'style'=>'color:#999;font-family:tahoma,arial;'),
									$lang['custom_reports_09']
								)
							)
						) .
						RCView::div(array('class'=>'fs11 mt-1', 'style'=>'color:#6f6f6f;'),
							$lang['custom_reports_19']." $script_time_total ".$lang['control_center_4469']
						)
					)
				) .
				RCView::div(array('class'=>'d-print-none', 'style'=>'float:left;'),
					// Buttons: Stats, Export, Print, Edit
					(isset($_GET['__report']) ? "" :
						RCView::div(array(),
							// Public link
							(!($report['is_public'] && $user_rights['user_rights']&& $GLOBALS['reports_allow_public'] > 0) ? "" :
								RCView::a(array('href'=>($report['short_url'] == "" ? APP_PATH_WEBROOT_FULL.'surveys/index.php?__report='.$report['hash'] : $report['short_url']), 'target'=>'_blank', 'class'=>'text-primary fs12 nowrap mr-3 ml-1 align-middle'),
									'<i class="fas fa-link"></i> ' .$lang['dash_35']
								)
							) .
							// Stats & Charts button
							(!$user_rights['graphical'] || !$enable_plotting ? '' :
								RCView::button(array('class'=>'report_btn jqbuttonmed', 'onclick'=>"window.location.href = '".APP_PATH_WEBROOT."DataExport/index.php?pid=".PROJECT_ID."&report_id={$report_id}&stats_charts=1'+getInstrumentsListFromURL()+getLiveFilterUrl();", 'style'=>'color:#800000;font-size:12px;'),
									'<i class="fas fa-chart-bar"></i> ' .$lang['report_builder_78']
								)
							) .
							RCView::SP .
							// Export Data button
							($user_rights['data_export_tool'] == '0' ? '' :
								RCView::button(array('class'=>'report_btn jqbuttonmed', 'onclick'=>"showExportFormatDialog('{$report_id}');", 'style'=>'color:#000066;font-size:12px;'),
									'<i class="fas fa-file-download"></i> ' .$lang['report_builder_48']
								)
							) .
							RCView::SP .
							// Print link
							RCView::button(array('class'=>'report_btn jqbuttonmed', 'onclick'=>"$('div.dataTables_scrollBody, div.dataTables_scrollHead').css('overflow','visible');$('.DTFC_Cloned').hide();setProjectFooterPosition();window.print();", 'style'=>'font-size:12px;'),
								RCView::img(array('src'=>'printer.png', 'style'=>'vertical-align:middle;')) .
								RCView::span(array('style'=>'vertical-align:middle;'),
									$lang['custom_reports_13']
								)
							) .
							RCView::SP .
							(($report_id == 'ALL' || $report_id == 'SELECTED' || !$user_rights['reports'] || (is_numeric($report_id) && !$report_edit_access)) ? '' :
								// Edit report link
								RCView::button(array('class'=>'report_btn jqbuttonmed', 'onclick'=>"window.location.href = '".APP_PATH_WEBROOT."DataExport/index.php?pid=".PROJECT_ID."&report_id={$report_id}&addedit=1';", 'style'=>'font-size:12px;'),
									'<i class="fas fa-pencil-alt fs10"></i> ' .$lang['custom_reports_14']
								)
							)
						)
					) .
					// Dynamic filters (if any)
					$dynamic_filters
				) .
				RCView::div(array('class'=>'clear'), '')
			) .
			// Report title
			RCView::div(array('id'=>'this_report_title', 'style'=>'margin:10px 0 '.($report['description'] == '' ? '8' : '0').'px;padding:5px 3px;color:#800000;font-size:18px;font-weight:bold;'),
				// Title
				$report['title']
			) .
			// Report description (if has one)
			($report['description'] == '' ? '' : 
				RCView::div(array('id'=>'this_report_description', 'style'=>'max-width:1100px;padding:5px 3px;line-height:15px;'),
					Piping::replaceVariablesInLabel(filter_tags($report['description']))
				) .
				// Output the JavaScript to display all Smart Charts on the page
				Piping::outputSmartChartsJS()
			) .
			// Report table
			$report_table .
		"</div>";
