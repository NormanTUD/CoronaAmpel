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
		$fragen_query = 'select id, frage, quelle, grundrechtseinschraenkung from fragen';
		$result = rquery($fragen_query);
		while ($row = mysqli_fetch_row($result)) {
			$keywords = array();

			$get_keywords_query = 'SELECT keyword.keyword FROM keyword WHERE keyword.id IN (SELECT keyword_id FROM frage_keyword WHERE frage_keyword.frage_id = '.esc($row[0]).")";
			$get_keywords_query_result = rquery($get_keywords_query);
			
			while($keyword_query_row = mysqli_fetch_row($get_keywords_query_result)) {
				array_push($keywords, $keyword_query_row[0]);
			}

			$fragen[] = array(
				"id" => $row[0],
				"frage" => $row[1],
				"quelle" => $row[2],
				"grundrechtseinschraenkung" => $row[3],
				"keywords" => $keywords
			);
		}


		foreach ($fragen as $this_frage) {
			$frage_id = $this_frage["id"];
			$frage = $this_frage["frage"];
			$quelle = $this_frage["quelle"];
			$grundrechtseinschraenkung = $this_frage["grundrechtseinschraenkung"];
			$keywords = $this_frage["keywords"];

			$show_ampel = get_show_ampel($frage_id);

?>
			<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php print $GLOBALS['this_page_number']; ?>">
				<input name="frage_id" type="hidden" value="<?php print htmle($frage_id); ?>" />
				<table>
				<tr><td colspan="2"><input name="frage" type="text" value="<?php print htmle($frage); ?>" /></td><td colspan="2"><input type="checkbox" name="show_ampel" <?php if ($show_ampel) { print "checked='CHECKED'"; } ?> />Ampel anzeigen?</td></tr>
					<tr>
						<td>
							<?php print print_ampel($frage_id, 1); ?>
						</td>
						<td>
							<textarea width="200" height="100" name="antwort"><?php print htmlentities(get_antwort($frage_id)); ?></textarea>
							<input type="text" name="quelle" value="<?php print htmlentities($quelle); ?>" placeholder="Quelle" />
							<input type="text" name="grundrechtseinschraenkung" value="<?php print htmlentities($grundrechtseinschraenkung); ?>" placeholder="Grundrechtseinschränkung" />
							<input type="text" name="keywords" class="keywords_input" placeholder="Keyword eingeben" />
							<div class="keyword_input_holder"></div>
							<div class="keyword_holder"><?php  foreach($keywords as $keyword) { echo ("<div class='keyword'>".$keyword."</div>"); }  ?></div>
						</td>
						<td>
							<input type="submit" value="Speichern" />
						</td>
						<td>
							<input type="submit" name="delete_frage" value="Löschen" />
						</td>
					</tr>
				</table>
			</form><br>
<?php
		}
?>
		<form method="post" enctype="multipart/form-data" action="admin.php?page=<?php print $GLOBALS['this_page_number']; ?>">
			<input name="neue_frage" type="hidden" value="1" />
			<table>
			<tr><td colspan="2"><input name="frage" placeholder="Neue Frage" type="text" value="" /></td><td><input type="checkbox" checked="CHECKED" name="show_ampel" />Ampel anzeigen?</td></tr>
				<tr>
					<td>
						<?php print print_ampel($frage_id, 1, 1); ?>
					</td>
					<td>
						<textarea placeholder="Neue Antwort" width="200" height="100" name="antwort"></textarea>
						<input type="text" name="quelle" placeholder="Quelle" />
						<input type="text" name="grundrechtseinschraenkung" placeholder="Grundrechtseinschränkung" />
						<input type="text" name="keywords" class="keywords_input" placeholder="Keyword eingeben" />
						<div class="keyword_input_holder"></div>
						<div class="keyword_holder"></div>
					</td>
					<td>
						<input type="submit" value="Speichern" />
					</td>
				</tr>
			</table>
		</form><br>
	</div>

	<script type="text/javascript" src="js/ampel.js"></script>
<?php
	}
?>
