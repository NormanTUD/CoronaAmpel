<?php
	$this_start = time();
	include("functions.php");

	foreach (array(
			array("hint", "blue"),
			array("error", "red"),
			array("right_issue", "red"),
			array("warning", "orange"),
			array("message", "blue"),
			array("easter_egg", "hotpink"),
			array("success", "green")
		) as $msg) {
		show_output($msg[0], $msg[1]);
	}

	include("query_analyzer.php");
	$this_end = time();
	$duration = $this_end - $this_start;
	print "\n<!-- duration: $duration s -->";
?>
