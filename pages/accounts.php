<?php
	$included_files = get_included_files();
	$included_files = array_map('basename', $included_files);

	if(!in_array('functions.php', $included_files)) {
		include_once('../functions.php');
	}

	if(check_page_rights(get_page_id_by_filename(basename(__FILE__)))) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann
		if(!get_setting('x11_debugging_mode')) {
		$rollen = create_rollen_array();
?>
	<div id="accounts">
		<?php print get_seitentext(); ?>
<?php
		include_once('hinweise.php');
?>
		<table>
			<tr>
				<th>Benutzer</th>
				<th>Passwort</th>
				<th>Rolle</th>
				<th>Speichern</th>
				<th>Account deaktivieren*</th>
				<th>Löschen?</th>
			</tr>
<?php
			$query = 'SELECT `v`.`user_id`, `v`.`username`, `v`.`role_id`, `v`.`dozent_id`, `v`.`enabled` FROM `view_user_to_role` `v` JOIN `users` `u` ON `u`.`id` = `v`.`user_id` ORDER BY `v`.`enabled` DESC, `v`.`username`';
			$result = rquery($query);

			while ($row = mysqli_fetch_row($result)) {
?>
				<tr>
					<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php print $GLOBALS['this_page_number']; ?>">
						<input type="hidden" name="id" value="<?php print htmlentities($row[0]); ?>" />
						<td><input type="text" name="name" value="<?php print htmlentities($row[1]); ?>" /></td>
						<td><input type="password" name="password" value="" placeholder="passwort" /></td>
						<td><?php create_select($rollen, $row[2], 'role'); ?></td>
						<td><input type="submit" value="Speichern" /></td>
<?php
						if($row[4] == "1") {
?>
							<td><input style="background-color: red;" type="submit" name="disable_account" value="Deaktivieren" /></td>
<?php
						} else {
?>

							<td><input style="background-color: green;" type="submit" name="enable_account" value="Bereits deaktiviert. Reaktivieren?" /></td>
<?php
						}
						if($GLOBALS['logged_in_user_id'] == $row[0]) {
?>
							<td>&mdash;</td>
<?php
						} else {
?>
							<td><input type="submit" name="delete" value="Löschen" /></td>
<?php
						}
?>
					</form>
				</tr>
<?php
			}
?>
			<tr>
				<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php print $GLOBALS['this_page_number']; ?>">
					<input type="hidden" name="new_user" value="1" />
					<td><input type="text" name="name" value="" /></td>
					<td><input type="password" name="password" value="" placeholder="passwort" /></td>
					<td><?php create_select($rollen, 2, 'role'); ?></td>
					<td><input type="submit" value="Speichern" /></td>
					<td>&mdash;</td>
					<td>&mdash;</td>
				</form>
			</tr>
		</table>
	</div>


	<p>* Viele Daten hängen von den Accounts ab und diese werden gelöscht, wenn der Account gelöscht wird. Daher gibt es die Möglichkeit, den Account zu deaktivieren.
	Dies verhindert das Löschen der abhängigen Datensätze und sorgt dafür, dass der Nutzer sich nicht mehr anmelden kann.</p>
<?php
		} else {
?>
			<br><i>Die Seite ist deaktiviert, weil der x11_debugging_mode aktiv ist</i>
<?php
		}
	}
?>
