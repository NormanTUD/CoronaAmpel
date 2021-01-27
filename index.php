<?php
	include("functions.php");
	include("header.php");

	$fragen_query = 'select id, frage, quelle, grundrechtseinschraenkung from fragen where antwort is not null or antwort != "" or red != 0 or yellow != 0 or green != 0';
	$result = rquery($fragen_query);
	while ($row = mysqli_fetch_row($result)) {
		$fragen[] = array(
			"id" => $row[0],
			"frage" => $row[1],
			"quelle" => $row[2],
			"grundrechtseinschraenkung" => $row[3]
		);
	}

	if(empty($fragen))
	{
		print "Es wurden noch keine Fragen im Adminbereich angelegt.";
		return;
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
		$quelle = $this_frage["quelle"];
		$grundrechtseinschraenkung = $this_frage["grundrechtseinschraenkung"];

		$show_ampel = get_show_ampel($frage_id);
?>
			<table>
			<tr><td colspan="2"><h2 id="frage_<?php print $frage_id; ?>"><?php print htmle($frage); ?></h2></td></tr>
				<tr>
					<td style="width: 86px"><?php print print_ampel($frage_id, 0); ?></td>
					<td>
<?php
						print htmlentities(get_antwort($frage_id));
						if($quelle) {
							print "<br><a href='".htmlentities($quelle)."'><i>Quelle</i></a>";
						}

						if($grundrechtseinschraenkung) {
							print "<br>Dadurch eingeschränkte Grundrechte: $grundrechtseinschraenkung";
						}
?>
					</td>
				</tr>
			</table>
<?php
	}
?>
</html>
