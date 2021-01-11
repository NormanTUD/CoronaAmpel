<?php
	if(!isset($setup_mode)) {
		$setup_mode = 0; // Im setup-Modus werden keine Anfragen ausgeführt. Setupmodus deaktiviert.
	}
	include_once("functions.php");

	if($GLOBALS['reload_page']) {
		header("Refresh:0");
	}

	if(!$page_title) {
		$page_title = 'Wartungstabelle';
	}
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="favicon.ico" type="image/x-icon" />

		<meta name="description" content="Wartungstabelle">
		<meta name="keywords" content="Wartungstabelle">
		<meta name="author" content="Norman Koch">
<?php
		if(preg_match('/index/', basename($_SERVER['SCRIPT_NAME']))) {
?>
			<title><?php
				print htmlentities($page_title);
				$chosen_page_id = get_get('page');
				if(!$chosen_page_id) {
					$chosen_page_id = get_get('show_items');
				}
				if($chosen_page_id) {
					if(check_page_rights($chosen_page_id, 0)) {
						$father_page = get_father_page($chosen_page_id);
						if($father_page) {
							print " | ".get_page_name_by_id($father_page);
						}

						$this_page_title = get_page_name_by_id($chosen_page_id);
						if($this_page_title) {
							print " | ".$this_page_title;
						}
					} else {
						print " &mdash; Kein Zugriff auf diese Seite";
					}
				}
			?></title>
<?php
		} else {
?>
			<title><?php print htmlentities($page_title); ?></title>
<?php
		}
?>
		<meta charset="UTF-8" />

		<link rel="stylesheet" href="data/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="data/style.php">
		<link rel="stylesheet" href="data/spin.css">
		<link rel="stylesheet" href="data/dark.css">
		<script src="data/sweetalert.min.js"></script>
		<script src="data/jquery-1.12.4.js"></script>
		<script src="data/jquery-ui.js"></script>
		<script src="data/main.js"></script>
		<script src="data/color-hash.js"></script>
		<script src="data/jscolor.js"></script>
		<script src="data/scroll.js"></script>
		<script>
			function hide_lines_not_these_anlage_id (anlagen_ids) {
				if (anlagen_ids === undefined || anlagen_ids === null) {
					alert("Keine Ergebnisse!");
				} else {
					var selector_string = '';
					for (let i=0; i < anlagen_ids.length; i++) {
						if(i == 0) {
							selector_string = "#tr_" + anlagen_ids[i];
						} else {
							selector_string = selector_string + ", #tr_" + anlagen_ids[i];
						}
					}
					$('.line_tr:not(' + selector_string + ')').hide();
					$('.divider').hide();
				}
				$("#number_of_rows").html($("#wartungstabelle tr:visible:not(divider_line)").length  - 1);
			}

			function show_all_anlagen () {
				$('.line_tr').show();
				$('.divider').show();
				$("#number_of_rows").html($("#wartungstabelle tr:visible:not(divider_line)").length  - 1);
			}

			function get_json (url) {
				return new Promise(function(resolve, reject) {
					var xhr = new XMLHttpRequest();
					xhr.open('get', url, true);
					xhr.responseType = 'json';
					xhr.onload = function() {
						var status = xhr.status;
						if (status == 200) {
							resolve(xhr.response);
						} else {
							reject(status);
						}
					};
					xhr.send();
				});
			};

			var livesearch = function livesearch () {
				var anything = $("#livesearch").val();
				if(!anything || /^\s*$/.test(anything)) {
					$("#katze").html("");
					$("#suchewarnung").html("");
					show_all_anlagen();

					var suche = "";
					var old_url = document.URL;
					var new_url = old_url;
					if(new_url.match("&search=")) {
						new_url = new_url.replace(/&search=.*($|&)/, "")
					}

					new_url = new_url.replace(/&$/, "")
					window.history.pushState({}, "Wartungstabelle: Suche nach " + suche, new_url);
				} else {
					$("#suchewarnung").html("");

					if(/katz|cat|kater|mietz|miez/.test(anything.toLowerCase())) {
						$("#katze").html("<img src='data/cat.jpg' />");
					} else {
						$("#katze").html("");
					}

					var search_url = "search_rows.php?search=" + anything;
					$.ajax({
						dataType: 'json', 
						async: true,
						url: search_url,
						success: function (data) { 
							if(data === undefined || !data.length) {
								show_all_anlagen();
								$("#suchewarnung").html("<span style='color: red;'>Keine Ergebnisse für &raquo;" + anything + "&laquo; gefunden. Es wird alles angezeigt.</span>");
							} else {
								show_all_anlagen();
								hide_lines_not_these_anlage_id(data);
								if($("#wartungstabelle tr:visible").length == 1) {
									show_all_anlagen();
									$("#suchewarnung").html("<span style='color: red;'>Keine Ergebnisse für &raquo;" + anything + "&laquo; gefunden. Es wird alles angezeigt.</span>");
								} else {
									$("#suchewarnung").html("");
								}
							}
						}
					});

					var suche = $("#livesearch").val();
					var old_url = document.URL;
					var new_url = old_url;
					if(new_url.match("&search=")) {
						new_url = new_url.replace(/&search=.*($|&)/, "&search=" + suche + "&")
					} else {
						new_url = new_url + "&search=" + suche;
					}

					new_url = new_url.replace(/&$/, "")

					window.history.pushState({}, "Wartungstabelle: Suche nach " + suche, new_url);

					return 'done';
				}
			}
<?php
			if(get_get('search')) {
?>
				$( document ).ready(function() {
					livesearch();
				});
<?php
			}
?>

			var add_link_startseite_suche = function add_link_startseite_suche () {
				$(".add_search_term").each(function (i, item) {
					item.href = item.href.replace(new RegExp("&search=.*", "gm"), "");
					item.href += "&search=" + $("#livesearch_startseite").val();
			       	});
			}
		</script>

<?php
		if(
			get_get("page") == get_page_id_by_filename("anlagen.php") || 
			get_get("page") == get_page_id_by_filename("ersatzteile.php") || 
			get_get("page") == get_page_id_by_filename("kunden.php")
		) {
?>
			<!--<script src="data/autosubmit.js"></script>-->
<?php
		}
?>
	</head>
<body>
