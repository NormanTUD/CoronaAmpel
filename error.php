<?php
	$php_start = microtime(true);
	if(file_exists('new_setup')) {
		include('setup.php');
		exit(0);
	}
	$page_title = "Vorlesungsverzeichnis TU Dresden";
	$filename = 'admin.php';
	include("header.php");
?>
	<div id="mainindex">
		<a href="admin.php" border="0"><img alt="Link zur Startseite" src="logo.png" /></a>
		<h2>Fehler</h2>
<?php
		$status_code = $_SERVER['REDIRECT_STATUS'];
		if($status_code) {
?>
			Es ist ein Fehler aufgetreten. Der Status-Code lautet <?php print htmlentities($status_code); ?>.
<?php
		} else {
?>
			Es ist ein Fehler aufgetreten.
<?php
		}
	include("footer.php");
?>
