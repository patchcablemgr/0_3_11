<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

$ch = curl_init('https://patchcablemgr.com/public/template-catalog-data-0-3-11.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, "/etc/ssl/certs/");

$responseJSON = curl_exec($ch);
$response = json_decode($responseJSON, true);

if(curl_errno($ch)) {
	echo '<strong>Error:</strong> Unable to contact template catalog server.';
} else {
	
	$categoryArray = $response['categoryArray'];
	$templateCategoryArray = array();
	$templateArray = $response['templateArray'];
	
	foreach($templateArray as &$template){
		
		// Create templateCategoryArray
		$categoryID = $template['templateCategory_id'];
		$templateName = $template['templateName'];
		$templateID = $template['id'];
		if(!isset($templateCategoryArray[$categoryID])) {
			$templateCategoryArray[$categoryID] = array();
		}
		$templateCategoryArray[$categoryID][$templateName] = array(
			'type' => 'regular',
			'id' => $templateID
		);
		
		$template['categoryData'] = $categoryArray[$categoryID];
		
		// Convert partition data.  Necessary for transition from 0.1.3 to 0.1.4
		if($template['templatePartitionData']) {
			$template['templatePartitionData'] = json_decode($template['templatePartitionData'], true);
			
			foreach($template['templatePartitionData'] as &$face) {
				$qls->App->alterTemplatePartitionDataLayoutName($face);
				$qls->App->alterTemplatePartitionDataDimensionUnits($face);
			}
			
			$template['templatePartitionData'] = json_encode($template['templatePartitionData']);
		}
	}
	require_once $_SERVER['DOCUMENT_ROOT'].'/includes/content-build-objects.php';
}

?>
