<?php
	$included_files = get_included_files();
	$included_files = array_map('basename', $included_files);

	if(!in_array('functions.php', $included_files)) {
		include_once('../functions.php');
	}

	if(check_page_rights(get_page_id_by_filename(basename(__FILE__)))) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann
?>
	<div id="accounts">
		<?php print get_seitentext(); ?>
<?php
		include_once('hinweise.php');

		$fragen = array();
		$fragen_query = 'select id, frage from fragen';
		$result = rquery($fragen_query);
		while ($row = mysqli_fetch_row($result)) {
			$fragen[] = array("id" => $row[0], "frage" => $row[1]);
		}


		foreach ($fragen as $this_frage) {
			$frage_id = $this_frage["id"];
			$frage = $this_frage["frage"];

			$show_ampel = get_show_ampel($frage_id);

?>
			<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php print $GLOBALS['this_page_number']; ?>">
				<input name="frage_id" type="hidden" value="<?php print htmle($frage_id); ?>" />
				<table>
				<tr><td colspan="2"><input name="frage" type="text" value="<?php print htmle($frage); ?>" /></td><td colspan="2"><input type="checkbox" name="show_ampel" <?php if ($show_ampel) { print "checked='CHECKED'"; } ?> />Ampel anzeigen?</td></tr>
					<tr><td><?php print print_ampel($frage_id, 1); ?></td><td><textarea width="200" height="100" name="antwort"><?php print htmlentities(get_antwort($frage_id)); ?></textarea></td><td><input type="submit" value="Speichern" /></td><td><input type="submit" name="delete_frage" value="Löschen" /></td></tr>
				</table>
			</form><br>
<?php
		}
?>
		<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php print $GLOBALS['this_page_number']; ?>">
			<input name="neue_frage" type="hidden" value="1" />
			<table>
			<tr><td colspan="2"><input name="frage" placeholder="Neue Frage" type="text" value="" /></td><td><input type="checkbox" checked="CHECKED" name="show_ampel" />Ampel anzeigen?</td></tr>
				<tr><td><?php print print_ampel($frage_id, 1, 1); ?></td><td><textarea placeholder="Neue Antwort" width="200" height="100" name="antwort"></textarea></td><td><input type="submit" value="Speichern" /></td></tr>
			</table>
		</form><br>
	</div>
<?php
	}
?>
