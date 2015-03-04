<?php 
/*
Template Name: paragraphr save
*/

require_once('paragraphr-app/paragraphrClass.php');
$url = "none";
/**
 * Handle the AJAX request caused by users completing their document. Call the 
 * save_JSON method of the Paragraphr class to save the JSON object of the document
 * to a file.
 * @return the file link, or -1 if there was an error
 */
if ($jsonPOST = file_get_contents("php://input")){
	$app = new Paragraphr();
	$url = $app->save_JSON($jsonPOST);
}

echo $url;
die();