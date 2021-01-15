<?php
	$included_files = get_included_files();
	$included_files = array_map('basename', $included_files);

	if(!in_array('functions.php', $included_files)) {
		include_once('functions.php');
	}

	function selftest () {
		$tables = array(
			"config" => "CREATE TABLE `config` (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `name` varchar(100) DEFAULT NULL,\n  `setting` varchar(100) DEFAULT NULL,\n  `default_value` varchar(100) DEFAULT NULL,\n  `description` varchar(100) DEFAULT NULL,\n category varchar(50),\n PRIMARY KEY (`id`),\n  UNIQUE KEY `name` (`name`)\n) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",
			"function_rights" => "CREATE TABLE `function_rights` (\n  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n  `name` varchar(100) DEFAULT NULL,\n  `role_id` int(10) unsigned NOT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `name_role_id` (`name`,`role_id`),\n  KEY `role_id` (`role_id`),\n  CONSTRAINT `function_rights_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",
			"hinweise" => array(
				"CREATE TABLE `hinweise` (\n  `page_id` int(10) unsigned NOT NULL,\n  `hinweis` text DEFAULT NULL,\n  PRIMARY KEY (`page_id`),\n  CONSTRAINT `hinweise_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			),
			"page" => "CREATE TABLE `page` (\n  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n  `name` varchar(50) NOT NULL,\n  `file` varchar(50) DEFAULT NULL,\n  `show_in_navigation` enum('0','1') NOT NULL DEFAULT '0',\n  `parent` int(10) unsigned DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `name` (`name`),\n  UNIQUE KEY `file` (`file`),\n  KEY `page` (`parent`),\n  CONSTRAINT `page_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `page` (`id`) ON DELETE SET NULL\n) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4",
			"page_info" => "CREATE TABLE `page_info` (\n  `page_id` int(10) unsigned NOT NULL,\n  `info` varchar(1000) DEFAULT NULL,\n  PRIMARY KEY (`page_id`),\n  KEY `page_id` (`page_id`),\n  CONSTRAINT `page_info_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"right_issues" => "CREATE TABLE `right_issues` (\n  `function` varchar(100) NOT NULL DEFAULT '',\n  `user_id` int(10) unsigned NOT NULL,\n  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',\n  PRIMARY KEY (`function`,`user_id`,`date`),\n  KEY `user_id` (`user_id`),\n  CONSTRAINT `right_issues_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"right_issues_pages" => "CREATE TABLE `right_issues_pages` (\n  `user_id` int(10) unsigned NOT NULL,\n  `page_id` int(10) unsigned NOT NULL,\n  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',\n  PRIMARY KEY (`user_id`,`page_id`,`date`),\n  KEY `page_id` (`page_id`),\n  CONSTRAINT `right_issues_pages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,\n  CONSTRAINT `right_issues_pages_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"role" => "CREATE TABLE `role` (\n  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n  `name` varchar(100) DEFAULT NULL,\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `name` (`name`)\n) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",
			"role_to_page" => "CREATE TABLE `role_to_page` (\n  `role_id` int(10) unsigned NOT NULL,\n  `page_id` int(10) unsigned NOT NULL,\n  PRIMARY KEY (`role_id`,`page_id`),\n  KEY `page_id` (`page_id`),\n  CONSTRAINT `role_to_page_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE,\n  CONSTRAINT `role_to_page_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"role_to_user" => "CREATE TABLE `role_to_user` (\n  `role_id` int(10) unsigned NOT NULL,\n  `user_id` int(10) unsigned NOT NULL,\n  PRIMARY KEY (`role_id`,`user_id`),\n  UNIQUE KEY `name` (`user_id`),\n  CONSTRAINT `role_to_user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE,\n  CONSTRAINT `role_to_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"seitentext" => "CREATE TABLE `seitentext` (\n  `page_id` int(10) unsigned NOT NULL,\n  `text` varchar(10000) DEFAULT NULL,\n  PRIMARY KEY (`page_id`),\n  CONSTRAINT `seitentext_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
			"session_ids" => "CREATE TABLE `session_ids` (\n  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n  `session_id` varchar(1024) NOT NULL,\n  `user_id` int(10) unsigned NOT NULL,\n  `creation_time` timestamp NOT NULL DEFAULT current_timestamp(),\n  PRIMARY KEY (`id`),\n  KEY `user_id` (`user_id`),\n  CONSTRAINT `session_ids_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",
			"users" => "CREATE TABLE `users` (\n  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n  `username` varchar(100) DEFAULT NULL,\n  `dozent_id` int(10) unsigned DEFAULT NULL,\n  `institut_id` int(10) unsigned DEFAULT NULL,\n  `password_sha256` varchar(256) DEFAULT NULL,\n  `salt` varchar(100) NOT NULL,\n  `enabled` enum('0','1') NOT NULL DEFAULT '1',\n  `barrierefrei` enum('0','1') NOT NULL DEFAULT '0',\n  `accepted_public_data` enum('0','1') NOT NULL DEFAULT '0',\n  PRIMARY KEY (`id`),\n  UNIQUE KEY `name` (`username`),\n  UNIQUE KEY `dozent_id` (`dozent_id`),\n  KEY `institut_id` (`institut_id`),\n  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`dozent_id`) REFERENCES `dozent` (`id`) ON DELETE CASCADE,\n  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`institut_id`) REFERENCES `institut` (`id`) ON DELETE CASCADE\n) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",
			"version" => array(
				"CREATE TABLE `version` (\n  `id` int(11) NOT NULL AUTO_INCREMENT,\n  `git` varchar(512) DEFAULT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4",
				"DELETE FROM `version`",
				"INSERT INTO `version` (git) VALUES (".esc(get_local_version()).")"
			),
			"fragen" => array(
				"create table fragen (id int not null AUTO_INCREMENT, frage varchar(1000), antwort varchar(1000), show_ampel tinyint default 1, red tinyint default 0, yellow tinyint default 0, green tinyint default 0, quelle varchar(1000), grundrechtseinschraenkung varchar(1000), primary key (id))",
				"insert into fragen (frage) values (".esc("Darf ich rausgehen?").")",
				"insert into fragen (frage) values (".esc("Darf ich meine Freunde treffen?").")",
				"insert into fragen (frage) values (".esc("Wie viele Freunde darf ich treffen?").")",
				"insert into fragen (frage) values (".esc("Darf ich ohne Mundschutz radfahren?").")",
				"insert into fragen (frage) values (".esc("Darf ich ohne Mundschutz Joggen?").")",
				"insert into fragen (frage) values (".esc("Darf ich wieder Schwimmen gehen?").")"
			),
			"keyword" => array(
				"CREATE TABLE keyword (id INT NOT NULL AUTO_INCREMENT, keyword VARCHAR(64), PRIMARY KEY (id))"
			),
			"frage_keyword" => array(
				"CREATE TABLE frage_keyword (id INT NOT NULL AUTO_INCREMENT, frage_id INT NOT NULL, keyword_id INT NOT NULL, PRIMARY KEY (id), FOREIGN KEY (frage_id) REFERENCES fragen(id), FOREIGN KEY (keyword_id) REFERENCES keyword(id))"
			)
		);

		$missing_tables = array();

		rquery('SET foreign_key_checks = 0');
		foreach ($tables as $this_table => $create_query) {
			if(!table_exists_nocache($this_table)) {
				$missing_tables[] = $this_table;
				if(is_array($create_query)) {
					foreach ($create_query as $this_create_query) {
						rquery($this_create_query);
					}
				} else {
					rquery($create_query);
				}
				$GLOBALS['settings_cache'] = array();
			}
		}
		rquery('SET foreign_key_checks = 1');

		$views = array(
			"view_account_to_role_pages" => "CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_account_to_role_pages` AS select `p`.`id` AS `page_id`,`p`.`name` AS `name`,`p`.`file` AS `file`,`ru`.`user_id` AS `user_id`,`p`.`show_in_navigation` AS `show_in_navigation`,`p`.`parent` AS `parent` from ((`role_to_user` `ru` join `role_to_page` `rp` on(`rp`.`role_id` = `ru`.`role_id`)) join `page` `p` on(`p`.`id` = `rp`.`page_id`))",
			"view_page_and_hinweis" => "CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_page_and_hinweis` AS select `p`.`id` AS `id`,`p`.`name` AS `name`,`p`.`show_in_navigation` AS `show_in_navigation`,`h`.`hinweis` AS `hinweis` from (`page` `p` left join `hinweise` `h` on(`h`.`page_id` = `p`.`id`))",
			"view_page_and_text" => "CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_page_and_text` AS select `p`.`id` AS `id`,`p`.`name` AS `name`,`p`.`show_in_navigation` AS `show_in_navigation`,`h`.`text` AS `text` from (`page` `p` left join `seitentext` `h` on(`h`.`page_id` = `p`.`id`))",
			"view_user_session_id" => "CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_user_session_id` AS select `s`.`id` AS `session_id_id`,`u`.`id` AS `user_id`,`s`.`session_id` AS `session_id`,`s`.`creation_time` AS `creation_time`,`u`.`username` AS `username`,`u`.`dozent_id` AS `dozent_id`,`u`.`institut_id` AS `institut_id`,`u`.`enabled` AS `enabled`,`u`.`accepted_public_data` AS `accepted_public_data` from (`users` `u` left join `session_ids` `s` on(`s`.`user_id` = `u`.`id`))",
			"view_user_to_role" => "CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_user_to_role` AS select `u`.`id` AS `user_id`,`u`.`username` AS `username`,`ru`.`role_id` AS `role_id`,`r`.`name` AS `name`,`u`.`dozent_id` AS `dozent_id`,`u`.`institut_id` AS `institut_id`,`u`.`enabled` AS `enabled`,`u`.`barrierefrei` AS `barrierefrei` from ((`users` `u` left join `role_to_user` `ru` on(`u`.`id` = `ru`.`user_id`)) join `role` `r` on(`r`.`id` = `ru`.`role_id`))",
		);

		$missing_views = array();
		foreach ($views as $this_view => $create_query) {
			if(!table_exists_nocache($this_view)) {
				$missing_views[] = $this_view;
				rquery($create_query);
			}
		}

		$settings = array(
			"debug" => array("setting" => "0", "default_value" => "0", "description" => "Aktiviert Debugging (1 ja, 0 nein)", "category" => "Debug"),
			"debug_truncate" => array("setting" => "1", "default_value" => "1", "description" => "Truncate lange debug arrays (1 ja, 0 nein)", "category" => "Debug"),
			"add_javascript_query_debug" => array("setting" => "0", "default_value" => "0", "description" => "Fügt Meldungen über AJAX im Query-Debugger ein (1 ja, 0 nein)", "category" => "Debug"),
			"enable_query_debugger" => array("setting" => "0", "default_value" => "0", "description" => "Aktiviert Query-Debugger (1 ja, 0 nein)", "category" => "Debug"),
			"highlight_debugger" => array("setting" => "0", "default_value" => "0", "description" => "Aktiviert Highligthing im Query-Debugger (1 ja, 0 nein)", "category" => "Debug"),
			"hide_debugger" => array("setting" => "1", "default_value" => "1", "description" => "Versteckt Query-Debugger per Default (1 ja, 0 nein)", "category" => "Debug"),
			"debug_truncate_limit" => array("setting" => "100", "default_value" => "100", "description" => "Anzahl der maximalen Zeichen im JS-Debugger-Output", "category" => "Debug"),
			"debug_js_time" => array("setting" => "0", "default_value" => "0", "description" => "Zeigt Zeit-Debugging-Infos für Javascript (1 ja, 0 nein)", "category" => "Debug"),
			"x11_debugging_mode" => array("setting" => "0", "default_value" => "0", "description" => "Deaktiviert Dinge, die im randomisiertem X11-Test Probleme machen (1 ja, 0 nein)", "category" => "Debug")
		);

		$errormsg = array();

		$GLOBALS['settings_cache'] = array();

		foreach ($settings as $this_setting_name => $this_setting_values) {
			if(is_null(get_setting($this_setting_name))) {
				$errormsg[] = "Fehlende Einstellung ".fq($this_setting_name)." existierte nicht und wurde eingefügt.";
				$query = 'insert into config (name, setting, default_value, description, category) values ('.esc($this_setting_name).', '.
					esc($this_setting_values['setting']).', '.esc($this_setting_values['default_value']).', '.esc($this_setting_values['description']).
					', '.esc($this_setting_values['category']).
					') on duplicate key update setting=values(setting), description=values(description), category=values(category)';
				if(!rquery($query)) {
					$errormsg[] = "Die Einstellung ".fq($this_setting_name)." wurde ***nicht*** eingerichtet";
				}
			}
		}

		if(count($missing_tables)) {
			$errormsg[] = "Fehlende Tabellen:\t\n\t".join("\n\t", $missing_tables)."\n\n";
		}

		if(count($missing_views)) {
			$errormsg[] = "Fehlende views:\t\n\t".join("\n\t", $missing_views)."\n\n";
		}

		$pages_queries = array(
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("1", "Accounts", "accounts.php", "1", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("11", "Rollen", "roles.php", "1", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("15", "Seiteninformationen", "edit_page_info.php", "0", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("16", "Rechteprobleme", "right_issues.php", "1", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("17", "Query-Analyzer", "query_analyzer.php", "0", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("18", "Willkommen!", "welcome.php", "0", NULL)',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("21", "Eigene Daten ändern", "password.php", "1", NULL)',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("23", "DB-Backup", "backup.php", "1", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("24", "DB-Backup-Export", "backup_export.php", "0", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("25", "System", NULL, "1", NULL)',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("31", "DB-Diff", "dbdiff.php", "1", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("32", "User-Agents", "useragents.php", "0", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("43", "Seiten", "newpage.php", "1", "25")',
			//'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("53", "SQL", "sql.php", "1", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("54", "Einstellungen", "settings.php", "1", "25")',
			'INSERT IGNORE INTO page (id, name, file, show_in_navigation, parent) VALUES ("63", "Ampeleinstellungen", "ampel.php", "1", null)'
		);

		if(get_single_value_from_query("select count(*) from page") < (count($pages_queries) - 1)) {
			$GLOBALS['ignore_function_rights'] = 1;
			$errormsg[] = "Es konnten nur ".count($pages_queries)." Seiten gefunden werden. Es wurde einige Standardseiten angelegt";

			rquery('SET foreign_key_checks = 0');
			foreach ($pages_queries as $this_page_query) {
				rquery($this_page_query);
			}
			rquery('SET foreign_key_checks = 1');
		}

		if(get_single_value_from_query("select count(*) from page_info") == 0) {
			$GLOBALS['ignore_function_rights'] = 1;
			$errormsg[] = "Es konnten keine Seiteninfos gefunden werden. Es wurde einige Standard-Seiteninfos angelegt";
			$pages_info_queries = array(
				"insert ignore into page_info (page_id, info) values (1, 'Erlaubt das Bearbeiten und Erstellen von Accounts zum Anmelden')",
				"insert ignore into page_info (page_id, info) values (11, 'Erlaubt das Bearbeiten und Erstellen von Rollen für Accounts')",
				"insert ignore into page_info (page_id, info) values (52, 'Erlaubt das Bearbeiten und Erstellen von Anlagen')",
				"insert ignore into page_info (page_id, info) values (53, 'Hier kann man SQL-Code einfügen, aber bitte vorsichtig')",
				"insert ignore into page_info (page_id, info) values (59, 'Erlaubt, Anlagen- und Kundendaten aus einer Excel-CSV zu importieren')",
				"insert ignore into page_info (page_id, info) values (18, 'Startseite')",
				"insert ignore into page_info (page_id, info) values (16, 'Zeigt Rechteprobleme an')",
				"insert ignore into page_info (page_id, info) values (61, 'Verschiebt Anlagen samt aller Metadaten zu anderen Firmen')",
				"insert ignore into page_info (page_id, info) values (23, 'Macht Backups der Datenbank')",
				"insert ignore into page_info (page_id, info) values (21, 'Erlaubt es, eigenen Benutzernamen und eigenes Passwort zu ändern')",
				"insert ignore into page_info (page_id, info) values (58, 'Zeigt alle Termine des Monats in einer Übersichtskarte an')",
				"insert ignore into page_info (page_id, info) values (31, 'Zeigt den Unterschied zwischen einem Backup und der aktuellen DB an')",
				"insert ignore into page_info (page_id, info) values (57, 'Erlaubt, Ersatzteile zu editieren und hinzuzufügen')",
				"insert ignore into page_info (page_id, info) values (25, 'Alle Unterseiten zu den Systemeinstellungen')",
				"insert ignore into page_info (page_id, info) values (51, 'Erlaubt das Einfügen neuer Statusse')",
				"insert ignore into page_info (page_id, info) values (49, 'Erlaubt das Erstellen und löschen von Turnussen')",
				"insert ignore into page_info (page_id, info) values (43, 'Erlaubt das Erstellen, bearbeiten und löschen von Seiten')",
				"insert ignore into page_info (page_id, info) values (54, 'Erlaubt das Erstellen und Bearbeiten von Einstellungen')",
				"insert ignore into page_info (page_id, info) values (48, 'Erlaubt das Erstellen und Bearbeiten von Kunden')",
				"insert ignore into page_info (page_id, info) values (50, 'Zeigt die Wartungstabelle an')",
				"insert ignore into page_info (page_id, info) values (62, 'Erlaubt es, die Metadaten einzelner Wartungen zu ändern')",
				"insert ignore into page_info (page_id, info) values (63, 'Führt einige automatisierte Tests durch')"
			);
			rquery('SET foreign_key_checks = 0');
			foreach ($pages_info_queries as $this_page_info_query) {
				rquery($this_page_info_query);
			}
			rquery('SET foreign_key_checks = 1');
		}

		if(get_single_value_from_query("select count(*) from role") == 0) {
			$GLOBALS['ignore_function_rights'] = 1;
			create_role("Administrator");
			$errormsg[] = "Es konnten keine Rollen gefunden werden. Es wurde eine Administratorrolle angelegt";
		}

		if(get_get("add_role_to_page") || get_single_value_from_query("select count(*) from role_to_page") == 0) {
			$GLOBALS['ignore_function_rights'] = 1;
			$role_id = get_single_value_from_query("select min(id) from role");
			$query = 'select id from page';
			$result = rquery($query);
			$insert_queries = array();
			while ($row = mysqli_fetch_row($result)) {
				$id = $row[0];
				$insert_queries[] =  "insert ignore into role_to_page (role_id, page_id) values (".esc($role_id).', '.esc($id).')';
			}
			foreach ($insert_queries as $this_insert_query) {
				rquery($this_insert_query);
			}
			$errormsg[] = "Es konnten keine Seiten-zu-Rollen-Zuordnungen gefunden werden. Es wurden alle Seiten der Rolle ".get_role_name($role_id)." ($role_id) zugeordnet. Sie ist der neue Administrator.";
		}

		if(get_single_value_from_query("select count(*) from users") == 0) {
			$GLOBALS['ignore_function_rights'] = 1;
			create_user("Administrator", "test", 1);
			$errormsg[] = "Es konnte kein Account zum Anmelden gefunden werden. Ich habe einen neuen angelegt:<br>Name: Administrator<br>Passwort: test<br>";
		}

		if(get_single_value_from_query('select count(*) from users where id not in (select user_id from role_to_user)')) {
			$role_id = get_single_value_from_query("select min(id) from role"); // Auf jeden Fall kein Admin
			$query = 'select id from users where id not in (select user_id from role_to_user)';
			$result = rquery($query);
			$insert_queries = array();
			while ($row = mysqli_fetch_row($result)) {
				$insert_queries[] = "insert into role_to_user (role_id, user_id) values (".esc($role_id).", ".esc($row[0]).")";
			}
			foreach ($insert_queries as $this_insert_query) {
				rquery($this_insert_query);
			}
			$errormsg[] = "Es gab Accounts, die keiner Rolle zugeordnet waren. Diese wurden der Rolle ".get_role_name($role_id)." zugeordnet";
		}

		if(count($errormsg)) {
			$string = "<table><tr><td><img style='width: 80px;' src='i/repair.svg' /></td><td><span style='font-size: 80px;'>Power-On-Self-Test</span></td></tr></table>";
			$string .= "<div style='background-color: #ff0000; color: white'>Der <b>P</b>ower-<b>O</b>n-<b>S</b>elf-<b>T</b>est (POST) ist gescheitert. Folgende Meldungen können helfen, das Problem zu debuggen:\n</div><div style='color: red;'><pre>".join("</pre></div><div style='color: red;'><pre>", $errormsg)."</pre></div><br>Versuche, die Seite neu zu laden. Vielleicht konnten die Probleme gelöst werden.";
			die($string);
		}

		return "Selftest OK";
	}

	if(!get_get('noselftest')) {
		selftest();
	}
?>
