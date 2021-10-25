<?php

class SurveyController extends Controller
{
	// Change a participant's Link Expiration time (time limit)
	public function changeLinkExpiration()
	{
		if ($_POST['action'] == 'save') {
			Survey::changeLinkExpiration();
		} else {
			Survey::changeLinkExpirationRenderDialog();
		}
	}
	
	// Render the HTML table for a record's scheduled survey invitations to be sent in the next X days
	public function renderUpcomingScheduledInvites()
	{
		global $lang;
		$SurveyScheduler = new SurveyScheduler();
		print RCView::div(array('style'=>'margin-bottom:10px;'), $lang['survey_1134'] . " ". RCView::b((int)$_POST['days'] . " " . $lang['scheduling_25']) . $lang['period']);
		print $SurveyScheduler->renderSurveyInvitationLog(rawurldecode(urldecode($_POST['record'])), false, $_POST['days']);
	}

    // Enable/disable Google reCaptcha
    public function enableCaptcha()
    {
        $enable = (isset($_POST['enable']) && (int)$_POST['enable'] === 1) ? '1' : '0';
        print Survey::enableCaptcha($enable) ? '1' : '0';
    }

	// Re-evaluate ASIs
	public function reevalAutoInvites()
	{
		global $lang;
		$surveyScheduler = new SurveyScheduler(PROJECT_ID);
		if ($_GET['action'] == 'save') {
			$surveysEvents = array();
			foreach (explode(",", $_POST['surveysEvents']) as $this_se) {
				list ($survey_id, $event_id) = explode("-", $this_se, 2);
				if (!isinteger($survey_id) || !isinteger($event_id)) continue;
				$surveysEvents[$survey_id][$event_id] = true;
			}
			list ($numInvitationsScheduled, $numInvitationsDeleted, $numRecordsAffected) = $surveyScheduler->triggerASIs(PROJECT_ID, $surveysEvents);
			if ($numRecordsAffected > 0) {
				print 	RCView::div(array('class'=>'text-success'),
							RCView::b('<i class="fas fa-check"></i> '.$lang['global_79']) . "<br>$numInvitationsScheduled " . $lang['asi_034'] .
							" $numInvitationsDeleted " . $lang['asi_035'] . RCView::b(" $numRecordsAffected " . $lang['data_entry_173']) . $lang['period']
						);
				$msglog = "$numInvitationsScheduled " . $lang['asi_034'] . " $numInvitationsDeleted " . $lang['asi_035'] . " $numRecordsAffected " . $lang['data_entry_173'] . $lang['period'];
			} else {
				print 	RCView::div(array('class'=>'text-success'),
							'<i class="fas fa-check"></i> '.$lang['asi_033']
						);
				$msglog = $lang['alerts_257'];
			}
			// Logging
			Logging::logEvent("", "redcap_surveys_scheduler_queue", "MANAGE", PROJECT_ID, "Re-evaluate automated survey invitations:\n".strip_tags($msglog), "Re-evaluate automated survey invitations");
		} elseif ($_GET['action'] == 'view') {
			print $surveyScheduler->displayAutoInviteSurveyEventCheckboxList(PROJECT_ID);
		} else {
			print '0';
		}
	}
}