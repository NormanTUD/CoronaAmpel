<?php
	$included_files = get_included_files();
	$included_files = array_map('basename', $included_files);

	if(!in_array('functions.php', $included_files)) {
		include_once('../functions.php');
	}

	function check_prepare_wartungstermine($prepare_wartungstermine, $anlagen, $test_anlage) {
		if($prepare_wartungstermine[0]["anlage_id"] == $anlagen[0]["id"]) {
			ok("prepare_wartungstermine anlage_id");
		} else {
			red_text("prepare_wartungstermine anlage_id");
		}

		is_equal('$prepare_wartungstermine[0]["kunde"]["ort"]', $prepare_wartungstermine[0]["kunde"]["ort"], "Dresden");
		is_equal('$prepare_wartungstermine[0]["kunde"]["plz"]', $prepare_wartungstermine[0]["kunde"]["plz"], "01159");

		if($prepare_wartungstermine[0]["kunde"]['id'] == $anlagen[0]["kunde_id"]) {
			ok("prepare_wartungstermine kunde -> id");
		} else {
			red_text("prepare_wartungstermine kunde -> id");
		}

		#dier($prepare_wartungstermine[0]["data"]["2020"]);
		if($prepare_wartungstermine[0]["data"]["2020"][1][$anlagen[0]["id"]][0]["jahr"] == 2020) {
			ok("prepare_wartungstermine data -> 2020 -> 1 -> anlage[0][id]");
		} else {
			red_text("prepare_wartungstermine data -> 2020 -> 1 -> anlage[0][id]");
		}

		if($prepare_wartungstermine[0]["data"]["2020"][1][$anlagen[0]["id"]][0]["status_id"] == 2) {
			ok("prepare_wartungstermine data -> 2020 -> 1 -> anlage[0][status_id]");
		} else {
			red_text("prepare_wartungstermine data -> 2020 -> 1 -> anlage[0][status_id]");
		}

		if($prepare_wartungstermine[0]["data"]["2020"][2][$anlagen[0]["id"]][0]["name"] == "Nicht OK") {
			ok("prepare_wartungstermine data -> 2020 -> 2 -> status");
		} else {
			red_text("prepare_wartungstermine data -> 2020 -> 2 -> status");
		}

		if($prepare_wartungstermine[0]["data"]["2020"][2][$anlagen[0]["id"]][0]["monat"] == 2) {
			ok("prepare_wartungstermine data -> 2020 -> 2 -> monat");
		} else {
			red_text("prepare_wartungstermine data -> 2020 -> 2 -> monat");
		}

		if($prepare_wartungstermine[0]["anlage_comment"] == $test_anlage["this_anlage_comment"]) {
			ok("prepare_wartungstermine data -> 2020 -> 2 -> this_anlage_comment");
		} else {
			red_text("prepare_wartungstermine data -> 2020 -> 2 -> this_anlage_comment");
		}
	}



	include_once('testing.php');

	if(check_page_rights(get_page_id_by_filename(basename(__FILE__)))) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann
?>
		<h1>Tests</h1>
		<?php print get_seitentext(); ?>
