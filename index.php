<?php
	include("functions.php");

	$fragen = array();
	$fragen_query = 'select id, frage from fragen where antwort is not null or antwort != "" or red != 0 or yellow != 0 or green != 0';
	$result = rquery($fragen_query);
	while ($row = mysqli_fetch_row($result)) {
		$fragen[] = array("id" => $row[0], "frage" => $row[1]);
	}


	foreach ($fragen as $this_frage) {
		$frage_id = $this_frage["id"];
		$frage = $this_frage["frage"];

		print "<a href='#frage_$frage_id'>".htmle($frage)."</a><br>";
	}
?>
	<br>
<?php

	foreach ($fragen as $this_frage) {
		$frage_id = $this_frage["id"];
		$frage = $this_frage["frage"];

		$show_ampel = get_show_ampel($frage_id);
?>
			<table>
			<tr><td colspan="2"><h2 id="frage_<?php print $frage_id; ?>"><?php print htmle($frage); ?></h2></td></tr>
				<tr><td style="width: 86px"><?php print print_ampel($frage_id, 0); ?></td><td><?php print htmlentities(get_antwort($frage_id)); ?></td></tr>
			</table>
<?php
	}
?>
