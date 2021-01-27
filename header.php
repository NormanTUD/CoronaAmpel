<?php
	include_once("functions.php");

	if($GLOBALS['reload_page']) {
		header("Refresh:0");
	}

	if(!$page_title) {
		$page_title = 'Corona-Ampel';
	}
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="favicon.ico" type="image/x-icon" />

		<meta name="description" content="Wartungstabelle">
		<meta name="keywords" content="Wartungstabelle">
		<meta name="author" content="Norman Koch">
		<title><?php print htmlentities($page_title); ?></title>
		<meta charset="UTF-8" />

		<link rel="stylesheet" href="data/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="data/style.php">
		<link rel="stylesheet" href="data/spin.css">
		<link rel="stylesheet" href="data/dark.css">
		<script src="data/sweetalert.min.js"></script>
		<script src="data/jquery-1.12.4.js"></script>
		<script src="data/jquery-ui.js"></script>
		<script src="data/main.js"></script>
		<script src="data/jscolor.js"></script>
	</head>
<body>
