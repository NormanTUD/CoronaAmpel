<?php
	$GLOBALS['php_start'] = microtime(true);
	if(file_exists('new_setup')) {
		include('setup.php');
		exit(0);
	}
	$page_title = "Coronaampel";
	$filename = 'admin.php';
	$GLOBALS['adminpage'] = 1;
	include("selftest.php");
	include("header.php");

	if(!$GLOBALS['logged_in']) {
?>
		<div id="main">
<?php
			if(get_setting('x11_debugging_mode')) {
?>
				<img id="mainlogo" alt="Link zur Startseite" src="logo.png" />
<?php
			} else {
?>
				<a href="admin.php"><img id="mainlogo" alt="Link zur Startseite" src="logo.png" /></a>
<?php
			}
?>
			<div id="wrapper" style="text-align: center">
			<div style="width: auto; text-align: center; display: inline-block;">
<?php
				if($GLOBALS['logged_in_was_tried']) {
					if(get_post('username') || get_post('password')) {
						sleep(3);
?>
						<span style="color: red;">Das Passwort war falsch</span><br />
<?php
					} else {
?>
						<span style="color: red;">Passwort darf nicht leer sein.</span><br />
<?php
					}
				}
?>
				<table>
					<tr>
						<td class="invisible_td">
							<img height=150 src="i/user.svg" />
						</td>
						<td class="invisible_td">
							<form method="post">
								<input type="hidden" name="try_login" value="1" />
								<div style="height: 10px;"></div>
<?php
								if(get_single_value_from_query('select count(*) from users where enabled = "1"') > 1) {
?>
									<select name="username" style="width: 200px;">
<?php
										$query = 'select username from users';
										$result = rquery($query);
										$selected = '';

										while($row = mysqli_fetch_row($result)) {
											$username = $row[0];
											if(get_post('username') == $username) {
												$selected = ' selected ';
											}
?>
											<option <?php print $selected; ?> value="<?php print htmle($username); ?>"><?php print htmle($username); ?></option>
<?php
										}
?>
									</select>
<?php
								} else {
									$username = get_single_value_from_query("select username from users where enabled = '1'");
?>
									<input type="hidden" name="username" value="<?php print htmlentities($username); ?>" />
									<?php print $username; ?>
<?php
								}
?>
								<div style="height: 10px;"></div>
								<input type="password" style="width: 95%;" name="password" placeholder="Passwort" />
								<div style="height: 20px;"></div>
								<input type="submit" value="Anmelden" />
							</form>
						</td>
						<td class="invisible_td">
							<img height=150 src="i/right_issue.svg" />
						</td>
					<tr>
				</table>
			</div>
			</div>
<?php
			$GLOBALS['end_html'] = 0;
?>
		</body>
	</html>
<?php
	} else {
		$dozent_name = htmlentities($GLOBALS['logged_in_data'][1]);
		if(!preg_match('/\w{2,}/', $dozent_name)) {
			$dozent_name = htmlentities($GLOBALS['logged_in_data'][1]);
		}
		if(!$GLOBALS['user_role_id'][0]) {
			$dozent_name = htmlentities($GLOBALS['logged_in_data'][1]).' <span class="class_red">!!! Ihr Account hat keine ihm zugeordnete Rolle! !!!</span>';
		}
?>
		<div id="main">
<?php
			if(get_setting('x11_debugging_mode')) {
?>
				<img id="mainlogo" alt="Link zur Startseite" src="logo.png" />
<?php
			} else {
?>
				<a href="admin.php"><img id="mainlogo" alt="Link zur Startseite" src="logo.png" /></a>
<?php
			}
?>
			<span class="welcome_text">Willkommen zur Coronaampel, <i><?php print $dozent_name; ?></i>!</span>
<?php
			if((date('d') == 4 && date('m') == 10 || get_get('geburtstag') == 42)) {
?>
				<br><span class="rainbow" style="font-size: 40px;"><img width=40 src="i/birthday.svg" />Happy Birthday, Lennart! Arbeite nicht so viel.</span>
<?php
			}

			if(!get_setting("x11_debugging_mode")) {
?>
				<img alt="Platzhalter" src="empty.gif" width="50" height=1 />
				<a class="pseudobutton_red" href="logout.php">Abmelden</a>
<?php
			} else {
?>
				<span class="class_red">x11-Debugging aktiv</span>
<?php
			}
?>
<?php
			$subconscious_data = array(
				"Die Software ist perfekt",
				"Du willst nie wieder Änderungen an der Software",
				"Du willst mir eine Pizza ausgeben"
			);
?>
			<span style="background-color: white; color: #FAFCFB; -ms-user-select: none; user-select: none; -webkit-user-select: none; -moz-user-select: none;"><?php print $subconscious_data[array_rand($subconscious_data)]; ?></span><!-- Ein Experiment, ob unterbewusste Beeinflussung klappt :-) -->

<?php
			if($GLOBALS['user_role_id'] == 1) {
				$df = sprintf("%0.2f", disk_free_space($_SERVER['DOCUMENT_ROOT']) / 1024 / 1024 / 1024);
				if($df <= 1) {
					print("<br /><span class='class_red'>Warnung: nur noch $df GB freier Speicher auf der Festplatte!</span>");
				}
			}
