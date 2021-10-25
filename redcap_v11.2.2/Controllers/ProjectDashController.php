<?php

class ProjectDashController extends Controller
{
	// Render the setup page
	public function index()
	{
		$this->render('HeaderProject.php', $GLOBALS);
		$dash = new ProjectDashboards();
		$dash->renderSetupPage();
		$this->render('FooterProject.php');
	}

	// View a dashboard
	public function view()
	{
		if (!isset($_GET['dash_id']) || !isinteger($_GET['dash_id'])) {
			redirect(APP_PATH_WEBROOT."index.php?route=ProjectDashController:index&pid={$_GET['pid']}");
		}
		$this->render('HeaderProject.php', $GLOBALS);
		$dash = new ProjectDashboards();
		$dash->viewDash($_GET['dash_id']);
		$this->render('FooterProject.php');
	}

	// View access list
	public function access()
	{
		global $lang;
		$dash = new ProjectDashboards();
		// Display list of usernames who would have access
		$content = $dash->displayDashboardAccessUsernames($_POST);
		// Output JSON
		print json_encode_rc(array('content'=>$content, 'title'=>$lang['report_builder_108']));
	}

	// Save a dashboard
	public function save()
	{
		if (!isset($_GET['dash_id']) || !isinteger($_GET['dash_id'])) {
			redirect(APP_PATH_WEBROOT."index.php?route=ProjectDashController:index&pid={$_GET['pid']}");
		}
		$dash = new ProjectDashboards();
		$dash->saveDash();
	}

	// Copy dashboard
	public function copy()
	{
		$dash = new ProjectDashboards();
		// Validate id
		if (!isset($_POST['dash_id'])) exit('0');
		$dash_id = $_POST['dash_id'];
		$dash1 = $dash->getDashboards(PROJECT_ID, $dash_id);
		if (empty($dash1)) exit('0');
		// Copy the report and return the new report_id
		$new_dash_id = $dash->copyDash($dash_id);
		if ($new_dash_id === false) exit('0');
		// Return HTML of updated report list and report_id
		print json_encode_rc(array('new_dash_id'=>$new_dash_id, 'html'=>$dash->renderDashboardList()));
	}

	// Delete dashboard
	public function delete()
	{
		$dash = new ProjectDashboards();
		// Validate id
		if (!isset($_POST['dash_id'])) exit('0');
		$dash_id = $_POST['dash_id'];
		$dash1 = $dash->getDashboards(PROJECT_ID, $dash_id);
		if (empty($dash1)) exit('0');
		// Delete the dashboard
		$success = $dash->deleteDash($dash_id);
		print ($success === false) ? '0' : '1';
	}

	// Reorder dashboards
	public function reorder()
	{
		$dash = new ProjectDashboards();
		$dash->reorderDashboards();
	}

	// Reload left-hand menu panel of dashboards
	public function viewpanel()
	{
		$dash = new ProjectDashboards();
		list ($dashboardsListTitle, $dashboardsListCollapsed) = $dash->outputDashboardPanelTitle();
		$dashboardsList = $dash->outputDashboardPanel();
		if ($dashboardsList != "") {
			print renderPanel($dashboardsListTitle, $dashboardsList, 'dashboard_panel', $dashboardsListCollapsed);
		}
	}

	// Create a custom short URL for a dashboard
	public function shorturl()
	{
		if (!isset($_POST['hash']) || !isset($_POST['custom_url']) || !isset($_POST['dash_id'])) exit("0");
		$dash = new ProjectDashboards();
		$dash->saveShortUrl($_POST['hash'], $_POST['custom_url'], $_POST['dash_id']);
	}

	// Delete the custom short URL for a dashboard
	public function remove_shorturl()
	{
		if (!isset($_POST['dash_id']) || !isinteger($_POST['dash_id'])) exit("0");
		$dash = new ProjectDashboards();
		$dash->removeShortUrl($_POST['dash_id']);
	}

	// Reset dashboard cache
	public function reset_cache()
	{
		if (!isset($_POST['dash_id']) || !isinteger($_POST['dash_id'])) exit("0");
		$dash = new ProjectDashboards();
		print $dash->resetCache($_POST['dash_id']) ? '1' : '0';
	}

	// Enable/disable colorblind feature of Pie/Donut Charts
	public function colorblind()
	{
		$dash = new ProjectDashboards();
		$dash->colorblind();
		print "1";
	}

	// Send request to admin to enable a public dashboard
	public function request_public_enable()
	{
		if (!isset($_POST['dash_id']) || !isinteger($_POST['dash_id'])) exit("0");
		$dash = new ProjectDashboards();
		$dash->requestPublicEnable($_POST['dash_id']);
	}

	// Admin approves request to enable a public dashboard
	public function public_enable()
	{
		if (!(SUPER_USER && ((isset($_GET['dash_id']) && isinteger($_GET['dash_id'])) || (isset($_POST['dash_id']) && isinteger($_POST['dash_id']))))) {
			exit("ERROR");
		}
		if (isset($_POST['dash_id'])) {
			$dash = new ProjectDashboards();
			$dash->publicEnable($_POST['dash_id'], false);
		} else {
			$this->render('HeaderProject.php', $GLOBALS);
			$dash = new ProjectDashboards();
			$dash->publicEnable($_GET['dash_id'], true);
			$this->render('FooterProject.php');
		}
	}
}