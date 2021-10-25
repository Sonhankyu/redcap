<h4>Example Page With Headers</h4>

<?php
$url = $module->getUrl('public-page.php', true);
$module->initializeJavascriptModuleObject();
?>
<p><a href="<?=$url?>">Click here</a> for an example of a NOAUTH page.</p>

<button id='add-log-entry'>Click here to add a log entry</button>

<script>
    (function(){
        var module = <?=$module->getJavascriptModuleObjectName()?>;

        $('button#add-log-entry').click(function(){
            module.log('test log from configuration example module')
            alert("A log entry should now appear in the database.")
        })
    })()
</script>
