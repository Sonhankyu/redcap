<?php


define("NOAUTH", true);
if (isset($_GET['pid'])) {
	include_once dirname(dirname(__FILE__)) . '/Config/init_project.php';
} else {
	include_once dirname(dirname(__FILE__)) . '/Config/init_global.php';
}

$replaceSeparator = "|-RC-COLON-|";
$smartVarsInfo = Piping::getSpecialTagsInfo();

// Build list of all action tags
$smart_var_descriptions = 
	RCView::tr(array(),
		RCView::td(array('rowspan'=>'2', 'class'=>'wrap', 'style'=>'width:300px;background-color:#e5e5e5;padding:7px;font-weight:bold;border:1px solid #bbb;border-bottom:0;position:sticky;top:0;'),
			$lang['piping_25']
		) .
		RCView::td(array('rowspan'=>'2', 'style'=>'background-color:#e5e5e5;padding:7px;font-weight:bold;border:1px solid #bbb;border-bottom:0;border-left:0;position:sticky;top:0;'),
			$lang['global_20']
		) .
		RCView::td(array('colspan'=>'2', 'style'=>'width:300px;text-align:center;background-color:#e5e5e5;padding:7px;font-weight:bold;border:1px solid #bbb;border-bottom:0;border-left:0;position:sticky;top:0;'),
			$lang['piping_26']
		)
	) .
	RCView::tr(array(),
		RCView::td(array('style'=>'width:180px;text-align:center;background-color:#e5e5e5;padding:7px;border:1px solid #bbb;border-bottom:0;border-left:0;position:sticky;top:0;'),
			$lang['piping_27']
		) .
		RCView::td(array('style'=>'width:120px;text-align:center;background-color:#e5e5e5;padding:7px;border:1px solid #bbb;border-bottom:0;border-left:0;position:sticky;top:0;'),
			$lang['piping_28']
		)
	);
foreach ($smartVarsInfo as $catname=>$attr0) 
{
	// Add more video and text for Smart Charts/Functions/Tables
	if ($catname == $lang['global_181']) {
		$catname =  RCView::div(array('class'=>'float-right'), "<a onclick=\"window.open('".CONSORTIUM_WEBSITE."videoplayer.php?video=smart_charts01.mp4&referer=".SERVER_NAME."&title={$lang['training_res_104']}','myWin','width=1050, height=800, toolbar=0, menubar=0, location=0, status=0, scrollbars=1, resizable=1');\" href=\"javascript:;\" style=\"font-size:12px;text-decoration:underline;font-weight:normal;\"><i class=\"fas fa-film mr-1\"></i>{$lang['training_res_107']} (14 {$lang['calendar_12']})</a>") .
			        RCView::div(array('class'=>'float-left'), $lang['global_181'] . RCView::br() . RCView::span(array('class'=>'font-weight-normal fs12'), $lang['global_230']));
	}
	// Category header
	$smart_var_descriptions .=
			RCView::tr(array(),
				RCView::td(array('colspan'=>'4', 'class'=>'header', 'style'=>'padding:10px;font-size:14px;color:#800000;'),
					$catname
				)
			);
    // Loop through all items in this category
	foreach ($attr0 as $tag=>$attr) 
	{
		$description = array_shift($attr);
		$examplesCount = count($attr);
		$example = array_shift($attr);
		// Make the parameters that follow the colon a lighter color
		$tag = str_replace(":", $replaceSeparator, $tag);
		$tagParts = explode($replaceSeparator, $tag);
		$tag = array_shift($tagParts);
		$tag = "<span style='font-size:110% !important;' class='nowrap'>$tag</span>";
		if (count($tagParts) > 0) {
			$tag .= "<span style='color:#ca8a00;'>$replaceSeparator" . implode($replaceSeparator, $tagParts) . "</span>";
			// Make "Custom Text" a different color text
			if (strpos($tagParts[0], "Custom Text") !== false || strpos($tagParts[1], "Custom Text") !== false) {
				$tag = str_replace($replaceSeparator . "Custom Text", "<span style='color:rgba(128, 0, 0, 0.70);'>" . $replaceSeparator . "Custom Text</span>", $tag);
			}
		}
		if (count($tagParts) > 1) {
			$tag = str_replace($replaceSeparator . $tagParts[1], "<span style='color:rgba(128, 0, 0, 0.70);'>" . $replaceSeparator . $tagParts[1] . "</span>", $tag);
			if (strpos($tagParts[1], "parameters") !== false || strpos($tagParts[2], "parameters") !== false) {
				$tag = str_replace($replaceSeparator . "parameters", "<span style='color:rgba(1, 84, 187, 0.70);'>" . $replaceSeparator . "parameters</span>", $tag);
			}
			if (strpos($tagParts[0], "_____") !== false) {
				$tag = str_replace($replaceSeparator . $tagParts[1], "<span style='color:rgba(1, 84, 187, 0.70);'>" . $replaceSeparator . $tagParts[1] . "</span>", $tag);
			}
		}
        // Add spaces after any commas
		$tag = str_replace(",", ", ", $tag);
		// Put some spacing around colons for easier reading
		$tag = str_replace($replaceSeparator, "<span style='margin:0 2px;'>:</span>", $tag);
		// Output row
		$example[0] = implode("-<wbr>", explode("-", $example[0]));
		$example[0] = str_replace("][", "<span class='nowrap'>][</span>", $example[0]);
		$smart_var_descriptions .=
			RCView::tr(array(),
				RCView::td(array('rowspan'=>$examplesCount, 'style'=>'width:300px;line-height:1.3;background-color:#f5f5f5;color:green;padding:7px;font-weight:bold;border:1px solid #ccc;border-bottom:0;'),
					RCView::span(array('style'=>'margin-right:1px;'), "[") . $tag . RCView::span(array('style'=>'margin-left:1px;'), "]")
				) .
				RCView::td(array('rowspan'=>$examplesCount, 'style'=>'font-size:12px;background-color:#f5f5f5;padding:7px;border:1px solid #ccc;border-bottom:0;border-left:0;'),
					$description
				) .
				RCView::td(array('style'=>'width:180px;font-size:11px;background-color:#f5f5f5;padding:7px;border:1px solid #ccc;border-bottom:0;border-left:0;color:#666;'),
					$example[0]
				) .
				RCView::td(array('class'=>'wrap','style'=>'word-break: break-word;width:120px;font-size:11px;background-color:#f5f5f5;padding:7px;border:1px solid #ccc;border-bottom:0;border-left:0;color:#666;'),
					$example[1]
				)
			);
		// Add extra examples
		foreach ($attr as $example) {
			$example[0] = implode("-<wbr>", explode("-", $example[0]));
			$example[0] = str_replace("][", "<span class='nowrap'>][</span>", $example[0]);
			$smart_var_descriptions .=
				RCView::tr(array(),
					RCView::td(array('style'=>'width:180px;font-size:11px;background-color:#f5f5f5;padding:7px;border:1px solid #ccc;border-bottom:0;border-left:0;color:#666;'),
						$example[0]
					) .
					RCView::td(array('class'=>'wrap','style'=>'word-break: break-word;width:120px;font-size:11px;background-color:#f5f5f5;padding:7px;border:1px solid #ccc;border-bottom:0;border-left:0;color:#666;'),
						$example[1]
					)
				);
		}
	}
}

