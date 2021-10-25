<?php


require_once dirname(dirname(__FILE__)) . '/Config/init_project.php';
?>
<link rel="stylesheet" href="<?php print APP_PATH_JS; ?>cdp-mapping/dist/cdp_mapping_vue.css">

<?php
// Header
include APP_PATH_DOCROOT  . 'ProjectGeneral/header.php';
renderPageTitle("<i class=\"fas fa-database\"></i> " . ($DDP->isEnabledInProjectFhir() ? $lang['ws_210'] : $lang['ws_51']) . " " . $DDP->getSourceSystemName());

// CSS & Javascript
?>

<script src="<?php print APP_PATH_JS; ?>vue.min.js"></script>
<script type="text/javascript" src="<?php print APP_PATH_JS; ?>cdp-mapping/dist/cdp_mapping_vue.umd.min.js" defer></script>
<div id="cdp-mapping-container"></div>
<script>
	(function(Vue) {
		window.addEventListener('DOMContentLoaded', function(event) {
			const cdp_mapping = new Vue(cdp_mapping_vue).$mount('#cdp-mapping-container')
		})
	})(Vue)
</script>

<?php

// Render page
// print $DDP->renderSetupPage();

// Footer
include APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
