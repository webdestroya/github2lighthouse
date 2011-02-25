<?php

define('GITHUB_USER', "<USERNAME>");
define('GITHUB_TOKEN', "<TOKEN>");
define('GITHUB_PROJECT', "<PROJECT>");

define('LH_TOKEN', "<LH_TOKEN>");
define('LH_DOMAIN', "<LH_SUBDOMAIN>");
define('LH_PROJECT_ID', "<LH_PROJECTID>");




function setup_curl_github($state) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://github.com/api/v2/json/issues/list/".GITHUB_USER."/".GITHUB_PROJECT."/$state");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, GITHUB_USER."/token:".GITHUB_TOKEN);
	
	$resp = curl_exec($ch);
	curl_close($ch);
	return $resp;
}


function create_lh_issue($params) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://".LH_DOMAIN.".lighthouseapp.com/projects/".LH_PROJECT_ID."/tickets.json");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, LH_TOKEN.":x");
	curl_setopt($ch, CURLOPT_POST, true);
	
	$paramdata = "<?xml version=\"1.0\" encoding=\"UTF-8\"?"."><ticket>";
	foreach($params as $k => $v) {
		$paramdata .= "<$k>".htmlspecialchars($v, ENT_QUOTES)."</$k>";
	}
	$paramdata .= "</ticket>";
	
	curl_setopt($ch, CURLOPT_POSTFIELDS, $paramdata);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: text/xml',
	));
	
	$resp = curl_exec($ch);
	curl_close($ch);
	return $resp;
}

$github_types = array("open", "closed");
$lighthouse_types = array(
	'open'=>"open",
	'closed'=>"resolved",
);

foreach($github_types as $ghtype) {

	$issuelist = json_decode(setup_curl_github($ghtype));

	if( count($issuelist->issues)>0) {
		foreach($issuelist->issues as $issue) {
			//
			$params = array(
				"title" => $issue->title,
				"body" => $issue->body,
				"state" => $lighthouse_types[$ghtype],
				"tag" => implode(",", $issue->labels),
			);
			echo create_lh_issue($params);
			//print_r($params);
		}
	}

}