// Content
$content  = (!$isAjax ? '' :
				RCView::div(array('class'=>'clearfix'),
					RCView::div(array('style'=>'color:green;font-size:18px;font-weight:bold;float:left;padding:10px 0;'),
						"[<i class='fas fa-bolt fa-xs' style='margin:0 1px;'></i>] " . $lang['global_146']
					) .
					RCView::div(array('style'=>'text-align:right;float:right;'),
						RCView::a(array('href'=>PAGE_FULL, 'target'=>'_blank', 'style'=>'text-decoration:underline;'),
							$lang['survey_977']
						)
					)
				)
			) . 
			// Instructions
			RCView::div(array('style'=>'font-weight:bold;font-size:14px;margin:10px 0 5px;'),
				$lang['design_737']
			) .
			RCView::div(array('style'=>''),
				$lang['design_738']
			) .
			RCView::div(array('style'=>'font-weight:bold;font-size:14px;margin:20px 0 5px;'),
				$lang['design_739']
			) .
			RCView::div(array('style'=>''),
				$lang['design_740'] .
				RCView::div(array('style'=>'margin-top:10px;'),
					$lang['design_746'] .
					RCView::ul(array('style'=>'margin-top:5px;'),
						RCView::li(array('style'=>''), $lang['design_743']) .
						RCView::li(array('style'=>''), $lang['design_744']) .
						RCView::li(array('style'=>''), $lang['design_745'])
					)
				)
			) .
			RCView::div(array('style'=>'font-weight:bold;font-size:14px;margin:20px 0 5px;'),
				$lang['design_741']
			) .
			RCView::div(array('style'=>''),
				$lang['design_742'] .
				RCView::div(array('style'=>'margin-top:10px;'),
					$lang['design_752'] .
					RCView::ul(array('style'=>'margin-top:5px;'),
						RCView::li(array('style'=>''), $lang['design_749']) .
						RCView::li(array('style'=>''), $lang['design_767']) .
						RCView::li(array('style'=>''), $lang['design_751'])
					)
				)
			) .
			RCView::div(array('style'=>'margin:10px 0 5px;'),
				$lang['design_766']
			) .
			(!(defined("SUPER_USER") && SUPER_USER) ? '' :
				RCView::div(array('style'=>'margin:10px 0 5px;'),
					$lang['design_765']
				)
			) .
			RCView::div(array('style'=>'font-weight:bold;font-size:14px;margin:20px 0 5px;'),
				$lang['piping_39']
			) .
			RCView::div(array('style'=>''),
				$lang['piping_40']
			) .
			// Table
			RCView::div(array('style'=>''),
				RCView::table(array('id'=>'smart_var_table', 'style'=>'table-layout:fixed;margin-top:20px;width:100%;border-bottom:1px solid #ccc;line-height:1.2;'),
					$smart_var_descriptions
				)
			);

if ($isAjax) {	
	// Return JSON
	print json_encode_rc(array('content'=>$content, 'title'=>$lang['global_146']));
} else {
	$objHtmlPage = new HtmlPage();
	$objHtmlPage->PrintHeaderExt();
	?><style type="text/css">
        #pagecontainer { max-width:1100px; }
        table#smart_var_table td { font-size: 105% !important; };
    </style><?php
	print 	RCView::div(array('class'=>'clearfix'),
				RCView::div(array('style'=>'font-size:18px;font-weight:bold;float:left;padding:10px 0 0;color:green;'),
					"[<i class='fas fa-bolt fa-xs' style='margin:0 1px;'></i>] " . $lang['global_146']
				) .
				RCView::div(array('style'=>'text-align:right;float:right;'),
					RCView::img(array('src'=>'redcap-logo.png'))
				)
			) .
			RCView::div(array('style'=>'margin:10px 0;font-size:13px;'),
				$content
			);
	$objHtmlPage->PrintFooterExt();
}
