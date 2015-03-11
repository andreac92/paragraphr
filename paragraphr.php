<?php 
/*
Template Name: paragraphr
*/

$doc = 0;
/**
 * Check if this is a request for a previously saved document. If so, use the ID provided
 * to retrieve the document and save it as a text string (in HTML format) to $doc.
 * $doc == -1 if there's an error in retrieval (should usually be if it doesn't exist).
 * $doc == 0 if this is a non-request viewing of the app.
 */
if ($url = $wp_query->query_vars['url']){
	require_once('paragraphr-app/paragraphrClass.php');
	$app = new Paragraphr();
	$doc = $app->get_doc($url, 'html');
} 
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="http://andrea-campos.com/wp-content/themes/personal-site/pstyle.css">
<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
<script src="http://andrea-campos.com/wp-content/themes/personal-site/jquery.ui.touch-punch.min.js"></script>
<script src="http://andrea-campos.com/wp-content/themes/personal-site/jquery.autosize.min.js"></script>
</head>
<body>
<div id="temp-head"><a href="">andrea-campos.com</a></div>
<div id="header"><a href="http://andrea-campos.com/paragraphr"><h1>paragraphr</h1></a><i class="fa fa-cog fa-2x header-cog"></i></div>
<div id="app-area">
	<?php if ($doc != -1) { ?>
	<div id="wrap">
		<h3 id="step-title">
			<?php if ($doc) { ?>
			<i class="fa fa-share"></i>share.</h3>
			<?php } else { ?>
			<i class="fa fa-pencil"></i>write.</h3>
			<?php } ?>
		<div id="writing-area" <?php if (!$doc) echo 'class="normal writing"'; ?>>
			<?php if ($doc) { 
				echo $doc;
			 } else { ?>
				<textarea id="writing-doc" class="text-font" placeholder="Write it out..."></textarea>
			<?php } ?>
		</div>
	</div>
	<div id="writing-settings">
		<div id="settings">
			<h3></h3>
		</div>
		<div class="inner">
			<div id="submit">
				<i class="fa fa-angle-double-right"></i> <span><?php echo ($doc) ? 'write another' : 'I\'m ready to edit'; ?></span> <i class="fa fa-angle-double-right"></i>
			</div>
			<?php if (!$doc) { ?>
			<div id="write-modes">
				<h4>Mode</h4>
				<div><div id="normal-mode" class="chk chked"></div> Normal view</div>
				<div><div id="focused-mode" class="chk"></div> Focused view</div>
			</div>
			<?php } ?>
			<h4>Font</h4>
			<div id="change-font">
				<select id="fontface">
					<option>Arial</option>
					<option>Courier New</option>
					<option selected="selected">Georgia</option>
					<option>Times</option>
					<option>Verdana</option>
				</select>
				<select id="fontsize">
					<option>12</option>
					<option>13</option>
					<option>14</option>
					<option selected="selected">15</option>
					<option>16</option>
					<option>17</option>
					<option>18</option>
					<option>19</option>
					<option>20</option>
					<option>21</option>
					<option>22</option>
					<option>23</option>
					<option>24</option>
				</select>
			</div>
			<div id="change-color">
				<h4>Intro Color</h4>
				<ul id="i-colors">
					<li><div id="purple" class="active-color"></div></li>
					<li><div id="blue"></div></li>
					<li><div id="green"></div></li>
					<li><div id="orange"></div></li>
					<li><div id="red"></div></li>
					<li><div id="gray"></div></li>
				</ul>
			</div>
			<h4><?php echo ($doc) ? 'Share' : 'Help'; ?></h4>
			<div id="help">
				<?php if (!$doc) { ?>
					<p>Write out your thoughts and work on getting them down "on paper". Flow and cohesion are less important at this stage, though for easier editing make sure you write in complete sentences.</p>
					<p>Separate your ideas into paragraphs. A paragraph must be separated by at least one line break (by pressing 'Enter'), though you can use more.</p>
					<p>If you tend to slow yourself down by re-reading what you have already written, choose the "Focused" mode which clears the writing space after each paragraph (after each 'Enter' press).</p>
					<p>When you are done, click the edit button above.</p>
				<?php } else { ?>
					<p>Share your writing with the world, or download a copy for yourself.
					</p><h5>Download</h5><a id="click-dl" href="#"><i class="fa fa-arrow-circle-o-down"></i> As text file.</a><a id="dl-button" href="#">Download</a>
					<p>You can also access your writing at the following URL for 60 days, after which it will be permanently deleted, so make sure it's saved before then :</p><p id='w-url'>http://andrea-campos.com/paragraphr/<?php echo $url; ?></p>
				<?php } ?>
		</div>
	</div>
</div>
<?php } else { ?>
<h4 class="error-msg">Oops! Looks like this document doesn't exist. Try writing a new one!</h4>
<?php } ?>
</body>
<script src="http://andrea-campos.com/wp-content/themes/personal-site/paragraphr.js"></script>
</html>