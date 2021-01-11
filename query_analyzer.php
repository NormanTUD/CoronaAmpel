<?php
	if(get_setting("enable_query_debugger")) {
		include_once('scripts/SqlFormatter.php');
		$result = '';
		$result .= "<div style='clear: both;' id='query_debugger' />\n";
		$result .= "<div class='autocenter_large'>\n";
		$json = get_post("json") ? 1 : 0;
		$add_javascript_query_debug = get_setting("add_javascript_query_debug");
		if(!$add_javascript_query_debug) {
			$json = 0;
		}
		$highlight = get_setting("highlight_debugger");
		$hide_debugger = get_setting("hide_debugger");

		$called_from = basename($_SERVER['PHP_SELF']);
		if($called_from == 'submit.php' || $called_from == "get_row_again.php") {
			$json = 1;
		}

		if(!$json) {
			if($hide_debugger) {
				$result .= "<a onclick='$(\"#query_analyzer\").toggle()' class='outline_text'>Query-Debugger anzeigen?</a>\n";
				$result .= "<div style='clear: both; display: none;' id='query_analyzer' />\n";
			} else {
				$result .= "<div style='clear: both;' id='query_analyzer' />\n";
			}
		} else {
			$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$result .= "<h2>$actual_link</h2>";
			$result .= "<pre>".htmlentities(print_r($_POST, 1))."</pre>";
			$result .= "<div style='clear: both; display: none;' id='query_analyzer' />\n";
		}
		$result .= "\t<table style='max-width: 1350px; background-color: #707070'><tr><th>Query</th><th>Duration</th><th>Numrows</th><th>Query doppelt?</th></tr>\n";
		$i = 0;
		$j = 0;
		$sum = 0;
		$rows = 0;
		$done_queries = array();
		$irgendeine_query_doppelt = 'Nein';
		foreach ($GLOBALS['queries'] as $item) {
			if(!preg_match('/;$/', $item['query'])) {
				$item['query'] .= ';';
			}
			$item['query'] = preg_replace('/`session_id` = "[^"]+"/', '`session_id` = "" /* !!!ausgeblendet!!! */', $item['query']);
			$item['query'] = preg_replace('/INSERT IGNORE INTO `session_ids` \(`session_id`, `user_id`\) VALUES \("[^"]+"/', 'INSERT IGNORE INTO `session_ids` (`session_id`, `user_id`) VALUES ("/* !!!ausgeblendet!!! */"', $item['query']);


			$item['query'] = preg_replace('/`password_sha256` = "[^"]+"/', '`password_sha256` = "" /* !!!ausgeblendet!!! */', $item['query']);

			if(array_key_exists('numrows', $item) && is_int($item["numrows"])) {
				$rows += $item['numrows'];
			} else {
				$item['numrows'] = '&mdash;';
			}

			$item['cleaned_query'] = $item['query'];
			$item['cleaned_query'] = preg_replace('/\/\*.*?\*\/\s*/', '', $item['cleaned_query']);

			$query_doppelt = array_key_exists($item['cleaned_query'], $done_queries) ? '!!!Ja!!!' : 'Nein';
			if(
				preg_match('/set autocommit/i', $item['query']) ||
				preg_match('/ROLLBACK/i', $item['query']) ||
				preg_match('/START TRANSACTION;/i', $item['query']) ||
				preg_match('/COMMIT;/i', $item['query'])
			) {
				$query_doppelt = 'Nein';
			}
			if($query_doppelt == '!!!Ja!!!') {
				$irgendeine_query_doppelt = '!!!Ja!!!';
			}
			if($highlight) {
				$result .= "\t\t<tr><td>".SqlFormatter::highlight($item['query'])."</td><td>".number_format($item['time'], 6)."</td><td>".$item['numrows']."</td><td>".$query_doppelt."</td>\n";
			} else {
				$result .= "\t\t<tr><td><pre>".$item['query']."</pre></td><td>".number_format($item['time'], 6)."</td><td>".$item['numrows']."</td><td>".$query_doppelt."</td>\n";
			}
			if(preg_match('/^\s*\/\*.*\*\/\s*(UPDATE|SELECT|DELETE|INSERT)\s(?!@@)/i', $item['query'])) {
				$i++;
			} else {
				$j++;
			}
			$sum += $item['time'];
			$done_queries[$item['cleaned_query']] = 1;

		}

		if($irgendeine_query_doppelt == '!!!Ja!!!') {
			$irgendeine_query_doppelt = '<span class="class_red">!!!Ja!!!</span>';
		} else {
			$irgendeine_query_doppelt = '<span class="class_green">Nein</span>';
		}

		$result .= "\t\t<tr><td>&mdash;</td><td>&sum;Zeit&darr;</td><td>&sum;NR&darr;</td><td>Queries Doppelt? $irgendeine_query_doppelt</td></tr>\n";
		$result .= "\t\t<tr><td>All ".($j + $i)." Queries ($j preparational, $i functional)</td><td>".number_format($sum, 8)."</td><td>$rows</td><td></td></tr>\n";
		$php_time = microtime(true) - $GLOBALS['php_start'];
		$result .= "\t\t<tr><td>PHP without Queries</td><td>".number_format($php_time - $sum, 8)."</td><td></td><td></td></tr>\n";
		$result .= "\t\t<tr><td>All</td><td>".number_format($php_time, 6)."</td><td></td><td></td></tr>\n";
		$result .= "\t</table>\n";
		if(count($GLOBALS['function_usage'])) {
			$result .= "<br /><br />\t<table style='max-width: 1350px; background-color: #707070'><tr><th>Funktionsname</th><th>Anzahl Aufrufe</th><th>Zeit in Queries</th></tr>\n";
			foreach ($GLOBALS['function_usage'] as $name) {
				$result .= "\t\t<tr><td>".$name['name']."</td><td>".$name['count']."</td><td>".number_format($name['time'], 6)."</td></tr>\n";
			}
			$result .= "\t</table>\n";
		}

		$included_files = get_included_files();
		$included_files = array_map('basename', $included_files);

		$result .= "\t<br /><table>\n";
		$i = 0;
		foreach ($included_files as $id => $name) {
			if($i == 0) {
				$result .= "\t\t<tr><td style='background-color: rgb(0, 48, 94); color: white'>Benutzte Dateien:</td></tr>\n";
			}
			if(!file_exists($name)) {
				$testname = "./pages/$name";
				$testname2 = "./scripts/$name";
				if(file_exists($testname)) {
					$name = $testname;
				} else if (file_exists($testname2)) {
					$name = $testname2;
				} else {
					$name = "<span style='color: red'>$name</span>";
				}
			}
			$result .= "\t\t<tr><td>$name</td></tr>\n";
			$i++;
		}
		$result .= "\t</table>\n";
	
		if(!$json) {
			$result .= "<div id='query_debugger_add'></div>\n";
		}
		$result .= "</div>\n";
		$result .= "</div>\n";
		if($json) {
			print '<!--JSONSTART:{"add_to_debug":'.json_encode($result)."}:JSONEND-->";
		} else {

			print $result;
		}
	}
?>
