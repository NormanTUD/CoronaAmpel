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
		if(!get_setting('x11_debugging_mode')) {
?>
			<table class="invisible_table">
				<tr>
					<td class="invisible_td"><img src="i/id.svg"></td><td class="invisible_td">&nbsp;<i>Hier kannst du dein eigenes Passwort ändern.</i></td>
				</tr>
			</table>
			<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php print $GLOBALS['this_page_number']; ?>">
				<table>
					<tr>
						<th>Benutzer</th>
						<th>Passwort</th>
						<th>Passwort erneut eingeben</th>
						<th>Speichern</th>
					</tr>
<?php
					$query = 'SELECT `user_id`, `username`, `role_id`, `dozent_id`, `institut_id` FROM `view_user_to_role` WHERE `user_id` = '.esc($GLOBALS['logged_in_user_id']);
					$result = rquery($query);

					while ($row = mysqli_fetch_row($result)) {
?>
						<tr>
							<input type="hidden" name="change_own_data" value="1" />
							<td><?php print htmlentities($row[1]); ?></td>
							<td><input type="password" name="password" value="" /></td>
							<td><input type="password" name="password_repeat" value="" /></td>
							<td><input type="submit" value="Speichern" /></td>
						</tr>
<?php
					}
?>
				</table>
			</form>
<?php
		} else {
?>
			<br><i>Die Seite ist deaktiviert, weil der x11_debugging_mode aktiv ist</i>
<?php
		}
	}
?>
	</div>
