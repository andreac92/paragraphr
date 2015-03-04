<?php
/*
Template Name: paragraphr download
*/

require_once('paragraphr-app/paragraphrClass.php');
/**
 * Handle the AJAX request caused by users clicking the "Download" button. Call the 
 * download method of the Paragraphr class to create the downloadable file.
 * @return the file link, or -1 if there was an error
 */
if ($_SERVER["REQUEST_METHOD"] == "POST"){
	$success = '';
	if ($_POST["textfile"]) {
		$url = $_POST["textfile"];
		$app = new Paragraphr();
		$success = $app->download($url, '.txt');
	}
}
echo ($success) ? 'http://andrea-campos.com/wp-content/uploads/files_DL/paragraphr_'.$url.'.zip' : -1;
die();

?>