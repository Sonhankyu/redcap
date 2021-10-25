<?php
namespace ExternalModules;

$data = json_decode(file_get_contents('php://input'), true);

if($data['noAuth']){
	define('NOAUTH', true);
}

$_POST['redcap_csrf_token'] = $data['redcap_csrf_token'];

require_once __DIR__ . '/../../redcap_connect.php';

$framework = ExternalModules::getFrameworkInstance($_GET['prefix']);
$framework->logAjax($data);

echo 'success';
