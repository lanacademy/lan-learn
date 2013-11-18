<?php

/**
* @author Paarth Chadha
* @link nothing
* 
* Javascript can't do file reading or writing so
* this page is intended to provide that service.
* 
* It takes in two url parameters: user and keyword.
* As per the specification for the wiki portion of
* the lan academy spec, a new .xml file will be 
* created for each username. If a username is given
* that is less than 2 characters, the entries go
* into a catch-all file 'record.xml'.
*
* Note that user should not be a url parameter in
* production. The logged in user's id should be
* referenced directly from the session object.
*/


$fn = $_GET['user'] . '.xml'; //filename
if( strlen($_GET['user']) < 2){
	$fn = 'record.xml';
}
$doc = new SimpleXmlElement("<root/>");
if (file_exists($fn)){
	$doc = simplexml_load_file($fn);
}

$xml = $doc->addChild('record');

$xml->addChild('user',$_GET['user']); 
$xml->addChild('keyword',$_GET['keyword']);
$xml->addChild('time',date(DATE_RFC2822));

$doc->asXML($fn);
?>