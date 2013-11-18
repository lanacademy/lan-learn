<?php
/**
* @author Paarth Chadha
*
* PHP function to handle note loading and
* saving.
*
*
*
*/

$name = $_GET['user'];
$ext = '-notes.xml';

if($_GET['method'] == 'saveNotes'){
	$fn = $name . $ext;
	if( strlen($name) < 2){
		$fn = 'notes.xml';
	}
	$doc = new SimpleXmlElement("<root/>");

	$notedata = json_decode($_GET['notes']);

	var_dump($notedata);
	echo "hello\n";

	var_dump($notedata->notes);

	foreach($notedata->notes as $n){
		$xml = $doc->addChild('note');
		$xml->addChild('title',$n->title);
		$xml->addChild('text',$n->text);
	}

	$doc->asXML($fn);
}
if($_GET['method'] == 'getNotes'){
	$fn = $name . $ext;

	$noteset = array('notes' => array());
	if(file_exists($fn)){
		$doc = simplexml_load_file($fn);
		$notes = $doc->root->children();
		foreach($doc as $n){
			array_push($noteset["notes"], array(
				"title" => current($n->title), 
				"text" => current($n->text)));
		}
	}else{
		//leave it be.
	}
	echo json_encode($noteset);
}



?>