<?php
		include_once('hinweise.php');
		if(!get_setting('x11_debugging_mode')) {
			$table_queries = backup_tables ($tables = '*', null, null, 1);

			$test_db = $GLOBALS['dbname'].'_test';

			$GLOBALS['db_exists_cache'] = array();
			if(database_exists($test_db)) {
				rquery("DROP DATABASE $test_db");
			}
			rquery("CREATE DATABASE $test_db");
			rquery("USE $test_db");


			h("Copying databases to $test_db");

			$failed = 0;
			foreach ($table_queries as $id => $table_create_query) {
				if($table_create_query) {
					if(!rquery($table_create_query)) {
						$failed = 1;
					}
				}
			}

			if($failed) {
				red_text("Copying DB failed");
			} else {
				ok("Copying DB OK");
			}

			h("Füge Statusse und Einstellungen ein");


			if(set_setting("show_h_pro_wartung", "1", "show_h_pro_wartung")) {
				ok("Einstellung show_h_pro_wartung eintragen hat geklappt");
			} else {
				red_text("Einstellungen show_h_pro_wartung eintragen hat nicht geklappt");
			}

			if(set_setting("jahre_plus_nach_erstellen_anlage", "5", "jahre_plus_nach_erstellen_anlage")) {
				ok("Einstellung jahre_plus_nach_erstellen_anlage eintragen hat geklappt");
			} else {
				red_text("Einstellungen jahre_plus_nach_erstellen_anlage eintragen hat nicht geklappt");
			}

			if(set_setting("jahre_minus_nach_erstellen_anlage", "5", "jahre_minus_nach_erstellen_anlage")) {
				ok("Einstellung jahre_minus_nach_erstellen_anlage eintragen hat geklappt");
			} else {
				red_text("Einstellungen jahre_minus_nach_erstellen_anlage eintragen hat nicht geklappt");
			}

			if(set_setting("status_ok", "1", "testeinstellung desc")) {
				ok("Einstellung status_ok eintragen hat geklappt");
			} else {
				red_text("Einstellungen status_ok eintragen hat nicht geklappt");
			}

			if(set_setting("status_default", "2", "testeinstellung desc")) {
				ok("Einstellung status_default eintragen hat geklappt");
			} else {
				red_text("Einstellungen status_default eintragen hat nicht geklappt");
			}

			if(set_setting("status_geplant", "3", "testeinstellung desc")) {
				ok("Einstellung status_geplant eintragen hat geklappt");
			} else {
				red_text("Einstellungen status_geplant eintragen hat nicht geklappt");
			}

			$query = 'insert ignore into status (id, color, name) values ('.esc(get_setting("status_ok")).', "00FF00", "OK")';
			rquery($query);

			$query = 'insert ignore into status (id, color, name) values ('.esc(get_setting('status_default')).', "FF0000", "Nicht OK")';
			rquery($query);

			$query = 'insert ignore into status (id, color, name) values ('.esc(get_setting('status_geplant')).', "FFFF00", "Geplant")';
			rquery($query);


			h("Creating testkunde");

			$testkunde = array(
				"kunde" => "Testkunde",
				"plz" => "01159",
				"ort" => "Dresden",
				"strasse" => "Teststr.",
				"hausnummern" => "1 - 5",
				"erinnerung" => 1,
				"pruefung" => 0,
				"pruefung_abgelehnt" => 1,
				"phone" => "123456",
				"email" => "test@email.com",
				"land" => "Deutschland"
			);
			
			$kunde_id = create_kunde($testkunde['kunde'], $testkunde['plz'], $testkunde['ort'], $testkunde['strasse'], $testkunde['hausnummern'], $testkunde['erinnerung'], $testkunde['pruefung'], $testkunde['pruefung_abgelehnt'], $testkunde['phone'], $testkunde['email'], $testkunde['land']);
			if(preg_match('/^\d+$/', $kunde_id)) {
				ok("Anlage erstellt, ID in N;");
			} else {
				red_text("Error creating anlage");
			}

			is_equal("get_kunde_name_by_id($kunde_id)", get_kunde_name_by_id($kunde_id), $testkunde['kunde']);

			h("Checking testkunde");
			$kunden_array = get_kunden_array();

			foreach ($kunden_array[0] as $key => $value) {
				is_equal("Testkunde $key", $value, $kunden_array[0][$key]);
			}

			regex_matches("Kunde ID is a number", $kunden_array[0]['id'], '/^\d+$/');

			rquery('insert into turnus (id, name, anzahl_monate, wartungen_pro_monat) values (1, "1x pro Monat", 1, 1)');

			$test_anlage = array(
				"kunde_id" => $kunden_array[0]['id'],
				"name" => "Testanlage 1",
				"turnus_id" => 1,
				"ibn_beendet_am" => "2020-01-01",
				"letzte_wartung" => "2020-01-01",
				"ende_gewaehrleistung" => "2030-01-01",
				"this_anlage_comment" => "Testkommentar",
				"status_default_id" => null,
				"wartungspauschale" => 666,
				"ansprechpartner_id" => null,
				"zeit_pro_wartung" => null,
				"plz" => "12345",
				"ort" => "Testort",
				"strasse" => "Str.",
				"hausnummer" => "10a",
				"land" => "Frankreich",
				"material_wartung" => "10"
			);

			h("Ansprechpartner");

			//function get_or_create_ansprechpartner ($ansprechpartner_email, $ansprechpartner_telnr, $ansprechpartner_name, $recursion = 0) {
			$ansprechpartner_id = get_or_create_ansprechpartner("test@email.com", "12345678", "Herr Testus Testarius", 0);
			regex_matches("ansprechpartner_id", $ansprechpartner_id, '/^\d+$/');
			is_equal("get_ansprechpartner_name($ansprechpartner_id)", get_ansprechpartner_name($ansprechpartner_id), "Herr Testus Testarius");
			is_equal("get_ansprechpartner_telnr($ansprechpartner_id)", get_ansprechpartner_telnr($ansprechpartner_id), "12345678");
			is_equal("get_ansprechpartner_email($ansprechpartner_id)", get_ansprechpartner_email($ansprechpartner_id), "test@email.com");

			h("Einstellungen");

			if(set_setting("testeinstellung", "hallo", "testeinstellung desc")) {
				ok("Einstellung hallo eintragen hat geklappt");
			} else {
				red_text("Einstellungen hallo eintragen hat nicht geklappt");
			}

			is_equal("get_setting('testeinstellung')", get_setting('testeinstellung'), "hallo");

			if(set_setting("testeinstellung", "hallo 2", "testeinstellung desc")) {
				ok("Einstellung hallo mit neuem Wert eintragen hat geklappt");
			} else {
				red_text("Einstellungen hallo mit neuem Wert eintragen hat nicht geklappt");
			}

			is_equal("get_setting('testeinstellung')", get_setting('testeinstellung'), "hallo 2");


			h("Ersatzteile");

			$testersatzteil_name = "testersatzteil";
			$ersatzteil_id = create_ersatzteil($testersatzteil_name);
			
			$ersatzteile_array = get_ersatzteil_array();

			regex_matches("Ersatzteile-ID is a number", $ersatzteil_id, '/^\d+$/');
			is_equal("get_ersatzteil_name($ersatzteil_id)", get_ersatzteil_name($ersatzteil_id), $testersatzteil_name);
			is_equal("count(\$ersatzteile_array)", count($ersatzteile_array), 1);

			$renamed_ersatzteil = "renamed";
			if(update_ersatzteil($ersatzteil_id, $renamed_ersatzteil, 100.51)) {
				ok("update_ersatzteil($ersatzteil_id, $renamed_ersatzteil, 100.51)");
			} else {
				red_text("update_ersatzteil failed");
			}

			$ersatzteile_array = get_ersatzteil_array();
			is_equal("\$ersatzteile_array[0][1]", $ersatzteile_array[0][1], $renamed_ersatzteil);
			is_equal("get_ersatzteil_default_price($ersatzteil_id)", get_ersatzteil_default_price($ersatzteil_id), 100.51);


			if(delete_ersatzteil($ersatzteil_id)) {
				ok("delete_ersatzteil($ersatzteil_id) did work");
			} else {
				red_text("delete_ersatzteil($ersatzteil_id) did not work");
			}

			$ersatzteile_array_2 = get_ersatzteil_array();
			is_equal("count(\$ersatzteile_array_2)", count($ersatzteile_array_2), 0);

			h("Create test_anlage");

			create_anlage($test_anlage['kunde_id'], $test_anlage['name'], $test_anlage['turnus_id'], $test_anlage['ibn_beendet_am'], $test_anlage['letzte_wartung'], $test_anlage['ende_gewaehrleistung'], $test_anlage['this_anlage_comment'], $test_anlage['status_default_id'], $test_anlage['wartungspauschale'], $test_anlage['ansprechpartner_id'], $test_anlage['zeit_pro_wartung'], $test_anlage['plz'], $test_anlage['ort'], $test_anlage['strasse'], $test_anlage['hausnummer'], $test_anlage['land'], $test_anlage['material_wartung']);

			is_equal("get_anzahl_anlagen_pro_kunde()", "1", get_anzahl_anlagen_pro_kunde($test_anlage['kunde_id']));

			$anlagen = get_anlagen();
			is_equal("Anlage-Kunde-ID", $kunden_array[0]['id'], $anlagen[0]['kunde_id']);
			is_equal("get_kunde_id_by_anlage_id(".$anlagen[0]['kunde_id'].")", get_kunde_id_by_anlage_id($anlagen[0]['id']), $anlagen[0]['kunde_id']);
			is_equal("Anlage-Name", $test_anlage['name'], $anlagen[0]['name']);
			is_equal("get_anlage_name_by_id(".$anlagen[0]['id'].")", get_anlage_name_by_id($anlagen[0]['id']), $anlagen[0]['name']);
			regex_matches("Anlage ID in N", $anlagen[0]['id'], '/^\d+$/');

			$last_termin = get_last_termin_by_anlage_id($anlagen[0]["id"])->format("Y-m-d");
			is_equal("last_termin", $last_termin, "2021-12-01");

			h("Addressdaten");

			$addressdaten = get_addressdaten($anlagen[0]["id"]);

			is_equal("get_addressdaten() -> anlage_id", $addressdaten["anlage_id"], $anlagen[0]["id"]);
			is_equal("get_addressdaten() -> kunde_id", $addressdaten["kunde_id"], $anlagen[0]["kunde_id"]);
			is_equal("get_addressdaten() -> plz", $addressdaten["plz"], "12345");
			is_equal("get_addressdaten() -> ort", $addressdaten["ort"], "Testort");
			is_equal("get_addressdaten() -> strasse", $addressdaten["strasse"], "Str.");
			is_equal("get_addressdaten() -> hausnummern", $addressdaten["hausnummern"], "10a");



			h("Checking wartungen (neue Methode)");

			$prepare_wartungstermine_new = prepare_wartungstermine(2020, 1, 2020, null, null, $anlagen[0]['id'], 1, null, null, null, null);
			check_prepare_wartungstermine($prepare_wartungstermine_new, $anlagen, $test_anlage);

			h("Checking wartungen (alte Methode)");

			$prepare_wartungstermine_old = prepare_wartungstermine_group_by_kunde(2020, 1, 2020, null, null, $anlagen[0]['id'], 1, null, null, null, null);
			check_prepare_wartungstermine($prepare_wartungstermine_old, $anlagen, $test_anlage);

			h("Internes Zeugs");

			$monate = get_monate();

			$naechste_wartung_str = get_naechste_wartung($anlagen[0]["id"], 0);
			$naechste_wartung_array = get_naechste_wartung($anlagen[0]["id"], 1);
			is_equal("naechste_wartung_str", $naechste_wartung_str, $monate[remove_leading_zeroes(date("m"))]." ".date("Y"));
			is_equal("naechste_wartung_array", $naechste_wartung_array, date("m").".".date("Y"));

			is_equal("string_to_number(10)", string_to_number("10"), 10);
			is_equal("string_to_number(10a)", string_to_number("10a"), 10);
			is_equal("string_to_number(a, 1)", string_to_number("a", 1), null);
			is_equal("string_to_number(10.5a)", string_to_number("10.5a"), 10.5);
			is_equal("string_to_number(10,5a)", string_to_number("10,5a"), 10.5);
			is_equal("string_to_number(10,50€)", string_to_number("10,50€"), 10.5);

			is_equal("search_for_anything_in_tabelle_get_anlage_ids('ishouldnotgiveanyresults')", search_for_anything_in_tabelle_get_anlage_ids('ishouldnotgiveanyresults'), array());
			is_equal("search_for_anything_in_tabelle_get_anlage_ids('testanlage')", search_for_anything_in_tabelle_get_anlage_ids('testanlage'), array(0 => $anlagen[0]['id']));

			is_equal("monat_zahl_nach_string(1)", monat_zahl_nach_string(1), "Januar");
			is_equal("monat_zahl_nach_string(2)", monat_zahl_nach_string(2), "Februar");
			is_equal("monat_zahl_nach_string(3)", monat_zahl_nach_string(3), "März");
			is_equal("monat_zahl_nach_string(4)", monat_zahl_nach_string(4), "April");
			is_equal("monat_zahl_nach_string(5)", monat_zahl_nach_string(5), "Mai");
			is_equal("monat_zahl_nach_string(6)", monat_zahl_nach_string(6), "Juni");
			is_equal("monat_zahl_nach_string(7)", monat_zahl_nach_string(7), "Juli");
			is_equal("monat_zahl_nach_string(8)", monat_zahl_nach_string(8), "August");
			is_equal("monat_zahl_nach_string(9)", monat_zahl_nach_string(9), "September");
			is_equal("monat_zahl_nach_string(10)", monat_zahl_nach_string(10), "Oktober");
			is_equal("monat_zahl_nach_string(11)", monat_zahl_nach_string(11), "November");
			is_equal("monat_zahl_nach_string(12)", monat_zahl_nach_string(12), "Dezember");

			is_equal("htmle('<b>hallo</b>')", htmle("<b>hallo</b>"), "&lt;b&gt;hallo&lt;/b&gt;");

			is_equal("fill_front_zeroes(5)", fill_front_zeroes(5, 4), "0005"); 

			h("Delete Funktionen");

			delete_anlage($anlagen[0]["id"]);
			$anlagen_after_array_deletetion = get_anlagen();
			if(count($anlagen_after_array_deletetion) > 0) {
				red_text("Irgendwas ist mit der Cache-Validation der Anlagen-Funktionen nicht OK (Cache sollte beim Löschen gelöscht werden, wurde er aber nicht");
			} else {
				ok("Anlagen deletion, Cache-Invalidierung");
			}

			is_equal("get_anzahl_anlagen_pro_kunde() Nach Cache-Invalidierung", 0, get_anzahl_anlagen_pro_kunde($test_anlage['kunde_id']));

			delete_kunde($kunde_id);
			$kunden_array_after_deletion = get_kunden_array();
			if(count($kunden_array_after_deletion) > 0) {
				red_text("Irgendwas ist mit der Cache-Validation der kunden-Funktionen nicht OK (Cache sollte beim Löschen gelöscht werden, wurde er aber nicht");
			} else {
				ok("Kunden deletion, Cache-Invalidierung");
			}

			rquery("USE ".$GLOBALS['dbname']);
			$GLOBALS['db_exists_cache'] = array();
			if(database_exists($test_db)) {
				if(rquery("DROP DATABASE IF EXISTS $test_db")) {
					ok("Dropping $test_db worked");
				} else {
					red_text("Dropping $test_db DID NOT work");
				}
			}

			error("Dies ist kein echter Fehler. Er testet nur die Fehleranzeigeroutine. Alles OK, wenn dieser Fehler oben angezeigt wird.");
		} else {
?>
			<br><i>Die Seite ist deaktiviert, weil der x11_debugging_mode aktiv ist</i>
<?php
		}
	}
?>