?>
			<div style="height: 5px;"></div>
				<ul class="topnav">
					<li><a href="admin.php" <?php print (get_get('page') || get_get('show_items')) ? '' : 'class="selected_tab"'; ?>><?php print (get_get('page') || get_get('show_items')) ? '' : '&rarr; '; ?>Willkommen!</a></li>
<?php
					if(count($GLOBALS['pages'])) {
						foreach ($GLOBALS['pages'] as $this_page) {
							# 0	   1	   2		3		    4
							#`name`, `file`, `page_id`, `show_in_navigation`, `parent`
							if($this_page[3]) {
								if($this_page[1]) { # Kein Dropdown
									if(!$this_page[4]) {
										if($this_page[2] == get_get('page') || $this_page[2] == get_get('show_items')) {
											print "<li class='selected_tab'><a href='admin.php?page=".$this_page[2]."'>&rarr; $this_page[0]</a></li>\n";
										} else {
											print "<li><a href='admin.php?page=".$this_page[2]."'>$this_page[0]</a></li>\n";
										}
									}
								} else { # Dropdown
									$subnav_data = print_subnavigation($this_page[2]);
									if($subnav_data[0]) {
?>
										<li class='selected_tab'><a href='admin.php?show_items=<?php print $this_page[2];?>'>&rarr; <?php print $this_page[0]; ?> &darr;</a><?php print $subnav_data[1]; ?></li>
<?php
									} else {
										if($this_page[2] == get_get('page') || $this_page[2] == get_get('show_items')) {
?>
											<li class="dropdown selected_tab"><a href='admin.php?show_items=<?php print $this_page[2];?>'>&rarr; <?php print $this_page[0]; ?> &darr;</a><?php print $subnav_data[1]; ?></li>
<?php
										} else {
?>
											<li class="dropdown"><a href='admin.php?show_items=<?php print $this_page[2];?>'><?php print $this_page[0]; ?> &darr;</a><?php print $subnav_data[1]; ?></li>
<?php
										}
									}
								}
							}
						}
					} else {
						print "<h2 class='class_red'>Fehler beim Holen der Seiten!</h2>";
					}
?>
				</ul>
