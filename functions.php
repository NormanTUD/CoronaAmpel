<?php
	$GLOBALS['php_start'] = time();
	error_reporting(E_ALL);
	set_error_handler(function ($severity, $message, $file, $line) {
		throw new \ErrorException($message, $severity, $severity, $file, $line);
	});

	ini_set('display_errors', 1);

	header_remove("X-Powered-By"); // Serverinfos entfernen

	// Definition globaler Variablen
	$GLOBALS['error'] = array();
	$GLOBALS['mysql_error'] = array();
	$GLOBALS['hint'] = array();
	$GLOBALS['message'] = array();
	$GLOBALS['warning'] = array();
	$GLOBALS['mysql_warning'] = array();
	$GLOBALS['trash'] = array();
	$GLOBALS['success'] = array();
	$GLOBALS['debug'] = array();
	$GLOBALS['easter_egg'] = array();
	$GLOBALS['import_table'] = '';
	$GLOBALS['show_backtrace'] = 0;
	if(file_exists('/etc/show_trace')) {
		$GLOBALS['show_backtrace'] = 1;
	}

	$GLOBALS['compare_db'] = '';

	$GLOBALS['already_deleted_old_session_ids'] = 0;

	$GLOBALS['submenu_id'] = null;

	$GLOBALS['end_html'] = 1;

	$GLOBALS['slurped_sql_file'] = 0;

	$GLOBALS['deletion_page'] = 0;

	$GLOBALS['rquery_print'] = 0;

	$GLOBALS['queries'] = array();
	$GLOBALS['function_usage'] = array();

	$GLOBALS['dbh'] = '';
	$GLOBALS['right_issue'] = array();
	$GLOBALS['reload_page'] = 0;

	$GLOBALS['user_role_cache'] = array();
	$GLOBALS['settings_cache'] = array();
	$GLOBALS['table_exists_cache'] = array();
	$GLOBALS['db_exists_cache'] = array();

	$GLOBALS['memoize'] = array();

	include_once('mysql.php');

	if(!isset($GLOBALS['setup_mode'])) {
		$GLOBALS['setup_mode'] = 0;
	}

	if(file_exists('new_setup')) {
		$GLOBALS['setup_mode'] = 1;
	}

	if(!$GLOBALS['setup_mode']) {
		if(!function_exists('mysqli_connect')) {
			dier("Das PHP-Plugin für MySQL-Verbindungen ist nicht installiert!");
		}
		if(!database_exists($GLOBALS['dbname'])) {
			rquery("CREATE DATABASE ".$GLOBALS['dbname']);
		}

		rquery('USE `'.$GLOBALS['dbname'].'`');
		rquery('SELECT @@FOREIGN_KEY_CHECKS');
		rquery('SET FOREIGN_KEY_CHECKS=1');
	}

	rquery("SET NAMES utf8");

	/* Login-Kram */
	$GLOBALS['logged_in_was_tried'] = 0;
	$GLOBALS['logged_in'] = 0;
	$GLOBALS['logged_in_user_id'] = NULL;
	$GLOBALS['logged_in_data'] = NULL;
	$GLOBALS['accepted_public_data'] = NULL;

	$GLOBALS['pages'] = array();

	if(!$GLOBALS['setup_mode']) {
		if(get_post('try_login')) {
			$GLOBALS['logged_in_was_tried'] = 1;
		}

		if(get_cookie('session_id')) {
			delete_old_session_ids();
			if(table_exists('view_user_session_id')) {
				try {
					$query = 'SELECT `user_id`, `username`, `accepted_public_data` FROM `view_user_session_id` WHERE `session_id` = '.
						esc($_COOKIE['session_id']).' AND `enabled` = "1"';
					$result = rquery($query, 0, 1);
					while ($row = mysqli_fetch_row($result)) {
						$GLOBALS['logged_in'] = 1;
						$GLOBALS['logged_in_data'] = $row;
						$GLOBALS['logged_in_user_id'] = $row[0];
						$GLOBALS['user_role_id'] = get_role_id_by_user($row[0]);
						$GLOBALS['accepted_public_data'] = $row[2];
					}
					$result->free();
				} catch (Exception $e) {
					$GLOBALS['logged_in'] = 0;
				}
			}
		}

		if (!$GLOBALS['logged_in'] && get_post('username') && get_post('password')) {
			delete_old_session_ids();
			$GLOBALS['logged_in_was_tried'] = 1;
			$user = $_POST['username'];
			$possible_user_id = get_user_id($user);
			$salt = get_salt($possible_user_id);
			$pass = hash('sha256', $_POST['password'].$salt);

			$query = 'SELECT `id`, `username`, `accepted_public_data` FROM `users` WHERE `username` = '.
				esc($user).' AND `password_sha256` = '.esc($pass).' AND `enabled` = "1"';
			$result = rquery($query);
			while ($row = mysqli_fetch_row($result)) {
				delete_old_session_ids($GLOBALS['logged_in_user_id']);
				$GLOBALS['logged_in'] = 1;
				$GLOBALS['logged_in_data'] = $row;
				$GLOBALS['logged_in_user_id'] = $row[0];
				$GLOBALS['user_role_id'] = get_role_id_by_user($row[0]);
				$GLOBALS['accepted_public_data'] = $row[2];

				$session_id = generate_random_string(1024);
				$query = 'INSERT IGNORE INTO `session_ids` (`session_id`, `user_id`) VALUES ('.esc($session_id).', '.esc($row[0]).')';
				rquery($query);

				setcookie('session_id', $session_id, time() + (86400 * 2), "/");
			}
			$result->free();
		}

		if($GLOBALS['logged_in_user_id'] && basename($_SERVER['SCRIPT_NAME']) == 'admin.php') {
			$query = 'SELECT `name`, `file`, `page_id`, `show_in_navigation`, `parent` FROM `view_account_to_role_pages` WHERE `user_id` = '.esc($GLOBALS['logged_in_user_id']);
			$query .= ' ORDER BY `parent`, `name`';
			$result = rquery($query);

			while ($row = mysqli_fetch_row($result)) {
				$GLOBALS['pages'][$row[2]] = $row;
			}
			$result->free();

			if(get_get('sdsg_einverstanden')) {
				$query = 'UPDATE `users` SET `accepted_public_data` = "1" WHERE `id` = '.esc($GLOBALS['logged_in_user_id']);
				rquery($query);

				$GLOBALS['accepted_public_data'] = 1;
			}
		}

		if(array_key_exists('REQUEST_URI', $_SERVER) && preg_match('/\/pages\//', $_SERVER['REQUEST_URI'])) {
			$script_name = basename($_SERVER['REQUEST_URI']);
			$page_id = get_page_id_by_filename($script_name);
			if($page_id) {
				$header = 'Location: ../admin.php?page='.$page_id;
				header($header);
			} else {
				die("Die internen Seiten dürfen nicht direkt aufgerufen werden. Die gesuchte Seite konnte im Index nicht gefunden werden. Nehmen Sie &mdash; statt der direkten URL &mdash; den Weg über das Administrationsmenü.");
			}
		}
	}

	/* Parameter verarbeiten */

	if($GLOBALS['logged_in']) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann
		// Falls eine ID gegeben ist, dann sind bereits Daten vorhanden, die editiert oder gelöscht werden sollen.

		#dier($_POST);

		if(get_post("frage_id")) {
			if(get_post("delete_frage")) {
				delete_frage(get_post("frage_id"));
			} else {
				$frage_id = get_post("frage_id");
				$frage = get_post("frage");
				$antwort = get_post("antwort");

				$show_ampel = get_post("show_ampel") ? 1 : 0;
				$red = get_post("red") ? 1 : 0;
				$yellow = get_post("yellow") ? 1 : 0;
				$green = get_post("green") ? 1 : 0;


				$quelle = get_post("quelle");
				$grundrechtseinschraenkung = get_post("grundrechtseinschraenkung");

				update_frage($frage_id, $frage, $antwort, $show_ampel, $red, $yellow, $green, $quelle, $grundrechtseinschraenkung);
			}
		}

		if(!is_null(get_post('id')) || !is_null(get_get('id'))) {
			$this_id = get_post('id');
			if(!$this_id) {
				$this_id = get_get('id');
			}
			if(get_post('delete') && !get_post('delete_for_sure')) {
				/*
					Festlegung der Tabellen, aus denen etwas gelöscht werden soll.
				 */
				$GLOBALS['deletion_page'] = 1;

				$GLOBALS['deletion_where'] = array('id' => $this_id);

				if(get_post('funktion_name')) {
					$GLOBALS['deletion_db'] = 'function_rights';
				}

				if(get_post('update_wartungstermine')) {
					$GLOBALS['deletion_db'] = 'kunden';
				}

				if(get_post('neue_rolle') && get_post('page')) {
					$GLOBALS['deletion_db'] = 'role';
				}

				if(get_post('name') && get_post('id') && get_post('role')) {
					$GLOBALS['deletion_db'] = 'users';
				}

				if(get_post('updatepage') && get_post('id')) {
					$GLOBALS['deletion_db'] = 'page';
				}
			} else {
				if(get_post('newpage')) {
					$titel = get_post('titel');
					$datei = get_post('datei');
					$show_in_navigation = get_post('show_in_navigation') ? 1 : 0;
					$eltern = get_post('eltern') ? get_post('eltern') : '';
					$role_to_page = get_post('role_to_page');
					$beschreibung = get_post('beschreibung') ? get_post('beschreibung') : '';
					$hinweis = get_post('hinweis') ? get_post('hinweis') : '';

					if(isset($titel) && isset($datei) && isset($show_in_navigation) && isset($eltern) && isset($role_to_page) && isset($beschreibung) && isset($hinweis)) {

						create_new_page($titel, $datei, $show_in_navigation, $eltern, $role_to_page, $beschreibung, $hinweis);
					} else {
						error('Missing parameters!');
					}
				}

				if(get_post('updatepage')) {
					$id = get_post('id');
					if(get_post('delete')) {
						if(isset($id)) {
							delete_page($id);
						}
					} else {
						$titel = get_post('titel');
						$datei = get_post('datei');
						$show_in_navigation = get_post('show_in_navigation') ? 1 : 0;
						$eltern = get_post('eltern') ? get_post('eltern') : '';
						$role_to_page = get_post('role_to_page');
						$beschreibung = get_post('beschreibung') ? get_post('beschreibung') : '';
						$hinweis = get_post('hinweis') ? get_post('hinweis') : '';

						if(isset($id) && isset($titel) && isset($role_to_page)) {
							update_page_full($id, $titel, $datei, $show_in_navigation, $eltern, $role_to_page, $beschreibung, $hinweis);
						} else {
							error('Missing parameters!');
						}
					}
				}

				if(get_post('funktion_name')) {
					if(get_post('delete')) {
						delete_funktion_rights($this_id);
					} else {
						update_funktion_rights($this_id, get_post('funktion_name'));
					}
				}

				if(get_post('update_page_info')) {
					update_page_info(get_post('id'), get_post('info'));
				}

				if(get_post('neue_rolle') && get_post('page')) {
					if(get_post('delete')) {
						delete_role($this_id);
					} else {
						update_role($this_id, get_post('neue_rolle'));
						$query = 'DELETE FROM `role_to_page` WHERE `role_id` = '.esc(get_role_id(get_post('neue_rolle')));;
						rquery($query);
						foreach (get_post('page') as $key => $this_page_id) {
							if(preg_match('/^\d+$/', $this_page_id)) {
								assign_page_to_role(get_role_id(get_post('neue_rolle')), $this_page_id);
							}
						}
					}
				}

				if(get_post('name') && get_post('id') && get_post('role')) {
					if(get_post('delete')) {
						delete_user($this_id);
					} else {
						$enabled = get_account_enabled_by_id($this_id);
						if(get_post('disable_account')) {
							$enabled = 0;
						}

						if(get_post('enable_account')) {
							$enabled = 1;
						}

						$accpubdata = 1;
						if(get_post('accepted_public_data')) {
							$accpubdata = 1;
						}

						update_user(get_post('name'), get_post('id'), get_post('password'), get_post('role'), $enabled, $accpubdata);
					}
				}
			}
		} else {
			#dier($_POST);

			if(get_post("neue_frage")) {
				$frage = get_post("frage");
				$antwort = get_post("antwort");

				$show_ampel = get_post("show_ampel") ? 1 : 0;
				$red = get_post("red") ? 1 : 0;
				$yellow = get_post("yellow") ? 1 : 0;
				$green = get_post("green") ? 1 : 0;

				$quelle = get_post("quelle");
				$grundrechtseinschraenkung = get_post("grundrechtseinschraenkung");

				create_frage($frage, $antwort, $show_ampel, $red, $yellow, $green, $quelle, $grundrechtseinschraenkung);
			}

			if(get_post('update_setting')) {
				if(get_post('reset_setting')) {
					reset_setting(get_post('name'));
				} else {
					set_setting(get_post('name'), get_post('value'), get_post("description"));
				}
			}

			/*
			if(get_post("sqlinserter")) {
				$query_array = preg_split("/\n|;/", get_post("sqlinserter"));
				foreach ($query_array as $this_query) {
					if(!preg_match("/^\s*$/", $this_query)) {
						rquery($this_query);
					}
				}
			}
			*/

			if(get_post('merge_data')) {
				if(get_get('table') && get_post('merge_from') && get_post('merge_to')) {
					merge_data(get_get('table'), get_post('merge_from'), get_post('merge_to'));
				} else {
					error(' Sowohl eine bzw. mehrere Quelle als auch ein Zielort müssen angegeben werden.');
				}
			}

			if(get_post('new_function_right')) {
				$role_id = get_post('role_id');
				if($role_id) {
					$funktion_name = get_post('funktion_name');
					if($funktion_name) {
						create_function_right($role_id, $funktion_name);
					} else {
						error('Die Funktion konnte nicht angelegt werden, da sie keinen validen Namen zugeordnet bekommen hat. ');
					}
				} else {
					error('Die Funktion konnte nicht angelegt werden, da sie keiner Rolle zugeordnet wurden ist. ');
				}
			}

			if(get_post('import_datenbank')) {
				if(array_key_exists('sql_file', $_FILES) && array_key_exists('tmp_name', $_FILES['sql_file'])) {
					SplitSQL($_FILES['sql_file']['tmp_name']);
				}
			}

			if(get_post('datenbankvergleich')) {
				if(array_key_exists('sql_file', $_FILES) && array_key_exists('tmp_name', $_FILES['sql_file'])) {
					$GLOBALS['compare_db'] = compare_db($_FILES['sql_file']['tmp_name']);
				}
			}

			if(get_post('change_own_data')) {
				$new_password = get_post('password');
				$new_password_repeat = get_post('password_repeat');
				if($new_password && strlen($new_password) >= 5) {
					if($new_password == $new_password_repeat) {
						update_own_data($new_password);
					} else {
						error('Beide Passworteingaben müssen identisch sein. ');
					}
				} else {
					error('Das Passwort muss mindestens 5 Zeichen haben.');
				}
			}

			if(get_post('startseitentext')) {
				$startseitentext = get_post('startseitentext');
				update_startseitentext($startseitentext);
			}

			if(get_post('update_text') && get_post('page_id')) {
				update_text(get_post('page_id'), get_post('text'));
			}

			if(get_post('update_hinweis') && get_post('page_id')) {
				update_hinweis(get_post('page_id'), get_post('hinweis'));
			}

			if(get_post_multiple_check(array('new_user', 'name', 'password', 'role'))) {
				create_user(get_post('name'), get_post('password'), get_post('role'));
			} else if (get_post('new_user')) {
				warning('Benutzer müssen einen Namen, ein Passwort und eine Rolle haben. ');
			}

			if(get_post('neue_rolle') && get_post('page')) {
				create_role(get_post('neue_rolle'));
				// Alle alten Rollendaten löschen
				$query = 'DELETE FROM `role_to_page` WHERE `role_id` = '.esc(get_role_id(get_post('neue_rolle')));
				rquery($query);
				foreach (get_post('page') as $key => $this_page_id) {
					if(preg_match('/^\d+$/', $this_page_id)) {
						assign_page_to_role(get_role_id(get_post('neue_rolle')), $this_page_id);
					}
				}
			}
		}
	}

	if($GLOBALS['setup_mode']) {
		if(get_post('import_datenbank')) {
			rquery('USE `'.$GLOBALS['dbname'].'`');
			if(array_key_exists('sql_file', $_FILES) && array_key_exists('tmp_name', $_FILES['sql_file'])) {
				SplitSQL($_FILES['sql_file']['tmp_name']);
			}
		}
	}

	function string_to_number ($str, $show_error_ok = 0) {
		if(is_null($str)) {
			return null;
		}

		if(gettype($str) == "double" || gettype($str) == "integer") {
			return $str;
		}

		$original_string = $str;
		$str = preg_replace('/\s*(?:€|&euro;)/', '', $str);
		if(preg_match("/^-?\d+(?:[\.,]\d+)$/", $str)) {
			$str = preg_replace("/^-?(\d+),(\d+).*?/", "\\1.\\2", $str);
			return doubleval($str);
		} else if(preg_match("/^(-?\d+)[^\d]+$/", $str, $matches)) {
			return intval($matches[1]);
		} else if(preg_match("/^-?\d+$/", $str)) {
			return intval($str);
		} else if(preg_match("/^-?\d+(?:[\.,]\d+)?[^\d]+.*$/", $str)) {
			$str = preg_replace("/^-?(\d+),(\d+).*?/", "\\1.\\2", $str);
			return doubleval($str);
		} else if(preg_match("/^\s*$/", $str)) {
			return null;
		} else if(preg_match("/^(\d+)(.*?)$/", $str, $matches)) {
			warning("Der String ".fq($original_string)." konnte nicht so übernommen werden. Er wurde zu ".fq($matches[1])." gemacht.");
			return intval($matches[1]);
		} else {
			$error_str = "FEHLER: &raquo;$original_string&laquo; konnte nicht in eine Zahl konvertiert werden. Es wird stattdessen NULL genommen.";
			if($show_error_ok) {
				$error_str .= " (Im Testmodus ist dieser Fehler gewollt. Alles OK!)";
			}
			error($error_str);
			return null;
		}
	}

	function htmle ($str, $shy = 0) {
		if($shy) {
			if($str) {
				$str = htmlentities($str);
				return $str;
			} else {
				return '&mdash;';
			}
		} else {
			if($str) {
				return htmlentities($str);
			} else {
				return '&mdash;';
			}
		}
	}

	function compare_db ($file, $session_ids = 0) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if(file_exists($file)) {
			$skip = array();
			if(!$session_ids) {
				$skip = array('session_ids');
			}
			$now = backup_tables('*', $skip);
			$then = file_get_contents($file);

			if(strlen($then)) {
				require_once dirname(__FILE__).'/Classes/Diff.php';

				$file_a = explode("\n", $then);
				$file_b = explode("\n", $now);

				$options = array();

				$diff = new Diff($file_a, $file_b, $options);
				require_once dirname(__FILE__).'/Classes/Diff/Renderer/Html/SideBySide.php';
				$renderer = new Diff_Renderer_Html_SideBySide;
				$tdiff = $diff->Render($renderer);
				if($tdiff) {
					return $tdiff;
				} else {
					error('Das Diff konnte nicht erzeugt werden oder war leer. ');
				}
			} else if (!$now) {
				error('Das Image der aktuellen Datenbank konnte nicht erstellt werden. ');
			} else {
				error('Die Vergleichsdatei darf nicht leer sein. ');
			}
		} else {
			error('Die Datei konnte nach dem Hochladen nicht gefunden werden. Bitte die Apache-Konfiguration überprüfen! ');
		}
	}

	// https://stackoverflow.com/questions/1883079/best-practice-import-mysql-file-in-php-split-queries
	function SplitSQL($file, $delimiter = ';') {
		if(!$GLOBALS['setup_mode']) {
			if(!check_function_rights(__FUNCTION__)) { return; }
		}

		$GLOBALS['slurped_sql_file'] = 1;
		set_time_limit(0);

		if (is_file($file) === true) {
			$file = fopen($file, 'r');
			$GLOBALS['install_counter'] = 1;

			if (is_resource($file) === true) {
				$query = array();

				while (feof($file) === false) {
					$query[] = fgets($file);

					if(preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
						$query = trim(implode('', $query));

						stderrw(">>> ".($GLOBALS['install_counter']++).": $query\n");

						if (rquery($query) === false) {
							print '<h3>ERROR: '.htmlentities($query).'</h3>'."\n";
						}

						while (ob_get_level() > 0) {
							ob_end_flush();
						}

						flush();
					}

					if (is_string($query) === true) {
						$query = array();
					}
				}

				return fclose($file);
			}
		}

		return false;
	}

	/* https://davidwalsh.name/backup-mysql-database-php */
	function backup_tables ($tables = '*', $skip = null, $anonymize = null, $get_array = 0) {
		if(!$GLOBALS['setup_mode']) {
			if(!check_function_rights(__FUNCTION__)) { return; }
		}

		rquery('USE `'.$GLOBALS['dbname'].'`');
		//get all of the tables
		if($tables == '*') {
			$tables = array();
			$result = rquery('SHOW TABLES');
			while($row = mysqli_fetch_row($result)) {
				if(!((is_array($skip) && array_search($row[0], $skip)) || (!is_array($skip) && $row[0] == $skip))) {
					$tables[] = $row[0];
				}
			}
		} else {
			$tables = is_array($tables) ? $tables : explode(',', $tables);
		}

		$return = "SET FOREIGN_KEY_CHECKS=0;\n";
		if(!$get_array) {
			$return .= "DROP DATABASE `".$GLOBALS['dbname']."`;\n";
			$return .= "CREATE DATABASE `".$GLOBALS['dbname']."`;\n";
			$return .= "USE `".$GLOBALS['dbname']."`;\n";
		}

		foreach(sort_tables($tables) as $table) {
			$result = rquery('SELECT * FROM '.$table);
			$num_fields = mysqli_field_count($GLOBALS['dbh']);

			$this_return = '';

			$row2 = mysqli_fetch_row(rquery('SHOW CREATE TABLE '.$table));
			$row2 = preg_replace('/CHARSET=latin1/', 'CHARSET=utf8', $row2);
			if(!$get_array) {
				if(preg_match('/^CREATE TABLE/i', $row2[1])) {
					$this_return .= 'DROP TABLE IF EXISTS '.$table.';';
				} else {
					$this_return .= 'DROP VIEW IF EXISTS '.$table.';';
				}
			}

			$this_return.= "\n\n".$row2[1].";\n\n";

			if(preg_match('/^CREATE TABLE/i', $row2[1])) {
				for ($i = 0; $i < $num_fields; $i++) {
					while($row = mysqli_fetch_row($result)) {
						if(!$get_array) {
							$this_return.= 'INSERT INTO `'.$table.'` VALUES(';
							for($j = 0; $j < $num_fields; $j++) {
								if($anonymize && !preg_match('/\d/', $row[$j])) {
									$row[$j] = generate_random_string(strlen($row[$j]));
								}
								$row[$j] = esc($row[$j]);
								if (isset($row[$j])) {
									$this_return .= $row[$j];
								} else {
									$this_return .= 'NULL';
								}
								if ($j < ($num_fields - 1)) {
									$this_return .= ', ';
								}
							}
							$this_return .= ");\n";
						}
					}
				}
			}

			$return .= "$this_return\n";
		}

		$return .= "\n\n\nSET FOREIGN_KEY_CHECKS=1;\n";
		if($get_array) {
			return preg_split("/\n*;\n*/", $return);
		} else {
			return $return;
		}
	}

	function database_exists ($name) {
		if(!array_key_exists($name, $GLOBALS['db_exists_cache'])) {
			$query = 'SELECT count(SCHEMA_NAME) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '.esc($name);
			$GLOBALS['db_exists_cache'][$name] = !!get_single_value_from_query($query);	
		}
		return $GLOBALS['db_exists_cache'][$name];
	}

	function table_exists_nocache ($name, $db = null) {
		if(is_null($db)) {
			$db = $GLOBALS['dbname'];
		}
		$query = 'select count(*) from information_schema.tables where table_schema = '.esc($db).' and table_name = '.esc($name);
		return !!get_single_value_from_query($query);
	}

	function table_exists ($name, $db = null, $renew_cache = 0) {
		if(is_null($db)) {
			$db = $GLOBALS['dbname'];
		}

		if(!array_key_exists($db, $GLOBALS['table_exists_cache'])) {
			$GLOBALS['table_exists_cache'][$db] = array();
		}

		if(!array_key_exists($name, $GLOBALS['table_exists_cache'][$db])) {
			$query = 'select table_name, count(*) from information_schema.tables where table_schema = '.esc($db).' group by table_name';
			$result = rquery($query);

			while ($row = mysqli_fetch_row($result)) {
				$name = $row[0];
				$GLOBALS['table_exists_cache'][$db][$name] = !!$row[1];
			}
		}

		$this_result = 0;
		if(array_key_exists($db, $GLOBALS['table_exists_cache'])) {
			if(array_key_exists($name, $GLOBALS['table_exists_cache'][$db])) {
				$this_result = $GLOBALS['table_exists_cache'][$db][$name];
			}
		}

		if($renew_cache) {
			$GLOBALS['table_exists_cache'] = array();
		}

		return $this_result;
	}


	function sort_tables ($tables) {
		$create_views = array();
		$create_tables = array();

		foreach ($tables as $table) {
			if(preg_match('/^view_|^ua_overview$/', $table)) {
				$create_views[] = $table;
			} else {
				$create_tables[] = $table;
			}
		}

		$tables_sorted_tmp = array();

		foreach ($create_tables as $table) {
			$foreign_keys = get_foreign_key_tables($GLOBALS['dbname'], $table);
			$foreign_keys_counter = 0;
			if(array_key_exists(0, $foreign_keys)) {
				$foreign_keys_counter = count($foreign_keys[0]);
			}
			$tables_sorted_tmp[] = array('name' => $table, 'foreign_keys_counter' => $foreign_keys_counter);
		}

		usort($tables_sorted_tmp, 'foreignKeyAscSort');

		foreach ($tables_sorted_tmp as $table) {
			$tables_sorted[] = $table['name'];
		}

		foreach ($create_views as $view) {
			$tables_sorted[] = $view;
		}

		return $tables_sorted;
	}

	function foreignKeyAscSort($item1, $item2) {
		if ($item1['foreign_keys_counter'] == $item2['foreign_keys_counter']) {
			return 0;
		} else {
			return ($item1['foreign_keys_counter'] < $item2['foreign_keys_counter']) ? -1 : 1;
		}
	}

	function get_referencing_foreign_keys ($database, $table) {
		$query = 'SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = "'.$database.'" AND REFERENCED_TABLE_NAME = '.esc($table);
		$result = rquery($query);
		$foreign_keys = array();
		while ($row = mysqli_fetch_row($result)) {
			$foreign_keys[] = array('database' => $row[0], 'table' => $row[1], 'column' => $row[2], 'reference_column' => $row[3]);
		}
		$result->free();

		return $foreign_keys;
	}

	function get_foreign_key_deleted_data_html ($database, $table, $where) {
		$data = get_foreign_key_deleted_data ($database, $table, $where);

		$html = '';
		$j = 0;
		foreach ($data as $key => $this_data) {
			$html .= "<h2>$key</h2>\n";

			$html .= "<table>\n";
			$i = 0;
			foreach ($this_data as $value) {
				if($i == 0) {
					$html .= "\t<tr>\n";
					foreach ($value as $column => $column_value) {
						$html .= "\t\t<th>".htmlentities($column)."</th>\n";
					}
					$html .= "\t</tr>\n";
				}
				$html .= "\t<tr>\n";
				foreach ($value as $column => $column_value) {
					if(preg_match('/password|session_id|salt/', $column)) {
						$html .= "\t\t<td><i>Aus Sicherheitsgründen wird diese Spalte nicht angezeigt.</i></td>\n";
					} else {
						if($column_value) {
							$html .= "\t\t<td>".htmlentities($column_value)."</td>\n";
						} else {
							$html .= "\t\t<td><i style='color: orange;'>NULL</i></td>\n";
						}
					}
				}
				$html .= "\t</tr>\n";
				$i++;
			}
			$html .= "</table>\n";

			if($i == 1) {
				$html .= "<h3>$i Zeile</h3><br />\n";
			} else {
				$html .= "<h3>$i Zeilen</h3><br />\n";
			}
			$j += $i;
		}

		$html .= "<h4>Insgesamt $j Datensätze</h4>\n";;

		return $html;
	}

	function get_primary_keys ($database, $table) {
		$query = "SELECT k.column_name FROM information_schema.table_constraints t JOIN information_schema.key_column_usage k USING(constraint_name,table_schema,table_name) WHERE t.constraint_type='PRIMARY KEY' AND t.table_schema = ".esc($database)." AND t.table_name = ".esc($table);
		$result = rquery($query);

		$data = array();

		while ($row = mysqli_fetch_row($result)) {
			$data[] = $row;
		}
		$result->free();

		return $data;
	}

	function get_foreign_key_tables ($database, $table) {
		$query = "SELECT TABLE_NAME, COLUMN_NAME, ' -> ', REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_COLUMN_NAME IS NOT NULL AND CONSTRAINT_SCHEMA = ".esc($database)." AND TABLE_NAME = ".esc($table);
		$result = rquery($query);

		$data = array();

		while ($row = mysqli_fetch_row($result)) {
			$data[] = $row;
		}
		$result->free();

		return $data;
	}

	function get_foreign_key_deleted_data ($database, $table, $where) {
		$GLOBALS['get_data_that_would_be_deleted'] = array();
		$data = get_data_that_would_be_deleted($database, $table, $where);
		$GLOBALS['get_data_that_would_be_deleted'] = array();
		return $data;
	}

	function get_data_that_would_be_deleted ($database, $table, $where, $recursion = 100) {
		if($recursion <= 0) {
			error("get_data_that_would_be_deleted: Tiefenrekursionsfehler. ");
			return;
		}

		if($recursion == 100) {
			$GLOBALS['get_data_that_would_be_deleted'] = array();
		}

		if($table) {
			if(preg_match('/^[a-z0-9A-Z_]+$/', $table)) {
				if(is_array($where)) {
					$foreign_keys = get_referencing_foreign_keys($database, $table);
					$data = array();

					$query = 'SELECT * FROM `'.$table.'`';
					if(count($where)) {
						$query .= ' WHERE 1';
						foreach ($where as $name => $value) {
							$query .= " AND `$name` IN (".esc($value).')';
						}
					}
					$result = rquery($query);

					$to_check = array();

					while ($row = mysqli_fetch_row($result)) {
						$new_row = array();
						$i = 0;
						foreach ($row as $this_row) {
							$field_info = mysqli_fetch_field_direct($result, $i);
							$new_row[$field_info->name] = $this_row;
							foreach ($foreign_keys as $this_foreign_key) {
								if($this_foreign_key['reference_column'] == $field_info->name) {
									$to_check[] = array('value' => $this_row, 'foreign_key' => array('table' => $this_foreign_key['table'], 'column' => $this_foreign_key['column'], 'database' => $this_foreign_key['database']));
								}
							}
							$i++;
						}
						$GLOBALS['get_data_that_would_be_deleted'][$table][] = $new_row;
					}
					$result->free();
					foreach ($to_check as $this_to_check) {
						if(isset($this_to_check['value']) && !is_null($this_to_check['value'])) {
							get_data_that_would_be_deleted($database, $this_to_check['foreign_key']['table'], array($this_to_check['foreign_key']['column'] => $this_to_check['value']), $recursion - 1);;
						}
					}

					$data = $GLOBALS['get_data_that_would_be_deleted'];

					return $data;
				} else {
					die("\$where needs to be an array with column_name => value pairs");
				}
			} else {
				die('`'.htmlentities($table).'` is not a valid table name');
			}
		} else {
			die("\$table was not defined!");
		}
	}

	function check_page_rights ($page, $log = 1) {
		$log = 0;
		if( (array_key_exists('user_role_id', $GLOBALS) && isset($GLOBALS['user_role_id'])) ) {
			$role_id = $GLOBALS['user_role_id'];
			return check_page_rights_role_id($page, $role_id, $log);
		} else {
			return 0;
		}
	}

	function check_page_rights_role_id ($page_id, $role_id, $log = 1) {
		if( (isset($role_id) || is_null($role_id) ) && (array_key_exists('user_role_id', $GLOBALS) && isset($GLOBALS['user_role_id'])) ) {
			$role_id = $GLOBALS['user_role_id'];
		}

		if(!$role_id) {
			return 0;
		}

		if(is_array($page_id)) {
			$query = 'SELECT `page_id` FROM `role_to_page` WHERE `page_id` IN ('.multiple_esc_join($page_id).') AND `role_id` = '.esc($role_id);
			$result = rquery($query);

			$rights_id = array();
			while ($row = mysqli_fetch_row($result)) {
				$rights_id[] = $row[0];
			}
			$result->free();

			return $rights_id;
		} else {
			if(!preg_match('/^\d+$/', $page_id)) {
				$page_id = get_page_id_by_filename($page_id);
			}
			$return = 0;
			$key = "$page_id----$role_id";
			if(array_key_exists($key, $GLOBALS['user_role_cache'])) {
				$return = $GLOBALS['user_role_cache'][$key];
			} else {
				if(isset($GLOBALS['logged_in_user_id'])) {
					$query = 'SELECT `page_id` FROM `role_to_page` WHERE `page_id` = '.esc($page_id).' AND `role_id` = '.esc($role_id);
					$result = rquery($query);

					$rights_id = null;
					while ($row = mysqli_fetch_row($result)) {
						$rights_id = $row[0];
					}
					$result->free();

					if(!is_null($rights_id)) {
						$return = 1;
					}
				}
			}
			$GLOBALS['user_role_cache'][$key] = $return;

			if($log) {
				if(!$return) {
					right_issue("Die Seite mit der ID `$page_id` darf mit den aktuellen Rechten nicht ausgeführt werden. ");
					$query = 'INSERT IGNORE INTO `right_issues_pages` (`user_id`, `page_id`, `date`) VALUES ('.esc($GLOBALS['logged_in_user_id']).', '.esc($page_id).', now())';
					rquery($query);
					right_issue("Der Vorfall wird gespeichert und der Administrator informiert. ");
				}
			}

			return $return;
		}
	}

	function check_function_rights ($function, $log = 1) {
		if(array_key_exists('ignore_function_rights', $GLOBALS)) {
			return 1;
		} else {
			if(array_key_exists('user_role_id', $GLOBALS)) {
				$role_id = $GLOBALS['user_role_id'];
				return check_function_rights_role_id($function, $role_id, $log);
			} else {
				return 0;
			}
		}
	}

	function check_function_rights_role_id ($function, $role_id, $log = 1) {
		return 1;
		if(!$role_id || is_null($role_id)) {
			$role_id = $GLOBALS['user_role_id'];
		}

		$return = 0;
		if(isset($GLOBALS['logged_in_user_id'])) {
			$query = 'SELECT `id` FROM `function_rights` WHERE `name` = '.esc($function).' AND `role_id` = '.esc($role_id);
			$result = rquery($query);

			$rights_id = null;
			while ($row = mysqli_fetch_row($result)) {
				$rights_id = $row[0];
			}
			$result->free();

			if(!is_null($rights_id)) {
				$return = 1;
			}
		}

		if($log) {
			if(!$return) {
				right_issue("Die Funktion $function darf mit den aktuellen Rechten nicht ausgeführt werden. ");
				$query = 'INSERT IGNORE INTO `right_issues` (`user_id`, `function`, `date`) VALUES ('.esc($GLOBALS['logged_in_user_id']).', '.esc($function).', now())';
				rquery($query);
				right_issue("Der Vorfall wird gespeichert und der Administrator informiert. ");
			}
		}

		return $return;
	}

	function get_func_argNames($funcName) {
		if($funcName == 'include_once' || $funcName == 'include' || $funcName == 'require_once') {
			return array('filename');
		}

		$f = new ReflectionFunction($funcName);
		$result = array();
		foreach ($f->getParameters() as $param) {
			$result[] = $param->name;
		}
		return $result;
	}

	function getExceptionTraceAsString($exception) {
		$rtn = "";
		$count = 0;
		foreach ($exception->getTrace() as $frame) {
			$args = "";
			if (isset($frame['args'])) {
				$args = array();
				foreach ($frame['args'] as $arg) {
					if (is_string($arg)) {
						$args[] = "'" . $arg . "'";
					} elseif (is_array($arg)) {
						$args[] = "Array";
					} elseif (is_null($arg)) {
						$args[] = 'NULL';
					} elseif (is_bool($arg)) {
						$args[] = ($arg) ? "true" : "false";
					} elseif (is_object($arg)) {
						$args[] = get_class($arg);
					} elseif (is_resource($arg)) {
						$args[] = get_resource_type($arg);
					} else {
						$args[] = $arg;
					}
				}
				$args = join(", ", $args);
			}
			$rtn .= sprintf( "#%s %s(%s): %s(%s)\n",
				$count,
				$frame['file'],
				$frame['line'],
				$frame['function'],
				$args );
			$count++;
		}
		return $rtn;
	}

	// Idee: über diese Wrapperfunktion kann man einfach Queries mitloggen etc., falls notwendig.
	function rquery ($internalquery, $die = 1, $throw = 0, $show_warnings = 1) {
		$caller_args = '';
		$caller_function_without_extras = '';

		$query_addition = "";

		if($GLOBALS['show_backtrace']) {
			$backtrace = debug_backtrace();
			if(array_key_exists(1, $backtrace) && array_key_exists('args', $backtrace[1])) {
				$caller_file = $backtrace[0]['file'];
				$caller_line = $backtrace[0]['line'];
				if(array_key_exists(1, $backtrace) && array_key_exists('function', $backtrace[1])) {
					$caller_function_without_extras = $backtrace[1]['function'];
				}
				$caller_function = $caller_function_without_extras;

				$query_addition = "/* \n$caller_file, $caller_line".($caller_function ? "\n$caller_function\n" : '')." */";

				$args = get_func_argNames($caller_function_without_extras);
				$caller_args = '';
				$caller_args_array = array();
				foreach ($args as $id => $name) {
					$this_arg = '';
					if(
						array_key_exists(1, $backtrace) && 
						array_key_exists('args', $backtrace[1]) && 
						array_key_exists($id, $backtrace[1]['args'])
					) {
						if(is_null($backtrace[1]['args'][$id])) {
							$this_arg = 'null';
						} else if (is_array($backtrace[1]['args'][$id])) {
							$this_arg = 'array('.multiple_esc_join($backtrace[1]['args'][$id]).')';
						} else {
							$this_arg = esc($backtrace[1]['args'][$id]);
						}
					}
					$caller_args_array[] = "\$$name = ".$this_arg;
				}

				$e = new \Exception;
				$stack = getExceptionTraceAsString($e);

				$caller_args = implode(', ', $caller_args_array);
				$caller_function = "$caller_function($caller_args)\nFull Stack: \n $stack ";
			}
		}

		$start = microtime(true);
		$result = mysqli_query($GLOBALS['dbh'], $internalquery);
		$end = microtime(true);
		if($show_warnings && $GLOBALS['dbh']) {
			if(mysqli_connect_errno()) {
				mysql_error("MySQL-Error: ".mysqli_connect_error());
			}

			$count_warnings = mysqli_warning_count($GLOBALS['dbh']);
			if ($count_warnings > 0) {
				$e = mysqli_get_warnings($GLOBALS['dbh']);
				for ($i = 0; $i < $count_warnings; $i++) {
					mysql_warning("MySQL-Warning (".$e->errno."): <br><pre>".htmlentities($internalquery)."</pre>->&nbsp;<b>".$e->message."</b>");
					$e->next();
				}
			}
		}
		$used_time = $end - $start;
		$numrows = "&mdash;";
		if(!is_bool($result)) {
			$numrows = mysqli_num_rows($result);
		}

		$GLOBALS['queries'][] = array('query' => "$query_addition\n$internalquery", 'time' => $used_time, 'numrows' => $numrows);

		if($caller_function_without_extras) {
			if(array_key_exists($caller_function_without_extras, $GLOBALS['function_usage'])) {
				$GLOBALS['function_usage'][$caller_function_without_extras]['count']++;
				$GLOBALS['function_usage'][$caller_function_without_extras]['time'] += $used_time;
			} else {
				$GLOBALS['function_usage'][$caller_function_without_extras]['count'] = 1;
				$GLOBALS['function_usage'][$caller_function_without_extras]['time'] = $used_time;
				$GLOBALS['function_usage'][$caller_function_without_extras]['name'] = $caller_function_without_extras;
			}
		}

		if(!$result) {
			$msg = "Ung&uuml;ltige Anfrage: <p><pre>".$internalquery."</pre></p>".htmlentities(mysqli_error($GLOBALS['dbh']));
			if($die) {
				dier($msg, 1);
			} else if ($throw) {
				throw new Exception($msg);
			}
		}

		if($GLOBALS['rquery_print']) {
			print "<p>".htmlentities($internalquery)."</p>\n";
		}

		return $result;
	}

	function esc ($parameter) { // escape
		if(!is_array($parameter)) { // Kein array
			if(isset($parameter) && strlen($parameter)) {
				return '"'.mysqli_real_escape_string($GLOBALS['dbh'], $parameter).'"';
			} else {
				return 'NULL';
			}
		} else { // Array
			$str = join(', ', array_map('esc', array_map('my_mysqli_real_escape_string', $parameter)));
			return $str;
		}
	}

	function my_mysqli_real_escape_string ($arg) {
		return mysqli_real_escape_string($GLOBALS['dbh'], $arg);
	}

	function dier ($data, $enable_html = 0, $die = 1) {
		$debug_backtrace = debug_backtrace();
		$source_data = $debug_backtrace[0];

		$source = '';

		if(array_key_exists(1, $debug_backtrace) && array_key_exists('file', $debug_backtrace[1])) {
			@$source .= 'Aufgerufen von <b>'.$debug_backtrace[1]['file'].'</b>::<i>';
		}
		
		if(array_key_exists(1, $debug_backtrace) && array_key_exists('function', $debug_backtrace[1])) {
			@$source .= $debug_backtrace[1]['function'];
		}


		@$source .= '</i>, line '.htmlentities($source_data['line'])."<br />\n";

		if(array_key_exists("logged_in_user_id", $GLOBALS) && $GLOBALS['logged_in_user_id']) {
			print $source;
		}
		print "<pre>\n";
		ob_start();
		print_r($data);
		$buffer = ob_get_clean();
		if($enable_html) {
			print $buffer;
		} else {
			print htmlentities($buffer);
		}
		print "</pre>\n";
		if(array_key_exists('logged_in_user_id', $GLOBALS) && $GLOBALS['logged_in_user_id']) {
			print "Backtrace:\n";
			print "<pre>\n";
			foreach ($debug_backtrace as $trace) {
				print htmlentities(sprintf("\n%s:%s %s", $trace['file'], $trace['line'], $trace['function']));
			}
			print "</pre>\n";
		}
		if($die) {
			include_once("footer.php");
			exit();
		}
	}

	function multiple_esc_join ($data) {
		if(is_array($data)) {
			$data = array_map('esc', $data);
			$string = join(", ", $data);
			return $string;
		} else {
			return esc($data);
		}
	}

	function get_post_multiple_check ($names) {
		if(is_array($names)) {
			$return = 1;
			foreach ($names as $name) {
				if(!get_post($name)) {
					$return = 0;
					break;
				}
			}
			return $return;
		} else {
			return get_post($name);
		}
	}

	function get_cookie ($name) {
		if(array_key_exists($name, $_COOKIE)) {
			return $_COOKIE[$name];
		} else {
			return NULL;
		}
	}

	// Die get_-Funktionen sollen häßliche Konstrukte mit array_key_exists($bla, $_POST) vermeiden.
	function get_get ($name) {
		if(array_key_exists($name, $_GET)) {
			return $_GET[$name];
		} else {
			return NULL;
		}
	}

	function get_post ($name) {
		if(array_key_exists($name, $_POST)) {
			return $_POST[$name];
		} else {
			return NULL;
		}
	}

	function generate_random_string ($length = 50) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	function delete_old_session_ids ($user_id = null) {
		if($GLOBALS['already_deleted_old_session_ids']) {
			return;
		}

		if(table_exists_nocache('session_ids')) {
			$query = 'DELETE FROM `session_ids` WHERE `creation_time` <= now() - INTERVAL 1 DAY';
			rquery($query);
			if($user_id) {
				$query = 'DELETE FROM `session_ids` WHERE `user_id` = '.esc($user_id);
				rquery($query);
			}
			$GLOBALS['already_deleted_old_session_ids'] = 1;
		}
	}

	function print_subnavigation ($parent) {
		$query = 'SELECT `name`, `file`, `page_id`, `show_in_navigation`, `parent` FROM `view_account_to_role_pages` WHERE `user_id` = '.esc($GLOBALS['logged_in_user_id']).' AND `parent` = '.esc($parent).' AND `show_in_navigation` = "1" ORDER BY `name`';
		$result = rquery($query);

		$str = '';
		$subnav_selected = 0;

		if(mysqli_num_rows($result)) {
			$str .= "\t<ul>\n";
			while ($row = mysqli_fetch_row($result)) {
				if($row[2] == get_get('page')) {
					$str .= "\t\t<li style='font-weight: bold;'><a href='admin.php?page=".$row[2]."'>&rarr; $row[0]</a></li>\n";
					$subnav_selected = 1;
				} else {
					$str .= "\t\t<li><a href='admin.php?page=".$row[2]."'>$row[0]</a></li>\n";
				}
			}
			$result->free();
			$str .= "\t</ul>\n";
		}

		return array($subnav_selected, $str);
	}

	/* MySQL-get-Funktionen */

	/*
		Ich habe hier "auf Vorrat" gearbeitet. Fast alle dieser Funktionen sind irgendwie
		sinnvoll einsetzbar. Sobald das der Fall ist, will ich sie einfach benutzen können.
		Der Overhead ist vergleichsweise klein und wiegt den Aufwand im späteren Programmieren
		bei Weitem auf.
	 */

	function merge_data ($table, $from, $to) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if(preg_match('/^[a-z0-9A-Z_]+$/', $table)) {
			foreach ($from as $this_from) {
				$where = array('id' => $from);
				$data = get_foreign_key_deleted_data($GLOBALS['dbname'], $table, $where);

				foreach ($data as $this_table => $this_table_val) {
					if($this_table != $table) {
						$where = '';
						$refkey = '';

						$this_where = array();

						$foreign_keys = get_foreign_key_tables($GLOBALS['dbname'], $this_table);
						foreach ($foreign_keys as $this_foreign_key) {
							if($this_foreign_key[3] == $table) {
								$refkey = $this_foreign_key[1];
							}
						}

						if($refkey) {
							$primary_keys = get_primary_keys($GLOBALS['dbname'], $this_table);
							$i = 0;
							foreach ($this_table_val as $this_table_val_2) {
								$this_where_str = '';
								foreach ($primary_keys as $this_primary_key) {
									$this_where_str .= ' (';
									$this_where_str .= "`$this_primary_key[0]` = ".esc($this_table_val_2[$this_primary_key[0]]);
									$this_where_str .= ') OR ';

									$i++;
								}
								$this_where[] = $this_where_str;
							}
							$where = join(' ', $this_where);
							$where = preg_replace('/\s+OR\s*$/', '', $where);

							if($where) {
								if(preg_match('/=/', $where)) {
									$query = "UPDATE `$this_table` SET `$refkey` = ".esc($to)." WHERE $where";
									stderrw($query);
									$result = rquery($query);
								} else {
									die("Es konnte kein valides `$where entwickelt werden`: $where.");
								}
							} else {
								die("Es konnte kein `$where entwickelt werden`.");
							}
						}
					}
				}
			}

			$wherea = array();
			foreach ($from as $this_from) {
				if($this_from != $to) {
					$wherea[] = $this_from;
				}
			}
			$where = '`id` IN ('.join(', ', array_map('esc', $wherea)).')';
			$query = "DELETE FROM `$table` WHERE $where";
			$result = rquery($query);

			if($result) {
				return success('Die Keys wurden erfolgreich gelöscht. ');
			} else {
				return error('Die Daten wurden nicht erfolgreich gemergt. ');
			}
		} else {
			return error('Die Tabelle `'.htmlentities($table).'` konnte ist nicht valide.');
		}
	}

	function get_page_file_by_id ($id) {
		$key = "get_page_file_by_id($id)";
		if(array_key_exists($key, $GLOBALS['memoize'])) {
			return $GLOBALS['memoize'][$key];
		}

		$query = 'SELECT `file` FROM `page` WHERE `id` = '.esc($id);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}
		$result->free();

		$GLOBALS['memoize'][$key] = $id;

		return $id;
	}

	function get_page_info_by_id ($id) {
		$query = 'SELECT `page_id`, `info` FROM `page_info` WHERE `page_id` ';
		if(is_array($id)) {
			$query .= 'IN ('.join(', ', array_map('esc', $id)).')';
		} else {
			$query .= ' = '.esc($id);
		}
		$result = rquery($query);

		$data = array();

		while ($row = mysqli_fetch_row($result)) {
			if(is_array($id)) {
				$data[$row[0]] = $row[1];
			} else {
				$data = $row[1];
			}
		}
		$result->free();

		return $data;
	}

	function get_page_name_by_id ($id) {
		$query = 'SELECT `name` FROM `page` WHERE `id` = '.esc($id);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}
		$result->free();

		return $id;
	}

	function create_page_id_by_name_array () {
		$query = 'SELECT `name`, `id` FROM `page`';
		$result = rquery($query);

		$id = array();

		while ($row = mysqli_fetch_row($result)) {
			$id[$row[1]] = $row[0];
		}
		$result->free();

		return $id;
	}

	function get_role_id_by_user ($name) {
		$key = "get_role_id_by_user($name)";
		if(array_key_exists($key, $GLOBALS['memoize'])) {
			$return = $GLOBALS['memoize'][$key];
		} else {
			$query = 'SELECT `role_id` FROM `role_to_user` `ru` LEFT JOIN `users` `u` ON `ru`.`user_id` = `u`.`id` WHERE `u`.`id` = '.esc($name);
			$result = rquery($query);

			$return = NULL;

			while ($row = mysqli_fetch_row($result)) {
				$return = $row[0];
			}
			$result->free();
			$GLOBALS['memoize'][$key] = $return;
		}

		return $return;
	}

	function get_account_enabled_by_id ($id) {
		$query = 'select enabled from users where id = '.esc($id);

		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}
		$result->free();

		return $id;
	}

	function get_role_name ($id) {
		$query = 'SELECT `name` FROM `role` WHERE `id` = '.esc($id).' limit 1';
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}
		$result->free();

		return $id;
	}

	function get_role_id ($name) {
		$query = 'SELECT `id` FROM `role` WHERE `name` = '.esc($name).' limit 1';
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}
		$result->free();

		return $id;
	}

	function get_user_id ($name) {
		$query = 'SELECT `id` FROM `users` WHERE `username` = '.esc($name);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}
		$result->free();

		return $id;
	}

	function get_user_name ($id) {
		$query = 'SELECT `username` FROM `users` WHERE `id` = '.esc($id);
		$result = rquery($query);

		$name = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$name = $row[0];
		}
		$result->free();

		return $name;
	}

	function get_get_int ($key) {
		$data = get_get($key);
		if(preg_match('/^\d+$/', $data)) {
			return $data;
		} else {
			return '';
		}
	}

	function get_seitentext () {
		$tpnr = '';
		if(array_key_exists('this_page_number', $GLOBALS) && !is_null($GLOBALS['this_page_number'])) {
			$tpnr = $GLOBALS['this_page_number'];
		} else {
			$tpnr = get_page_id_by_filename('welcome.php');
		}

		$query = 'SELECT `text` FROM `seitentext` WHERE `page_id` = '.esc($tpnr);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			if($row[0]) {
				$id = $row[0];
			}
		}
		$result->free();

		return $id;
	}

	/* MySQL-create-Funktionen */

	/*
		Trägt die verschiedenen Datensatztypen ein.
	 */

	function create_role($role, $id = null) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT IGNORE INTO `role` (`name`) VALUES ('.esc($role).')';
		if(!is_null($id)) {
			$query = 'INSERT IGNORE INTO `role` (`id`, `name`) VALUES ('.esc($id).', '.esc($role).')';
		}
		$result = rquery($query);
		if($result) {
			return success('Die Rolle wurde erfolgreich eingetragen.');
		} else {
			return error('Die Rolle konnte nicht eingetragen werden.');
		}
	}


	function create_user($name, $password, $role) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$salt = generate_random_string(100);
		$query = 'INSERT IGNORE INTO `users` (`username`, `password_sha256`, `salt`) VALUES ('.esc($name).', '.esc(hash('sha256', $password.$salt)).', '.esc($salt).')';
		$result = rquery($query);
		if($result) {
			$id = get_user_id($name);
			$query = 'INSERT IGNORE INTO `role_to_user` (`role_id`, `user_id`) VALUES ('.esc($role).', '.esc($id).')';
			$result = rquery($query);

			if($result) {
				return success('Der User wurde mit seiner Rolle erfolgreich eingetragen.');
			} else {
				return error('Der User konnte eingefügt, aber nicht seiner Rolle zugeordnet werden. ');
			}
		} else {
			return error('Der User konnte nicht eingetragen werden. ');
		}
	}


	function create_function_right ($role_id, $name) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT IGNORE INTO `function_rights` (`name`, `role_id`) VALUES ('.esc($name).', '.esc($role_id).')';
		$result = rquery($query);
		if($result) {
			success('Das Funktionsrecht wurde erfolgreich eingetragen.');
		} else {
			error('Das Funktionsrecht konnte nicht eingetragen werden.');
		}
	}

	/* MySQL-delete-Funktionen */

	function delete_role ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'DELETE FROM `role` WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Die Rolle wurde erfolgreich gelöscht.');
		} else {
			error('Die Rolle konnte nicht gelöscht werden.');
		}
	}

	function delete_page ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'DELETE FROM `page` WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Die Seite wurde erfolgreich gelöscht.');
		} else {
			error('Die Seite konnte nicht gelöscht werden.');
		}
	}

	function delete_user ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'DELETE FROM `users` WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Der Benutzer wurde erfolgreich gelöscht.');
		} else {
			error('Der Benutzer konnte nicht gelöscht werden.');
		}
	}

	function delete_funktion_rights ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'DELETE FROM `function_rights` WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Das Funktionsrecht wurde erfolgreich gelöscht.');
		} else {
			error('Das Funktionsrecht konnte nicht gelöscht werden.');
		}
	}

	/* MySQL-update-Funktionen */

	function get_salt ($id) {
		$query = 'SELECT `salt` FROM `users` WHERE `id` = '.esc($id);
		$result = rquery($query);

		$id = NULL;

		while ($row = mysqli_fetch_row($result)) {
			$id = $row[0];
		}
		$result->free();

		return $id;

	}

	function get_and_create_salt ($id) {
		if(!check_function_rights(__FUNCTION__)) { return; }

		$result = get_salt($id);

		if($result) {
			return $result;
		} else {
			$salt = generate_random_string(100);
			$query = 'UPDATE `users` SET `salt` = '.esc($salt).' WHERE `id` = '.esc($id);
			$results = rquery($query);

			if($results) {
				$id = get_salt($id);
				if($id) {
					message('Salt eingefügt. ');
					return $id;
				} else {
					message('Salt konnte nicht eingefügt werden. ');
					return null;
				}
			} else {
				die(mysqli_error());
			}
		}
	}

	function update_own_data ($password) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$salt = get_and_create_salt($GLOBALS['logged_in_user_id']);
		$query = 'UPDATE `users` SET `password_sha256` = '.esc(hash('sha256', $password.$salt)).' WHERE `id` = '.esc($GLOBALS['logged_in_user_id']);
		$result = rquery($query);
		if($result) {
			success('Ihr Passwort wurde erfolgreich geändert. ');
		} else {
			message('Die Benutzerdaten konnten nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
	}

	function update_user ($name, $id, $password, $role, $enable, $accpubdata) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$salt = get_and_create_salt($id);
		$enabled = 1;
		if(!$enable) {
			$enabled = 0;
		}
		$query = '';
		if($password) {
			$query = 'UPDATE `users` SET `username` = '.esc($name).', `password_sha256` = '.esc(hash('sha256', $password.$salt)).', `enabled` = '.esc($enabled).', `accepted_public_data` = '.esc($accpubdata).' WHERE `id` = '.esc($id);
		} else {
			$query = 'UPDATE `users` SET `username` = '.esc($name).', `enabled` = '.esc($enabled).', `accepted_public_data` = '.esc($accpubdata).' WHERE `id` = '.esc($id);
		}
		$result = rquery($query);
		if($result) {
			$query = 'INSERT INTO `role_to_user` (`role_id`, `user_id`) VALUES ('.esc($role).', '.esc($id).') ON DUPLICATE KEY UPDATE `role_id` = '.esc($role);
			$result = rquery($query);
			if($result) {
				success('Die Benutzerdaten und Rollenzuordnungen wurden erfolgreich geändert. ');
			} else {
				success('Die Benutzerdaten wurden erfolgreich geändert, aber die Rollenänderung hat nicht geklappt. ');
			}
		} else {
			message('Die Benutzerdaten konnten nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
		$GLOBALS['reload_page'] = 1;
	}

	function update_startseitentext ($startseitentext) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = '';
		if(get_startseitentext()) {
			$query = 'UPDATE `startseite` SET `text` = '.esc($startseitentext);;
		} else {
			$query = 'INSERT INTO `startseite` (`text`) VALUES ('.esc($startseitentext).');';
		}
		$result = rquery($query);

		if($result) {
			success('Startseitentext erfolgreich editiert. ');
		} else {
			success('Startseitentext konnte nicht editiert werden. ');
		}
	}

	function update_funktion_rights ($id, $name) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'UPDATE `function_rights` SET `name` = '.esc($name).' WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			success('Das Funktionsrecht wurde erfolgreich geändert.');
		} else {
			message('Das Funktionsrecht konnte nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
	}

	function start_transaction () {
		rquery('SET autocommit = 0');
		rquery('START TRANSACTION');
	}

	function commit () {
		rquery('COMMIT');
		rquery('SET autocommit = 1');
	}

	function rollback () {
		rquery('ROLLBACK');
		rquery('SET autocommit = 1');
	}

	function fill_front_zeroes ($str, $len, $pre = '0') {
		while (strlen($str) < $len) {
			$str = "$pre$str";
		}
		return $str;
	}

	/*
		id ist die page-id
		role_to_page muss ein array sein mit ids von rollen, die der seite
		zugeordnet werden sollen
	 */
	function update_or_create_role_to_page ($id, $role_to_page) {
		if(!check_function_rights(__FUNCTION__)) { return; }

		if(isset($role_to_page) && !is_array($role_to_page)) {
			$temp = array();
			$temp[] = $role_to_page;
		}

		if(is_array($role_to_page) && count($role_to_page)) {
			$at_least_one_role_set = 0;
			foreach ($role_to_page as $trole) {
				$rname = get_role_name($trole);
				if($rname) {
					$at_least_one_role_set = 1;
				}
			}

			$roles_cleared = 0;
			if($at_least_one_role_set) {
				$query = 'DELETE FROM `'.$GLOBALS['dbname'].'`.`role_to_page` WHERE `page_id` = '.esc($id);
				$result = rquery($query);
				if($result) {
					success("Die Rollen wurden erfolgreich geklärt. ");
					$roles_cleared = 1;
				} else {
					error("Die Rollen wurden NICHT erfolgreich geklärt. ");
				}
			}

			if($roles_cleared) {
				foreach ($role_to_page as $trole) {
					$rname = get_role_name($trole);
					if($rname) {
						$query = 'INSERT IGNORE INTO `'.$GLOBALS['dbname'].'`.`role_to_page` (`role_id`, `page_id`) VALUES ('.esc($trole).', '.esc($id).')';
						$result = rquery($query);
						if($result) {
							success("Die Rolle $rname wurde erfolgreich hinzugefügt. ");
						} else {
							error("Die Rolle $rname konnte nicht eingefügt werden. ");
						}
					} else {
						error("Die Rolle mit der ID $trole existiert nicht. ");
					}
				}
			}
		}
	}

	function create_new_page ($name, $file, $show_in_navigation, $parent, $role_to_page, $beschreibung, $hinweis) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if($parent == "") {
			$parent = null;
		}
		$query = 'INSERT IGNORE INTO `'.$GLOBALS['dbname'].'`.`page` (`name`, `file`, `show_in_navigation`, `parent` ) VALUES ('.esc(array($name, $file, $show_in_navigation, $parent)).')';
		$result = rquery($query);
		if($result) {
			$id = get_page_id_by_filename($file);

			if($id) {
				if(isset($role_to_page)) {
					update_or_create_role_to_page($id, $role_to_page);
				}

				if(isset($beschreibung)) {
					update_page_info($id, $beschreibung);
				}

				if(isset($hinweis)) {
					update_hinweis($id, $hinweis);
				}

				success('Die Seite wurde erfolgreich hinzugefügt. ');
			} else {
				error('Die letzte insert-id konnte nicht ermittelt werden, aber die Seite wurde erstellt. ');
			}
		} else {
			message('Die Seite konnte nicht erfolgreich hinzugefügt werden. ');
		}
	}

	function update_page_full($id, $name, $file, $show_in_navigation, $parent, $role_to_page, $beschreibung, $hinweis) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if($parent == "") {
			$parent = null;
		}
		$query = 'UPDATE `page` SET `name` = '.esc($name).', `file` = '.esc($file).', `show_in_navigation` = '.esc($show_in_navigation).', `parent` = '.esc($parent).' WHERE `id` = '.esc($id);
		$result = rquery($query);
		if($result) {
			if(isset($role_to_page)) {
				update_or_create_role_to_page($id, $role_to_page);
			}

			if(isset($beschreibung)) {
				update_page_info($id, $beschreibung);
			}

			if(isset($hinweis)) {
				update_hinweis($id, $hinweis);
			}

			success('Die Seite wurde erfolgreich geändert. ');
		} else {
			message('Die Seite konnte nicht geändert werden oder es waren keine Änderungen notwendig. ');
		}
	}

	function update_role ($id, $name) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'UPDATE `role` SET `name` = '.esc($name).' WHERE `id` = '.esc($id);;
		$result = rquery($query);
		if($result) {
			success('Die Rolle wurde erfolgreich geändert. ');
		} else {
			message('Die Rolle konnte nicht geändert werden oder es waren keine Änderungen notwendig. ');
		}
	}

	function update_text ($page_id, $text) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT INTO `seitentext` (`page_id`, `text`) VALUES ('.esc($page_id).', '.esc($text).') ON DUPLICATE KEY UPDATE `text` = '.esc($text);
		$result = rquery($query);
		if($result) {
			success('Der Seitentext wurde erfolgreich geändert.');
		} else {
			message('Der Seitentext konnte nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
	}

	function update_hinweis ($page_id, $hinweis) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		if(get_page_name_by_id($page_id)) {
			if(isset($hinweis)) {
				$query = 'INSERT INTO `hinweise` (`page_id`, `hinweis`) VALUES ('.esc($page_id).', '.esc($hinweis).') ON DUPLICATE KEY UPDATE `hinweis` = '.esc($hinweis);
				$result = rquery($query);
				if($result) {
					success('Der neue Hinweis wurde erfolgreich geändert.');
				} else {
					warning('Der Hinweis konnte nicht geändert werden oder es waren keine Änderungen notwendig.');
				}
			} else {
				warning("Leerer Hinweis. ");
			}
		} else {
			error("Falsche Page-ID. ");
		}
	}

	function update_page_info ($id, $info) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT INTO `page_info` (`page_id`, `info`) VALUES ('.esc($id).', '.esc($info).') ON DUPLICATE KEY UPDATE `info` = '.esc($info);
		$result = rquery($query);
		if($result) {
			success('Die Seiteninfo wurde erfolgreich geändert.');
		} else {
			message('Die Seiteninfo konnte nicht geändert werden oder es waren keine Änderungen notwendig.');
		}
	}

	/* Darstellungsfunktionen */

	function simple_edit ($columnnames, $table, $columns, $datanames, $block_user_id, $htmlentities = 1, $special_input = array(), $order_by = null, $classes = array()) {
		$query = 'SELECT `id`, `'.join('`, `', $columnnames).'` FROM `'.$table.'`';
		if($order_by) {
			$query .= ' ORDER BY `'.join('`, `', $order_by).'`';
		}
		$result = rquery($query);

?>
			<table>
				<tr>
<?php
		foreach ($columns as $c) {
?>
					<th><?php print $c ?></th>
<?php
		}
?>
				</tr>
<?php
		while($row = mysqli_fetch_row($result)) {
?>
			<tr>
				<form class="form" method="post" action="admin.php?page=<?php print htmlentities($GLOBALS['this_page_number']); ?>">
					<input type="hidden" name="update_<?php print $table; ?>" value="1" />
<?php
			$i = 0;
			foreach ($datanames as $c) {
				if(!is_null($special_input) && is_array($special_input) && array_key_exists($i, $special_input)) {
					print $special_input[$i];
				} else {
					if($i == 0) {
?>
								<input type="hidden" value="<?php print htmlentities($row[0]); ?>" name="<?php print htmlentities($datanames[0]); ?>" />
<?php
					} else {
						$class = '';
						if(array_key_exists($i, $classes)) {
							$class = " class='".$classes[$i]."'";
						}
?>
								<td><input <?php print $class; ?> type="<?php print $c == 'password' ? 'password' : 'text'; ?>" name="<?php print $c; ?>" placeholder="<?php print $c; ?>" value="<?php print $c == 'password' ? '' : ($htmlentities ? htmlentities($row[$i]) : $row[$i]); ?>" /></td>
<?php
					}
				}
				$i++;
			}
?>
					<td><input type="submit" value="Speichern" /></td>
<?php
			if($block_user_id && $GLOBALS['logged_in_data'][0] == $row[0]) {
?>
						<td><button name="delete" value="1" disabled>Löschen</button></td>
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
				<form class="form" method="post" action="admin.php?page=<?php print htmlentities($GLOBALS['this_page_number']); ?>">
					<input type="hidden" name="create_<?php print $table; ?>" value="1" />
<?php
		$i = 0;
		foreach ($datanames as $c) {
			if($i != 0) {
				$class = '';
				if(array_key_exists($i, $classes)) {
					$class = " class='".$classes[$i]."'";
				}
?>
							<td><input <?php print $class; ?> type="<?php print $c == 'password' ? 'password' : 'text'; ?>" name="new_<?php print $c; ?>" placeholder="<?php print $c; ?>" /></td>
<?php
			}
			$i++;
		}
?>
					<td><input type="submit" class="submit" value="Speichern" /></td>
					<td>&mdash;</td>
				</form>
			</tr>
		</table>
<?php
	}

	function create_select_str ($data, $chosen, $name, $allow_empty = 0, $class = '', $attr = array(), $onchange = null, $width = null) {
		$attr_str = '';
		if(count($attr)) {
			foreach ($attr as $this_name => $value) {
				$attr_str .= " data-$this_name=".json_encode($value);
			}
		}
		$changestr = '';
		if(!is_null($onchange)) {
			$changestr = " onchange='$onchange' ";
		}
		$style = '';
		if(!is_null($width)) {
			$style = ' style="width: '.$width.'px;" ';
		}
		$str = '<select '.$style.' '.$changestr.' name="'.htmlentities($name).'"'.($class ? " class='$class'" : '').$attr_str.'>'."\n";
		if($allow_empty) {
			$str .= '<option value="">&mdash;</option>'."\n";
		}
		foreach ($data as $datum) {
			if(is_array($datum)) {
				$str .= '<option value="'.$datum[0].'"'.(($chosen && $datum[0] == $chosen) ? ' selected' : '').'>'.htmlentities($datum[1]).'</option>'."\n";
			} else {
				$str .= '<option value="'.$datum.'" '.(($chosen && $datum == $chosen) ? ' selected' : '').'>'.htmlentities($datum).'</option>'."\n";
			}
		}
		$str .= '</select>';
		return $str;
	}

	function create_select ($data, $chosen, $name, $allow_empty = 0, $autosubmit_warning_yesno = null, $resetdefault = null, $noautosubmit = 0) {
		if(!is_null($autosubmit_warning_yesno)) {
			$autosubmit_warning_yesno = " autosubmitwarning='$autosubmit_warning_yesno' ";
		} else {
			$autosubmit_warning_yesno = "";
		}

		if(!is_null($resetdefault)) {
			$resetdefault = " resetdefault='$resetdefault' ";
		} else {
			$resetdefault = "";
		}

		if($noautosubmit) {
			$noautosubmit = " noautosubmit='1' ";
		}
?>
		<select <?php print $autosubmit_warning_yesno; print " "; print $resetdefault; print " "; print $noautosubmit; ?> name="<?php print htmlentities($name); ?>">
<?php
		if($allow_empty) {
?>
				<option value="">&mdash;</option>
<?php
		}
		foreach ($data as $datum) {
			if(is_array($datum)) {
?>
					<option value="<?php print $datum[0]; ?>" <?php print ($chosen && $datum[0] == $chosen) ? 'selected' : ''; ?>><?php print htmlentities($datum[1]); ?></option>
<?php
			} else {
?>
					<option value="<?php print $datum; ?>" <?php print ($chosen && $datum == $chosen) ? 'selected' : ''; ?>><?php print htmlentities($datum); ?></option>
<?php
			}
		}
?>
		</select>
<?php
	}

	/* Datenfunktionen */

	function create_page_info_parent ($parent, $user_role_id_data = null) {
		$page_infos = array();
		$query = 'SELECT `p`.`id`, `p`.`name`, `p`.`file`, `pi`.`info`, `p`.`parent` FROM `page` `p` LEFT JOIN `page_info` `pi` ON `pi`.`page_id` = `p`.`id` WHERE `p`.`show_in_navigation` = "1" AND `parent` = '.esc($parent);
		if(isset($user_role_id_data)) {
			$query .= ' AND `p`.`id` IN (SELECT `page_id` FROM `role_to_page` WHERE `role_id` = '.esc($user_role_id_data).')';
		}
		$query .= ' ORDER BY p.name';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$page_infos[$row[0]] = array($row[0], $row[1], $row[2], $row[3], $row[4]);
		}
		$result->free();
		return $page_infos;
	}

	function get_father_page ($id) {
		$query = 'SELECT `parent` FROM `page` WHERE `id` = '.esc($id);
		$result = rquery($query);

		if(mysqli_num_rows($result)) {
			$father = null;
			while ($row = mysqli_fetch_row($result)) {
				$father = $row[0];
			}
			$result->free();
			return $father;
		} else {
			return null;
		}
	}

	function create_page_info () {
		$page_infos = array();
		$query = 'select p.id, p.name, p.file, pi.info, p.parent from page p left join page_info pi on pi.page_id = p.id where p.show_in_navigation = "1" ORDER BY p.name';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$page_infos[$row[0]] = array($row[0], $row[1], $row[2], $row[3], $row[4]);
		}
		$result->free();
		return $page_infos;
	}

	function create_seiten_array () {
		$seiten = array();
		$query = 'SELECT `id`, `name`, `file` FROM `page`';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$seiten[$row[0]] = array($row[0], $row[1], $row[2]);
		}
		$result->free();
		return $seiten;
	}

	function print_hinweis_for_page ($chosen_page) {
		$hinweis = get_hinweis_for_page($chosen_page);
		if($hinweis) {
			hint("Hinweis: <span class='blue_text'>".htmlentities($hinweis)."</span>");
		}
	}

	function get_hinweis_for_page ($chosen_page) {
		$query = 'SELECT `hinweis` FROM `hinweise` WHERE `page_id` = '.esc($chosen_page);
		$result = rquery($query);
		$hinweis = '';
		while ($row = mysqli_fetch_row($result)) {
			if(strlen($row[0]) && !preg_match('/^\s*$/', $row[0])) {
				$hinweis = $row[0];
			}
		}
		$result->free();
		return $hinweis;
	}

	function get_roles_for_page ($pageid) {
		$rollen = array();
		$query = 'SELECT `role_id` FROM `role_to_page` WHERE `page_id` = '.esc($pageid);
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$rollen[] = $row[0];
		}
		$result->free();
		return $rollen;
	}

	function create_page_parent_array () {
		$rollen = array();
		$query = 'SELECT `id`, `name` FROM `page` WHERE `parent` IS NULL AND `file` IS NULL';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$rollen[$row[0]] = array($row[0], $row[1]);
		}
		$result->free();
		return $rollen;
	}

	function create_turnus_array () {
		$rollen = array();
		$query = 'SELECT `id`, `name`, `anzahl_monate` FROM `turnus` order by `anzahl_monate` asc';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$rollen[$row[0]] = array($row[0], $row[1], $row[2]);
		}
		$result->free();
		return $rollen;
	}

	function create_rollen_array () {
		$rollen = array();
		$query = 'SELECT `id`, `name` FROM `role`';
		$result = rquery($query);
		while ($row = mysqli_fetch_row($result)) {
			$rollen[$row[0]] = array($row[0], $row[1]);
		}
		$result->free();
		return $rollen;
	}

	function create_user_array ($role = 0, $specific_role = null) {
		$user = array();
		if($role) {
			$query = 'SELECT `u`.`id`, `u`.`username`, `r`.`role_id` FROM `users` `u` JOIN `role_to_user` `r` ON `r`.`user_id` = `u`.`id`';
			if(isset($specific_role)) {
				$query .= ' WHERE `role_id` = '.esc($specific_role);
			}
			$result = rquery($query);
			while ($row = mysqli_fetch_row($result)) {
				$user[$row[0]] = array($row[0], $row[1], $row[2]);
			}
			$result->free();
			return $user;
		} else {
			$query = 'SELECT `id`, `username` FROM `users`';
			$result = rquery($query);
			while ($row = mysqli_fetch_row($result)) {
				$user[$row[0]] = array($row[0], $row[1]);
			}
			$result->free();
			return $user;
		}
	}

	/* Hilfsfunktionen */

	function global_exists ($name) {
		if(array_key_exists($name, $GLOBALS) && !empty($GLOBALS[$name])) {
			return 1;
		} else {
			return 0;
		}
	}

	function show_output ($name, $color) {
		if(global_exists($name)) {
			print "<div class='square'>\n";
			print "<div class='one'>\n";
			if(file_exists("./i/$name.svg")) {
				print "<img style='min-height: 30px; height: auto; max-height: 60px; top: 0px; bottom: 0px; margin: auto;' src='./i/$name.svg' />\n";
			}
			print "</div>\n";
			print "<div class='two'>\n";
			$this_output = $GLOBALS[$name];
			if(is_array($this_output)) {
				$this_output = array_unique($this_output);
			} else {

				$this_output = array($this_output);
			}
			if($color) {
				if(count($this_output) > 1) {
					print "<ul>\n";
				}
				foreach ($this_output as $this_output_item) {
					if(count($this_output) > 1) {
						print "<li>\n";
					}
					print "<span class='message_text'>".$this_output_item."</span>\n";
					if(count($this_output) > 1) {
						print "</li>\n";
					}
				}
				if(count($this_output) > 1) {
					print "</ul>\n";
				}
			}
			print "</div>\n";
			print "</div>\n";
			print "<div class='clear_both' /><br />\n";
			$GLOBALS[$name] = array();
			print "</div>\n";
		}
	}

	function get_page_id_by_name ($file) {
		if(is_null($file) || !$file) {
			return null;
		}

		$key = "get_page_id_by_name($file)";
		if(array_key_exists($key, $GLOBALS['memoize'])) {
			return $GLOBALS['memoize'][$key];
		}

		$return = null;

		$query = 'SELECT `id` FROM `page` WHERE `name` = '.esc($file);
		$result = rquery($query);

		$return = '';

		while ($row = mysqli_fetch_row($result)) {
			$return = $row[0];
		}
		$result->free();

		$GLOBALS['memoize'][$key] = $return;

		return $return;
	}


	function get_page_id_by_filename ($file) {
		if(is_null($file) || !$file) {
			return null;
		}

		$key = "get_page_id_by_filename($file)";
		if(array_key_exists($key, $GLOBALS['memoize'])) {
			return $GLOBALS['memoize'][$key];
		}

		$return = null;

		// Falls $file = aktuelle Seite, dann einfach &page=... zurückgeben
		if(get_get('page') && get_page_file_by_id(get_get('page')) == $file) {
			$return = get_get('page');
		} else {
			$query = 'SELECT `id` FROM `page` WHERE `file` = '.esc($file);
			$result = rquery($query);

			$return = '';

			while ($row = mysqli_fetch_row($result)) {
				$return = $row[0];
			}
			$result->free();
		}

		$GLOBALS['memoize'][$key] = $return;

		return $return;
	}

	/* Zuordnungsfunktionen */

	function assign_page_to_role ($role_id, $page_id) {
		if(!check_function_rights(__FUNCTION__)) { return; }
		$query = 'INSERT IGNORE INTO `role_to_page` (`role_id`, `page_id`) VALUES ('.esc($role_id).', '.esc($page_id).')';
		$result = rquery($query);
		if($result) {
			success("Die Seite wurde erfolgreich zur Rolle hinzugefügt. ");
			if($GLOBALS['user_role_id'] == $role_id) {
				$GLOBALS['reload_page'] = 1;
			}
		} else {
			error("Die Seite konnte nicht zur Rolle hinzugefügt werden. ");
		}
	}

	/* Systemfunktionen */

	function stderrw ($str) {
		trigger_error($str, E_USER_WARNING);
	}

	function add_to_output ($name, $msg) {
		if($name) {
			if($msg) {
				$GLOBALS[$name][] = $msg;
			}
		} else {
			die(htmlentities($name)." existiert nicht!");
		}
	}

	function mysql_error ($message) {
		add_to_output("mysql_error", $message);
	}

	function error ($message) {
		add_to_output("error", $message);
		return 0;
	}

	function hint ($message) {
		add_to_output("hint", $message);
		show_output("hint", $message);
	}

	function success ($message) {
		add_to_output("success", $message);
		return 1;
	}

	function trash ($message) {
		add_to_output("trash", $message);
	}

	function mysql_warning ($message) {
		add_to_output("mysql_warning", $message);
	}

	function warning ($message) {
		add_to_output("warning", $message);
		return 1;
	}

	function debug ($message) {
		add_to_output("debug", $message);
	}

	function right_issue ($message) {
		add_to_output("right_issue", $message);
	}

	function message ($message) {
		add_to_output("message", $message);
		return 1;
	}


	function return_checked_or_not ($value) {
		if(is_array($value)) {
			$data = array();
			foreach ($value as $this_value) {
				$data[] = return_checked_or_not($this_value);
			}
			return $data;
		} else {
			if($value) {
				return '&#9989;';
			} else {
				return '&#10060;';
			}
		}
	}


	function replace_newlines_htmlentities ($str) {
		$str = preg_replace("/[\n\r]/", "<br>", $str);

		return htmlentities($str);
	}

	function nbsp_every_n ($name, $n) {
		$str = wordwrap($name, $n, "NBSPNBSPNBSPNBSPNBSP", true);
		$str = preg_replace("/NBSPNBSPNBSPNBSPNBSP/", '&nbsp;', $str); 
		return $str;
	}

	function shy_every_n ($name, $n) {
		$str = wordwrap($name, $n, "SHYSHYSHYSHYSHY", true);
		$str = preg_replace("/SHYSHYSHYSHYSHY/", '&shy;', $str); 
		return $str;
	}

	function get_get_e($name) {
		return htmlentities(get_get($name));
	}

	function get_cached_result ($id, $query, $cache_name, $full_row = 0, $remove_first_element = 0, $default = null) {
		# BEDINGUNG: $row[0] muss die ID sein!!!
		if(!array_key_exists($id, $GLOBALS[$cache_name])) {
			$result = rquery($query);

			while ($row = mysqli_fetch_row($result)) {
				$t_id = $row[0];

				if($remove_first_element) {
					unset($row[0]);
					\array_splice($row, 1, 0);
				}

				if($full_row) {
					$GLOBALS[$cache_name][$t_id] = $row;
				} else {
					$GLOBALS[$cache_name][$t_id] = $row[1 - $remove_first_element];
				}
			}
		}
		if(array_key_exists($id, $GLOBALS[$cache_name])) {
			return $GLOBALS[$cache_name][$id];
		} else {
			return $default;
		}
	}


	function get_single_row_from_query ($query) {
		$id = array();

		$res = rquery($query);

		while ($row = mysqli_fetch_row($res)) {
			$id = $row;
		}
		$res->free();

		return $id;
	}

	function get_single_value_from_query ($query) {
		$id = null;

		$res = rquery($query);

		while ($row = mysqli_fetch_row($res)) {
			$id = $row[0];
		}
		$res->free();

		return $id;
	}

	function get_local_version () {
		$this_local_version = file_get_contents("version");
		$this_local_version = rtrim($this_local_version, "\n");
		return $this_local_version;
	}

	function has_updateable_version () {
		if($GLOBALS['logged_in']) {
			$this_local_version = file_get_contents("version");
			$this_local_version = rtrim($this_local_version, "\n");
			if(table_exists('version')) {
				$this_db_version = get_single_value_from_query('select git from version order by id desc limit 1');

				if($this_db_version == $this_local_version) {
					return 0;
				} else {
					$update_file = "version_updates/$this_local_version.sql";
					if(file_exists($update_file)) {
						return 1;
					} else {
						return 0;
					}
				}
			} else {
				return 1;
			}
		}
	}

	function get_config () {
		$query = 'select name, setting, category from config order by category, name';
		$result = rquery($query);
		$config = array();
		while ($row = mysqli_fetch_row($result)) {
			$config[$row[0]] = $row[1];
		}
		$result->free();
		return $config;
	}

	function get_setting ($name) {
		if(!array_key_exists($name, $GLOBALS['settings_cache'])) {
			if(table_exists("config")) {
				$query = 'select name, setting from config';
				$res = rquery($query);
				while ($row = mysqli_fetch_row($res)) {
					$GLOBALS['settings_cache'][$row[0]] = $row[1];
				}
				$res->free();
			} else {
				$GLOBALS['settings_cache'][$name] = array();
			}
		}

		if(array_key_exists($name, $GLOBALS['settings_cache'])) {
			return $GLOBALS['settings_cache'][$name];
		} else {
			error("Setting named `$name` could not be found!!!");
			return null;
		}
	}

	function reset_setting ($name) {
		$default_setting = get_setting_default($name);
		$description = get_setting_desc($name);
		$query = "insert into config (name, setting, description) values (".esc($name).", ".esc($default_setting).", ".esc($description).") on duplicate key update setting=values(setting), description=values(description);";
		if(rquery($query)) {
			$GLOBALS['settings_cache'] = array();
			return success("Die Einstellung $name wurde auf $default_setting resettet.");
		} else {
			return error("Die Einstellung $name konnte nicht resettet werden");
		}
	}

	function set_setting ($name, $setting, $description) {
		$query = "insert into config (name, setting, description) values (".esc($name).", ".esc($setting).", ".esc($description).") on duplicate key update setting=values(setting), description=values(description);";
		if(rquery($query)) {
			$GLOBALS['settings_cache'] = array();
			return success("Die Einstellung $name wurde auf $setting gesetzt.");
		} else {
			return error("Die Einstellung $name konnte nicht gesetzt werden.");
		}
	}

	function get_setting_default ($name) {
		$query = 'select default_value from config where name = '.esc($name);
		return get_single_value_from_query($query);
	}

	function get_setting_desc ($name) {
		$query = 'select description from config where name = '.esc($name);
		return get_single_value_from_query($query);
	}

	function get_setting_category ($name) {
		$query = 'select category from config where name = '.esc($name);
		return get_single_value_from_query($query);
	}

	function javascript_debugger ($name, $end = 0) {
		$string = '';
		if(get_setting("debug") && !$end) {
			$bt = debug_backtrace();
			$caller = array_shift($bt);

			$caller_text = $caller['file'].":".$caller['line'];

			$string .= "{\n";
			$string .= 'var thisfuncname = '.json_encode($name).';'."\n";
			$string .= 'var args = Array.prototype.slice.call(arguments);'."\n";
			$string .= "var argstring = '';\n";
			if(get_setting("debug_truncate")) {
				$string .= "args = args = args.map(x => truncate(x) );\n";
				$string .= "if (args.length >= 1) { argstring = '\"' + args.join('\", \"') + '\"'; }\n";
			} else {
				$string .= "if (args.length >= 1) { argstring = '\"' + args.join('\", \"') + '\"'; }\n";
			}
			$string .= 'console.log('.json_encode($caller_text).' + "->" + '.json_encode($name).');'."\n";
			$string .= 'console.log(thisfuncname + "(" + argstring + ")");'."\n";
			$string .= '}';
		}

		if(get_setting("debug_js_time")) {
			if($end) {
				$string .= "console.timeEnd('$name');\n";
			} else {
				$string .= "console.time('$name');\n";
			}
		}
		
		return $string;
	}

	function js_debug ($msg, $swal = 0) {
		$string = '';
		if(get_setting("debug")) {
			$bt = debug_backtrace();
			$caller = array_shift($bt);

			$caller_text = $caller['file'].":".$caller['line'];
			$this_string = "'$caller_text -> ' + ".$msg;

			if($swal) {
				$string .= 'swal('.$this_string.', { icon: "warning" }).then((value) => {alert("OK");});'."\n";
			} else {
				$string .= 'console.log('.$this_string.');'."\n";
			}
		}
		return $string;
	}

	function fq ($str) {
		return "&raquo;".htmle($str)."&laquo;";
	}

	function escape($string) {
		return $string;
	}

	function get_alternative ($first, $second, $mark_html = 1) {
		if(empty($first)) {
			if($mark_html) {
				return "<i style='color: red;'>$second</i>";
			} else {
				return $second;
			}
		} else {
			return $first;
		}
	}

	function remove_leading_zeroes ($str) {
		$str = preg_replace("/^0+/", "", $str);
		return $str;
	}

	function print_ampel ($frage_id, $add_checkbox = 0, $initialize = 0) {
		$query = 'select show_ampel, red, yellow, green from fragen where id = '.esc($frage_id);
		$row = get_single_row_from_query($query);

		if(!$row) {
			$row = array(0, 0, 0, 0);
		}

		if($initialize) {
			$row = array(1, 0, 0, 0);
			$add_checkbox = 1;
		}

		$show_ampel = $row[0];

		$red = $row[1];
		$yellow = $row[2];
		$green = $row[3];

		if(!$red && !$yellow && !$green) {
			$show_ampel = 0;
		}

		$html = '<table border="0" cellspacing="0">';


		$red_color = "black";
		$red_checkbox = "<td style='font-size: 16px;'><input type='checkbox' name='red'>Rot</td>";
		if($red) {
			$red_color = "red";
			$red_checkbox = "<td style='font-size: 16px;'><input type='checkbox' checked='CHECKED' name='red' />Rot</td>";
		}

		$yellow_color = "black";
		$yellow_checkbox = "<td style='font-size: 16px;'><input type='checkbox' name='yellow'>Gelb</td>";
		if($yellow) {
			$yellow_color = "yellow";
			$yellow_checkbox = "<td style='font-size: 16px;'><input type='checkbox' checked='CHECKED' name='yellow' />Gelb</td>";
		}

		$green_color = "black";
		$green_checkbox = "<td style='font-size: 16px;'><input type='checkbox' name='green'>Grün</td>";
		if($green) {
			$green_color = "green";
			$green_checkbox = "<td style='font-size: 16px;'><input type='checkbox' checked='CHECKED' name='gree' />Grün</td>";
		}

		if(!$add_checkbox) {
			$green_checkbox = "";
			$red_checkbox = "";
			$yellow_checkbox = "";
		}

		$disable_ampel_str = "";
		if(!$show_ampel) {
			$disable_ampel_str = " style='display: none;'";
		}

		$html .= '<tr style="font-size: 0px">'.$red_checkbox.'<td '.$disable_ampel_str.' style="width: 86px; height: 80px; margin: 0px !important; padding: 0px !important; background-color: '.$red_color.'"><img src="i/ampel_roh.png"></td></tr>';
		$html .= '<tr style="font-size: 0px">'.$yellow_checkbox.'<td '.$disable_ampel_str.'  style="width: 86px; margin: 0px !important; padding: 0px !important; background-color: '.$yellow_color.'"><img src="i/ampel_roh.png"></td></tr>';
		$html .= '<tr style="font-size: 0px">'.$green_checkbox.'<td '.$disable_ampel_str.' style="width: 86px; margin: 0px !important; padding: 0px !important; background-color: '.$green_color.'"><img src="i/ampel_roh.png"></td></tr>';

		$html .= "</table>";

		return $html;
	}

	function get_antwort ($frage_id) {
		$antwort = get_single_value_from_query("select antwort from fragen where id = ".esc($frage_id));
		return $antwort;
	}

	function get_show_ampel ($frage_id) {
		return !!get_single_value_from_query("select show_ampel from fragen where id = ".esc($frage_id));
	}

	function update_frage($frage_id, $frage, $antwort, $show_ampel, $red, $yellow, $green, $quelle, $grundrechtseinschraenkung) {
		$update_frage_query = "update fragen set frage = ".esc($frage).", antwort = ".esc($antwort).", show_ampel = ".esc($show_ampel).", red = ".esc($red).", yellow = ".esc($yellow).", green = ".esc($green).", quelle = ".esc($quelle).", grundrechtseinschraenkung = ".esc($grundrechtseinschraenkung)." where id = ".esc($frage_id);
		rquery($update_frage_query);
	}

	function create_frage($frage, $antwort, $show_ampel, $red, $yellow, $green, $quelle, $grundrechtseinschraenkung) {
		$create_frage_query = "insert into fragen (frage, antwort, show_ampel, red, yellow, green, quelle, grundrechtseinschraenkung) values (".multiple_esc_join(array($frage, $antwort, $show_ampel, $red, $yellow, $green, $quelle, $grundrechtseinschraenkung)).")";
		rquery($create_frage_query);
	}

	function delete_frage ($frage_id) {
		$query = "delete from fragen where id = ".esc($frage_id);
		rquery($query);
	}
?>
