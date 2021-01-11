<?php
	$GLOBALS['setup_mode'] = 0;
	include("functions.php");
	if(check_page_rights(get_page_id_by_filename(basename(__FILE__)))) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann
		$anonymize = get_get_int('anonymize');
		$filedate = date('Y-m-d_H-m-s', time());
		if(get_get("version")) {
			$filedate = get_get("version");
		}
		$anon = '';
		if($anonymize) {
			$anon = '-anonymized';
		}
		header('Content-type: application/sql, charset=utf-8');
		header('Content-Disposition: attachment; filename="dbbackup-'.$filedate.$anon.'.sql"');
		print backup_tables('*', null, $anonymize);
	} else {
		die("Leider haben Sie keinen Zugriff auf diese Seite.");
	}
?>
