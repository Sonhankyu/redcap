<?php

require_once dirname(dirname(__FILE__)) . '/Config/init_project.php';

//
//// Validate PAGE
//if (isset($_GET['page']) && $_GET['page'] != '' && (($status == 0 && !isset($Proj->forms[$_GET['page']])) || ($status > 0 && !isset($Proj->forms_temp[$_GET['page']])))) {
//    if ($isAjax) {
//        exit("ERROR!");
//    } else {
//        redirect(APP_PATH_WEBROOT . "index.php?pid=" . PROJECT_ID);
//    }
//}
//// If attempting to edit a PROMIS CAT, which is not allowed, redirect back to Form list
//list ($isPromisInstrument, $isAutoScoringInstrument) = PROMIS::isPromisInstrument(isset($_GET['page']) && $_GET['page'] != '' ? $_GET['page'] : '');
//if (isset($_GET['page']) && $_GET['page'] != '' && $isPromisInstrument) {
//    redirect(APP_PATH_WEBROOT . "ImageUpload/dicomImageUpload.php?pid=$project_id");
//}

include APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

$pid = $_GET['pid'];
$subj_id = $_GET['id'];

// Shared Library flag to avoid duplicate loading is reset here for the user to load a form
$_SESSION['import_id'] = '';

renderPageTitle("<table cellpadding=0 cellspacing=0 width='100%'>
                        <tr>
                         <td valign='top'>
                            Image Upload
                         </td>
				        </tr>
                    </table>");


//$visitName = $Proj->eventInfo[$_GET['event_id']['name_ext']];

print '<div>
        <form method="post">
            <select class="x-form-text x-form-field">
                <option>-- select --</option>';
                foreach ($Proj->eventInfo as $key=>$value) {
                    print '<option>';
                    print $value['name_ext'];
                    print'</option>';
                }
print      '</select>
            <input type="button" class="jqbutton" value="Image Upload">
        </form>
       </div>';

foreach ($Proj->eventInfo as $key=>$value) {

    $subj_name = $value['name_ext'];

    $sql = "SELECT series.series_iuid, series.series_desc
        FROM pacsdb.series AS series 
        INNER JOIN asan_pacs redcap 
        INNER JOIN asan_pacs_series rseries ON redcap.idx = rseries.asan_pacs_idx AND series.series_iuid = rseries.series_iuid
        WHERE redcap.project_id = '{$pid}' AND redcap.subject_id = '{$subj_id}' AND redcap.event_id = '{$key}'";

    $result = db_query($sql);
    while ($row = db_fetch_array($result)) {
        $seriesDesc_arr[] = RCView::div(array('style'=>'list-style: none;') , $row['series_desc']);
        $seriesUID_arr[] = $row['series_iuid'];

        $seriesList = implode("", $seriesDesc_arr);
    }

    unset($seriesDesc_arr, $seriesUID_arr, $series_arr);

    $visitList_arr[] = RCView::tr(array(),
        RCView::td(array(), $value['name_ext']) .
        RCView::td(array('style'=>'font-weight: normal;'),
            RCView::div(array('id'=>'seriesList'), $seriesList
            )
        )
    );
    $visitList = implode("", $visitList_arr);
    $seriesList = "";
}

print RCView::div(array('id'=>'dcmImageList'),
        RCView::table(array('class'=>'imageTable labelrc col-12','style'=>'margin-top:10px;'),
            RCView::thead(array('style'=>''),
                RCView::tr(array(),
                    RCView::td(array('style'=>'width: 20%;'), "Visit") .
                    RCView::td(array(), "Series List")
                )
            ) .
            RCView::tbody(array(), $visitList)
        )
    );


?>

<style>
    .imageTable {
        max-width: 60%;
    }
    .imageTable td {
        padding: 5px;
        border: 1px solid black;
    }
    .imageTable thead {
        text-align: center;
        font-size:15px;
    }
    .imageTable tbody {
        vertical-align: top;
    }
</style>