<?php
			print "<div id='main_notices'>";
			foreach (array(
					array("success", "green"),
					array("hint", "blue"),
					array("error", "red"),
					array("mysql_error", "red"),
					array("right_issue", "red"),
					array("warning", "orange"),
					array("mysql_warning", "orange"),
					array("debug", "yellow"),
					array("message", "blue"),
					array("easter_egg", "hotpink")
				) as $msg) {
				show_output($msg[0], $msg[1]);
			}
			print "</div>";

			if($GLOBALS['accepted_public_data']) {
				$pagenr = get_get('page');
				if(!$pagenr) {
					$pagenr = get_post('page');
				}

				if(!preg_match('/^\d+$/', $pagenr)) {
					$pagenr = null;
				}

				if(get_get('show_items')) {
					$query = 'SELECT `id`, `name` FROM `page` WHERE `parent` = '.esc(get_get('show_items')).' AND `show_in_navigation` = "1" AND `id` IN (SELECT `page_id` FROM `role_to_page` WHERE `role_id` = '.esc($GLOBALS['user_role_id'][0]).')';
					$query .= ' ORDER BY `name`';
					$result = rquery($query);

					if(mysqli_num_rows($result)) {
						$subpage_data = array();
						$subpage_ids = array();
						while ($row = mysqli_fetch_row($result)) {
							if($row[1]) {
								$subpage_data[] = array($row[0], $row[1]);
								$subpage_ids[] = $row[0];
							}
						}
						$subpage_texts = get_page_info_by_id($subpage_ids);
						print "<h2>Untermenüs von &raquo;".get_page_name_by_id(get_get('show_items'))."&laquo;</h2>\n";
						$GLOBALS['submenu_id'] = get_get('show_items');
						include('hinweise.php');
						print "<ul>\n";
						foreach ($subpage_data as $row) {
							if($row[1]) {
								if(array_key_exists($row[0], $subpage_texts) && $subpage_texts[$row[0]]) {
									print "<li style='margin: 5px 0;'><a href='admin.php?page=$row[0]'>$row[1]</a> &mdash; ".htmlentities($subpage_texts[$row[0]])."</li>\n";
								} else {
									print "<li style='margin: 5px 0;'><a href='admin.php?page=$row[0]'>$row[1]</a></li>\n";
								}
							}
						}
						print "</ul>\n";
					} else {
						print "<h2 class='class_red'>Der ausgewählte Menüpunkt ist leider nicht im System vorhanden oder Sie haben keine Rechte, auf ihn zuzugreifen.</h2>\n";
					}
				} else {
					if(!isset($pagenr)) {
						include(dirname(__FILE__).'/pages/welcome.php');
					} else {
						$page_file = '';
						if(array_key_exists($pagenr, $GLOBALS['pages'])) {
							$page_file = $GLOBALS['pages'][$pagenr][1];
						} else {
							$page_file = get_page_file_by_id($pagenr);
						}

						$page_file_basename = $page_file;

						$page_file = dirname(__FILE__).'/pages/'.$page_file;

						if(!file_exists($page_file)) {
							error("Die Datei `$page_file_basename` konnte nicht gefunden werden!");
						} else if (!$page_file_basename) {
							error("Die Unterseite konnte in der Datenbank nicht gefunden werden!");
						} else {
							if(check_page_rights($page_file_basename)) {
								if($GLOBALS['deletion_page']) {
									trash("<h2>Sicher, dass das alles gelöscht werden soll?</h2>");
									show_output("trash", "orange");
?>

									Um die <a href="https://de.wikipedia.org/wiki/Konsistenz_%28Datenspeicherung%29">Datenintegrität</a> zu gewährleisten, werden
									alle Datensätze, die von dem, der gelöscht werden soll, abhängig sind, auch gelöscht. Dies kann mitunter gewaltige
									Auswirkungen auf das gesamte System haben. Daher soll das Löschen extra bestätigt werden, bevor es ausgeführt wird.

									In den folgenden Tabellen sehen Sie alle Daten, die, mit diesem Datensatz zusammen, gelöscht werden. Am unteren Ende der
									Seite haben Sie die Möglichkeit, das Löschen tatsächlich auszuführen bzw. abzubrechen.
<?php
									if($GLOBALS['deletion_db'] && $GLOBALS['deletion_where']) {
										print get_foreign_key_deleted_data_html($GLOBALS['dbname'], $GLOBALS['deletion_db'], $GLOBALS['deletion_where']);
									}
?>
									<form method="post" enctype="multipart/form-data" action="<?php print $_SERVER['HTTP_REFERER']; ?>">
<?php
										foreach ($_POST as $this_post_name => $this_post_value) {
											if(!is_array($this_post_value)) {
?>
												<input type="hidden" name="<?php print htmlentities($this_post_name); ?>" value="<?php print htmlentities($this_post_value); ?>" />
<?php
											} else {
												foreach ($this_post_value as $array_this_post_name => $array_this_post_value) {
?>
													<input type="hidden" name="<?php print htmlentities($this_post_name); ?>[]" value="<?php print htmlentities($array_this_post_value); ?>" />
<?php
												}
											}
										}
?>
										<input type="hidden" name="delete_for_sure" value="1" />
										<input type="submit" value="Ja, ich bin mir sicher!" />
									</form>
									<form>
										<input type="button" value="Nein, lieber nicht." onClick="history.go(-1);return true;">
									</form>
<?php
								} else {
									$GLOBALS['this_page_number'] = $pagenr;
									$GLOBALS['this_page_file'] = $page_file;
									include('hinweise.php');
									include($page_file);
								}
							} else {
								print "<i class='class_red'>Sie haben kein Recht, auf diese Seite zuzugreifen.</i>";
							}
						}
					}
				}
			} else {
?>
				<h3>Datenschutz-/Einwilligungserklärung </h3>

				<p>Hiermit bestätige ich, dass ich berechtigt bin, diese Seite aufzurufen und dass ich damit keinen Scheiß bauen werde.</p>

				<p>Datenverarbeitende Stelle<br />
				Die LINKE, Dresden<br />

				<p>Kontakt: Norman Koch<br />
				Mail: kochnorman@rocketmail.com</p>
<?php
				if(get_get('page') || get_get('show_items')) {
					$id = get_get('page');
					if(!$id) {
						$id = get_get('show_items');
					}
?>
					<p style="color: red;">Die Seite &raquo;<?php print get_page_name_by_id($id); ?>&laquo; konnte nicht aufgerufen werden. Bitte stimmen
					Sie zuerst den Datenschutzbedingungen zu.</p>
<?php
				}
?>
				
				<form>
					<input type="hidden" name="page" value="<?php print htmlentities(get_get('page')); ?>" />
					<input type="hidden" name="show_items" value="<?php print htmlentities(get_get('show_items')); ?>" />
					Ankreuzeln, wenn einverstanden, dann &raquo;Akzeptieren&laquo; drücken! &rarr; <input noautosubmit="1" type="checkbox" name="sdsg_einverstanden" value="1" />
					<input noautosubmit="1" type="submit" value="Akzeptieren" />
				</form>
<?php
			}

			print "<div id='second_notices'>";
			foreach (array(
					array("success", "green"),
					array("error", "red"),
					array("mysql_error", "red"),
					array("right_issue", "red"),
					array("warning", "orange"),
					array("mysql_warning", "orange"),
					array("debug", "yellow"),
					array("message", "blue")
				) as $msg) {
				show_output($msg[0], $msg[1]);
			}
			print "</div>";
		}
?>
		<script>
			document.getElementById("main_notices").innerHTML = document.getElementById("main_notices").innerHTML + document.getElementById("second_notices").innerHTML;
			document.getElementById("second_notices").innerHTML = "";
		</script>
<?php
		include("footer.php");
?>
