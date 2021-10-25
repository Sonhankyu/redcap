<?php
global $format, $returnFormat, $post;

# get all the records to be exported
$content = delDags();

# Logging
Logging::logEvent("", "redcap_data_access_groups", "MANAGE", PROJECT_ID, "group_id in (" . implode(", ", $post['dags']) . ")", "Delete DAGs (API$playground)");

# Send the response to the requestor
RestUtility::sendResponse(200, $content, $format);


function delDags()
{
	global $post, $lang, $Proj;

	if(!isset($post['dags']) || empty($post['dags']) || !is_array($post['dags'])) {
		die(RestUtility::sendResponse(400, $lang['api_104']));
	}

	// Begin transaction
	db_query("SET AUTOCOMMIT=0");
	db_query("BEGIN");

	$count = 0;
	$errors = array();

    $groups = $Proj->getUniqueGroupNames();
	foreach($post['dags'] as $unique_group_name)
	{
		if ($unique_group_name != '' && $Proj->uniqueGroupNameExists($unique_group_name))
		{
            $dagId = array_search($unique_group_name, $groups);
			$count += DataAccessGroups::delGroup(PROJECT_ID, $dagId);
		}
		else
		{
			$errors[] = $unique_group_name;
		}
	}

	if (!empty($errors)) {
		db_query("ROLLBACK");
		db_query("SET AUTOCOMMIT=1");
		die(RestUtility::sendResponse(400, $lang['api_157'] . " " . implode(", ", $errors)));
	}

	db_query("COMMIT");
	db_query("SET AUTOCOMMIT=1");

	return $count;
}
