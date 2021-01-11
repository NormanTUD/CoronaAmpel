<div id="main">
<?php
	$page_title = "Wartungstabelle | Termin ausdrucken";
	include("header.php");
	$included_files = get_included_files();
	$included_files = array_map('basename', $included_files);

	if(!in_array('functions.php', $included_files)) {
		include_once('functions.php');
	}

	if(isset($GLOBALS['logged_in_user_id'])) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann
		$turnus_array = create_turnus_array();
		$termin_id = get_get('terminid');
		$anlage_id = get_anlage_id_by_termin_id($termin_id);
		$kunde_id = get_kunde_id_by_anlage_id($anlage_id);

		foreach (array(
				array("hint", "blue"),
				array("error", "red"),
				array("mysql_error", "red"),
				array("right_issue", "red"),
				array("warning", "orange"),
				array("mysql_warning", "orange"),
				array("debug", "yellow"),
				array("message", "blue"),
				array("easter_egg", "hotpink"),
				array("success", "green")
			) as $msg) {
			show_output($msg[0], $msg[1]);
		}
		$comments = get_single_row_from_query("select comment, comment2 from wartungen where id = ".esc($termin_id));
		$time = get_single_value_from_query("select concat(monat, '.', jahr) from wartungen where id = ".esc($termin_id));

		$metadaten = get_erinnerung_pruefung_and_pruefung_ortsfester_anlagen($kunde_id);
		$erinnerung_pruefung = $metadaten[0];
		$pruefung_ortsfester_anlagen = $metadaten[1];
		$pruefung_abgelehnt = $metadaten[2];
?>
		<h2>Kunde: <?php print get_kunde_link_name_by_id($kunde_id); ?>, Anlage: <?php print get_anlagen_link_name_by_id($anlage_id); ?></h2>
		<table style="font-size: 16px !important;">
			<tr>
				<td>Kunde</td>
				<td><?php print htmle(get_kunde_name_by_id(get_kunde_id_by_wartung_id($termin_id))); ?></td>
			</tr>
			<tr>
				<td>Anlage</td>
				<td><?php print htmle(get_anlagen_name_by_termin_id($termin_id)); ?></td>
			</tr>
			<tr>
				<td>Wartungsnummer</td>
				<td><?php print htmle($termin_id); ?></td>
			</tr>
			<tr>
				<td>Adresse</td>
				<td><?php print get_single_value_from_query('select concat(plz, " ", ort, ", ", strasse, " ", hausnummer, ", ", land) from (select ifnull(ifnull(a.plz, k.plz), "<i>keine Plz</i>") as plz, ifnull(ifnull(a.ort, k.ort), "<i>kein Ort</i>") as ort, ifnull(ifnull(a.strasse, k.strasse), "<i>keine Straße</i>") as strasse, ifnull(ifnull(a.hausnummer, k.hausnummern), "<i>keine Hausnummer</i>") as hausnummer, ifnull(ifnull(a.land, k.land), "Deutschland") as land from wartungen w join anlagen a on a.id = w.anlage_id join kunden k on k.id = a.kunde_id where w.id = '.esc($termin_id).') d'); ?></td>
			</tr>
			<tr>
				<td>Angesetzte Zeit</td>
				<td><?php print htmle(get_single_value_from_query("select zeit_pro_wartung from anlagen where id = ".esc($anlage_id))); ?></td>
			</tr>
			<tr>
				<td>Turnus</td>
				<td><?php print htmle(get_turnus_id_by_anlage_id($anlage_id)); ?></td>
			</tr>
			<tr>

				<td>Prfg. Erinnerung/ortsfester Anl./abgelehnt</td>
				<td><?php print "$erinnerung_pruefung / $pruefung_ortsfester_anlagen / $pruefung_abgelehnt"; ?></td>
			</tr>
			<tr>
				<td>Ansprechpartner</td>
				<td><?php print get_single_value_from_query('select concat(ifnull(concat("Name: <i>", asp.name, "</i><br>"), ""), ifnull(concat("Email: <i>", asp.email, "</i><br>"), ""), ifnull(concat("Telnr: ", asp.telnr), "")) as asp from wartungen w join anlagen a on a.id = w.anlage_id join ansprechpartner as asp on asp.id = a.ansprechpartner_id where w.id = '.esc($termin_id)); ?></td>
			</tr>
			<tr>
				<td>Anlage Kommentar</td>
				<td><?php print htmle(get_anlage_comment(get_anlage_id_by_termin_id($termin_id))); ?></td>
			</tr>
			<tr>
				<td>Monat/Jahr</td>
				<td><?php print htmle($time); ?></td>
			</tr>
			<tr>
				<td>Kommentar 1</td>
				<td><?php print htmle($comments[0]); ?></td>
			</tr>
			<tr>
				<td>Kommentar 2</td>
				<td><?php print htmle($comments[1]); ?></td>
			</tr>
			<tr>
				<td colspan="2"><hr class="hr_wartungstabelle">Ab hier per Hand ausfüllen<hr class="hr_wartungstabelle"></td>
			</tr>
			<tr>
				<td>Gebrauchte Zeit (in ganzen Stunden)</td>
				<td><span class="write_manually"><?php print str_repeat("&nbsp; ", 30); ?></span></td>
			</tr>
			<tr>
				<td>Kommentar 1</td>
				<td><span class="write_manually"><?php print str_repeat("&nbsp; ", 30); ?></span></td>
			</tr>
			<tr>
				<td>Kommentar 2</td>
				<td><span class="write_manually"><?php print str_repeat("&nbsp; ", 30); ?></span></td>
			</tr>
			<tr>
				<td>Ersatzteile</td>
				<td><span class="write_manually"><?php print str_repeat("&nbsp; ", 60); ?></span></td>
			</tr>
		</table>
<?php
	}
	#include("footer.php");
?>
