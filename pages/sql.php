<?php
	$included_files = get_included_files();
	$included_files = array_map('basename', $included_files);

	if(!in_array('functions.php', $included_files)) {
		include_once('../functions.php');
	}

	if(check_page_rights(get_page_id_by_filename(basename(__FILE__)))) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann
?>
		<img class="skull" src="i/skull.svg" />
		<img class="skull" src="i/skull.svg" />
		<img class="skull" src="i/skull.svg" />
		<i class="class_red">VORSICHTIG BENUTZEN!!! ÄNDERT DIE DATENBANK!!!!. <a href="admin.php?page=<?php print get_page_id_by_filename("backup.php"); ?>">Backup machen!!!</a></i>.
		<img class="skull" src="i/skull.svg" />
		<img class="skull" src="i/skull.svg" />
		<img class="skull" src="i/skull.svg" />
<?php
		if(!get_setting("x11_debugging_mode")) {
?>
			<form method="post" action="<?php print $_SERVER['REQUEST_URI'] ?>">
				<textarea name="sqlinserter" style="width: 1000px; height: 500px;"></textarea>
				<button style="background-color: red" type="submit">
					<img class="skull" src="i/skull.svg" />
					<img class="skull" src="i/skull.svg" />
					<img class="skull" src="i/skull.svg" />
					Ausführen
					<img class="skull" src="i/skull.svg" />
					<img class="skull" src="i/skull.svg" />
					<img class="skull" src="i/skull.svg" />
				</button>
			</form>
<?php
		} else {
?>
			<br><i>Die Seite ist deaktiviert, weil der x11_debugging_mode aktiv ist</i>
<?php
		}
	}
?>
