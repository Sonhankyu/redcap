<?php namespace ExternalModules;
require_once __DIR__ . '/../redcap_connect.php';
if(!ExternalModules::isCommandLine()){
    exit('This file is only executable on the command line.');
}

echo ExternalModules::getPHPUnitPath();