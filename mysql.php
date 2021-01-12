<?php
	$GLOBALS['dbname'] = 'coronaampel';

	$dbfile = '/etc/dbpw';

	if(!file_exists($dbfile)) {
		$dbfile_windows = 'C:\\dbpw';
		if(file_exists($dbfile_windows)) {
			$dbfile = $dbfile_windows;
		}
	}

	if(file_exists($dbfile)) {
		$vvzdbpw = explode("\n", file_get_contents($dbfile))[0];

		if($vvzdbpw) {
			$username = 'root';
			$password = $vvzdbpw;

			$GLOBALS['dbh'] = mysqli_connect('localhost', $username, $password);
			if (!$GLOBALS['dbh']) {
				die("Kann nicht zur Datenbank verbinden!");
			}
		} else {
			die("Die Passwortdatei war leer bzw. das Passwort war nicht in der ersten Zeile.");
		}
	} else {
		die("Die Verbindung zur Datenbank konnte nicht hergestellt werden (falsches oder kein Passwort)");
	}
?>
