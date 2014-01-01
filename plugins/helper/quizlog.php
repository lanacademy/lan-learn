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

$plugin_path = dirname(dirname(dirname(__FILE__))); //yolo

session_start();

//$fn = $_GET['user'] . '.xml'; //filename
if(isset($_SESSION['authed']) && $_SESSION['authed'] && strlen($_GET['score']) > 0 && strlen($_GET['page']) > 0) {
		//$fn = 'record.xml';

	$user = $_SESSION['username'];

	//$doc = new SimpleXmlElement("<root/>");
	//if (file_exists($fn)){
	//	$doc = simplexml_load_file($fn);
	//}

	$score = $_GET['score'];
    $page = $_GET['page'];
    $coursetitle = substr($page, strpos($page, "|") + 2, strlen($page));
    $pagetitle = substr($page, 0, strpos($page, "|") - 1);
	$data = "[SQZ],";
	$data = $data . date('Y/m/d H:i:s');
	$data = $data . "," . $coursetitle . "," . $pagetitle . "," . $score . "\n";

	if (file_exists($plugin_path . '/log/' . $user . '.log')) {
	    $data = file_get_contents($plugin_path . '/log/' . $user . '.log') . $data;
	}
	file_put_contents($plugin_path . '/log/' . $user . '.log', $data);
}

session_write_close();

// At this point the basic XML structure has been
// either created or loaded. We will now add a new
// record.

//$xml = $doc->addChild('record');

//$xml->addChild('user',$_GET['user']); 
//$xml->addChild('keyword',$_GET['keyword']);
//$xml->addChild('time',date(DATE_RFC2822));

//$doc->asXML($fn);
?>
