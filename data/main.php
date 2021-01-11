<?php
	$included_files = get_included_files();
	$included_files = array_map('basename', $included_files);

	if(!in_array('functions.php', $included_files)) {
		include_once('../functions.php');
	}
?>
/*jshint esversion: 6 */

if (!Date.now) {
    Date.now = function() { return new Date().getTime(); }
}

Date.isLeapYear = function (year) {
	return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0));
};

Date.getDaysInMonth = function (year, month) {
	return [31, (Date.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
};

Date.prototype.isLeapYear = function () {
	return Date.isLeapYear(this.getFullYear());
};

Date.prototype.getDaysInMonth = function () {
	return Date.getDaysInMonth(this.getFullYear(), this.getMonth());
};

Date.prototype.addMonths = function (value) {
	var n = this.getDate();
	this.setDate(1);
	this.setMonth(this.getMonth() + value);
	this.setDate(Math.min(n, this.getDaysInMonth()));
	return this;
};

var letztes_update = null;
var loc = window.location.pathname;
var dir = loc.substring(0, loc.lastIndexOf('/'));
var disable_termin;
var add_termin;
var update_data;
var reveal_data;
var show_spinner;
var hide_spinner;
var verschiebe_spaetere_confirm_selector = $("#verschiebe_spaetere_confirm");
var wait_ms_ok = <?php print get_setting("wait_ms_ok"); ?>;
var wait_ms_error = <?php print get_setting("wait_ms_error"); ?>;
var last_verschiebung_time;

function wait(ms) {
	<?php print javascript_debugger("wait"); ?>
	var start = new Date().getTime();
	var end = start;
	while(end < start + ms) {
		end = new Date().getTime();
	}
	<?php print javascript_debugger("wait", 1); ?>
}


function tr_is_visible (anlage_id) {
	<?php print javascript_debugger("reveal"); ?>
	var name = '#tr_' + anlage_id;

	var display_status = $($(name).closest("tr").find("td").find(".commentbox")[0]).css("display");
	if(display_status == 'none') {
		return 0;
	} else {
		return 1;
	}
}

function reveal (anlage_id) {
	<?php print javascript_debugger("reveal"); ?>
	var name = '#tr_' + anlage_id;

	var parent_tr = $(name).closest('tr');

	$(name).closest("tr").find("td").find(".commentbox").each(function() {
		$(this).toggle();
		update_wartungstabelle_size();
	});
	<?php print javascript_debugger("reveal", 1); ?>
}

function ajax_post_without_spinner (url, data, successfunction, errorfunction) {
	$.ajax({
		'async': false,
		'type': "POST",
		'global': false,
		'dataType': 'html',
		'url': url,
		'data': data,
		success: successfunction,
		error: errorfunction
	});
}

function ajax_post (url, data, successfunction, errorfunction) {
	<?php print javascript_debugger("ajax_post"); ?>
	show_spinner();
	ajax_post_without_spinner(url, data, successfunction, errorfunction);
	hide_spinner();
	<?php print javascript_debugger("ajax_post", 1); ?>
}

function truncate (x) {
	var max_length = <?php print get_setting("debug_truncate_limit"); ?>;
	if (typeof variable !== 'undefined') {
		if (typeof(x) === 'object') {
			x = JSON.stringify(x);
		}
		if (x.length > max_length) {
			x = x.substr(0, max_length - 3) + "...";
		}
		return x;
	} else {
		return 'undefined';
	}
}

var all_gets = "<?php
	$str = '';
	$get_array = array();
	foreach ($_GET as $name => $value) {
		if($name != "page" && $name != "jahrstart" && $name != "jahreplus") {
			$get_array[] = "$name=$value";
		}
	}
	$str = join('&', $get_array);
	print $str;
?>";
var base_url_tr_reload = "get_row_again.php?jahrstart=<?php print urlencode($jahrstart); ?>&jahreplus=<?php print urlencode($jahreplus); print "&$str"; ?>";



<?php
if(get_setting("reload_page_instead_of_line")) {
?>
	function reload_page () {
		<?php print javascript_debugger("reload_page"); ?>
		location.reload();
		<?php print javascript_debugger("reload_page", 1); ?>
		return false;
	}
<?php
}
?>

function add_to_debug (code) {
	<?php print javascript_debugger("add_to_debug"); ?>
	var regex = new RegExp("<!--JSONSTART:(.*):JSONEND-->");
	var match = regex.exec(code);
	var debug_code = match[1];
	var debug_obj = $.parseJSON(debug_code);
	var debug_string = debug_obj.add_to_debug;

	$("#query_debugger_add").append(debug_string);
	<?php print javascript_debugger("add_to_debug", 1); ?>
}

function get_letzte_wartung () {
	<?php print javascript_debugger("get_letzte_wartung"); ?>
	var updatefile = dir + '/last_updated_wartung.php';
	var update = null;
	ajax_post_without_spinner(updatefile, { }, function (data) { update = data; }, function (data) { } );

	<?php print javascript_debugger("get_letzte_wartung", 1); ?>
	return update;
}

function update_wartungstabelle_size () {
	<?php print javascript_debugger("update_wartungstabelle_size"); ?>
	$("#wartungstabelle").width("auto");
	<?php print javascript_debugger("update_wartungstabelle_size", 1); ?>
}

function update_letzte_wartung () {
	<?php print javascript_debugger("update_letzte_wartung"); ?>
	letztes_update = get_letzte_wartung();
	<?php print javascript_debugger("update_letzte_wartung", 1); ?>
}

function show_info (message, time) {
	<?php print javascript_debugger("show_info"); ?>
	var re = new RegExp("JSONSTART");
	if (re.test(message)) {
		add_to_debug(message);
	}
	if(time) {
		if($('.info').is(':visible')) {
			$("<div class='info'>" + message + "</div>").appendTo("body").delay(time).hide(0);
		} else {
			$("<div class='info'>" + message + "</div>").appendTo("body").delay(time).hide(0);
		}
	} else {
		if($('.info').is(':visible')) {
			$("<div class='info'>" + message + "</div>").appendTo("body");
		} else {
			$("<div class='info'>" + message + "</div>").appendTo("body");
		}
	}
	<?php print javascript_debugger("show_info", 1); ?>
}
<?php
if(get_setting("check_for_changed_table")) {
?>
	function show_warning_if_newer_update () {
		<?php print javascript_debugger("show_warning_if_newer_update"); ?>
<?php
		if(get_setting("check_for_changed_table")) {
?>
			var letzte_wartung = get_letzte_wartung();

			if(letztes_update != letzte_wartung) {
				var warning = "<img height=40 src='" + dir + "/i/update.svg'>";
				show_info("<span class='warningbg'>" + warning + " Die Wartungstabelle wurde geupdatet. Bitte lade die Seite neu! " + warning + "</span>", 0);
				update_letzte_wartung();
			}
<?php
		}
?>
		<?php print javascript_debugger("show_warning_if_newer_update", 1); ?>
	}
<?php
}
?>

var status_color = {
<?php
	$query = 'select id, color from status';
	$result = rquery($query);

	$colors = array();
	while ($row = mysqli_fetch_row($result)) {
		$array[] = "'$row[0]': '#$row[1]'";
	}
	print join(', ', $array);
?>
};

$(document).ready(function(){
	update_letzte_wartung();
	update_wartungstabelle_size();

	function update_tr (data) {
		<?php print javascript_debugger("update_tr"); ?>
		var obj = $.parseJSON(data);
		var anlage_id = obj.anlage_id;

		var str = obj.str;

		var name = "#tr_" + String(anlage_id);

		var tr_selector = $(name);

		tr_selector.empty();
		tr_selector.html(str);
		tr_selector.append("");

		update_wartungstabelle_size();

		begin_left_right();
		<?php print javascript_debugger("update_tr", 1); ?>
	}

	function reload_line (param, anlage_id) {
		<?php print javascript_debugger("reload_line"); ?>
<?php
		if(get_setting("reload_page_instead_of_line")) {
?>
			reload_page();
<?php
		} else {
?>
			var is_visible = tr_is_visible(anlage_id);
			wait(<?php print get_setting("wait_ms_before_reload_line"); ?>);
			var url = base_url_tr_reload + "&is_visible=" + is_visible + "&" + param;
			ajax_post (url, {}, function (data) { update_tr(data); }, function (jXHR, textStatus, errorThrown) { show_info(errorThrown, wait_ms_error); } );
			update_letzte_wartung();
			wait(100);
<?php
		}
?>
		<?php print javascript_debugger("reload_line", 1); ?>
		activate_left_right_handle();
	}

	show_spinner = function () {
		<?php print javascript_debugger("show_spinner"); ?>
<?php
		if(get_setting("show_spinner")) {
?>
			$(".spinnercontainer").show();
			document.body.style.cursor = "wait";
<?php
		}
?>
		<?php print javascript_debugger("show_spinner", 1); ?>
	}

	hide_spinner = function () {
		<?php print javascript_debugger("hide_spinner"); ?>
<?php
		if(get_setting("show_spinner")) {
?>
			$(".spinnercontainer").hide();
			document.body.style.cursor = "default";
<?php
		}
?>
		<?php print javascript_debugger("hide_spinner", 1); ?>
	}

	reveal_data = function (clickedItem) {
		<?php print javascript_debugger("reveal_data"); ?>
		var anlage_id = $(clickedItem).data("anlageid");
		reveal(anlage_id);
		<?php print javascript_debugger("reveal_data", 1); ?>
	};


	update_map_data = function (item) {
		<?php print javascript_debugger("update_map_data"); ?>
		var thisdata = $(name).serialize();
console.log(thisdata);

		if(thisdata != '') {
			ajax_post(submitfile, thisdata, function (data) { show_info(data, wait_ms_ok); update_letzte_wartung(); update_wartungstabelle_size(); reload_line("anlage_id=" + anlage_id, anlage_id); }, function (jXHR, textStatus, errorThrown) { show_info(errorThrown, wait_ms_error); update_letzte_wartung(); });
		}
		<?php print javascript_debugger("update_map_data", 1); ?>
	};

	update_data = function (item) {
		<?php print javascript_debugger("update_data"); ?>
		var closest_collapsable_div = $(item).closest(".collapsable");
		var status_select = $(closest_collapsable_div).find('select');
		$(closest_collapsable_div).css("background-color", status_color[$(status_select).val()]);
		var submitfile = dir + '/submit.php';
		var anlage_id = $(item).attr('data-anlageid');
		var name = "#tr_" + anlage_id + " :input";
		var thisdata = $(name).serialize();

		if(thisdata != '') {
			ajax_post(submitfile, thisdata, function (data) { show_info(data, wait_ms_ok); update_letzte_wartung(); update_wartungstabelle_size(); reload_line("anlage_id=" + anlage_id, anlage_id); }, function (jXHR, textStatus, errorThrown) { show_info(errorThrown, wait_ms_error); update_letzte_wartung(); });
		}
		<?php print javascript_debugger("update_data", 1); ?>
	};

	disable_termin = function (termin_id, anlage_id) {
		<?php print javascript_debugger("disable_termin"); ?>
		var updatefile = dir + '/submit.php';
		var data = {
			"json": 1,
			"disable_termin": 1,
			"termin_id": termin_id
		};

		var ret = 0;

<?php
		if(get_setting("warn_before_delete")) {
?>
			swal({
					title: "Sicher dass ich den Termin löschen soll?",
					text: "Das lässt sich nicht mehr rückgängig machen!",
					type: "warning",
					showCancelButton: true,
					buttons: ['Ja', 'Abbrechen'],
					closeOnConfirm: false,
					dangerMode: true
				}
			).then((value) =>  {
				if(!value) {
					ret = 1;
					ajax_post(updatefile, data, function (data) { show_info(data, wait_ms_ok); update_letzte_wartung(); reload_line("anlage_id="+String(anlage_id), anlage_id); reveal(anlage_id);  }, function (jXHR, textStatus, errorThrown) {show_info(errorThrown, wait_ms_error);});
				} else {
					swal({
						title: "Der Termin hat nochmal Glück gehabt",
						type: "success"
					});
				}

			});
<?php
		} else {
?>
			ret = 1;
			ajax_post(updatefile, data, function (data) { show_info(data, wait_ms_ok); update_letzte_wartung(); reload_line("anlage_id="+String(anlage_id), anlage_id); reveal(anlage_id);  }, function (jXHR, textStatus, errorThrown) {show_info(errorThrown, wait_ms_error);});

<?php

		}
?>

		<?php print javascript_debugger("disable_termin", 1); ?>
		return ret;
	};

	add_termin = function (anlage_id, this_monat, this_year) {
		<?php print javascript_debugger("add_termin"); ?>
		var updatefile = dir + '/submit.php';

		if(this_monat > 12) {
			this_monat = this_monat % 12;
			if(this_monat == 0) {
				this_monat = 12;
			}
		}

		var data = {
			"json": 1,
			"new_termin": 1,
			"anlage_id": anlage_id,
			"monat": this_monat,
			"year": this_year
		};
		ajax_post(updatefile, data, function (data) { show_info(data, wait_ms_ok); update_letzte_wartung(); reload_line("anlage_id="+String(anlage_id), anlage_id); }, function (jXHR, textStatus, errorThrown) { show_info(errorThrown, wait_ms_error); });

		<?php print javascript_debugger("add_termin", 1); ?>
	};

	function begin_left_right () {
		<?php print javascript_debugger("begin_left_right"); ?>

		$(function() {
			<?php print javascript_debugger("make_snappable_pseudo"); ?>

			<?php print javascript_debugger("snap_droppable"); ?>

<?php
			if(get_setting("enable_droppable")) {
?>
				$(".snap").droppable({
					tolerance: "pointer",
					classes: {
						"ui-droppable-active": "ui-state-active"
					},
					tolerance: 'pointer',
					drop: function(event, ui) {
						var this_this = $(this);
						ui.draggable.addClass('dropped');

						ui.draggable.data('droppedin', this_this);
						var draggableId = ui.draggable;

						ui.draggable.attr('data-dropped-Id', this_this.attr('id'));

						var anlage_id = this_this.attr("data-anlageid");
						var neuer_termin = $(draggableId).attr("data-terminid");

						var new_monat = this_this.data("monattrue");
	alert("A");

						var new_jahr = this_this.data("jahr")

						verschiebe_termin(neuer_termin, anlage_id, new_jahr, new_monat);
					}
				});
<?php
			}
?>
			<?php print javascript_debugger("snap_droppable", 1); ?>

<?php
			if(get_setting("enable_droppable")) {
?>
				function make_draggable () {
					<?php print javascript_debugger("make_draggable"); ?>
					var snap_ui_droppable_selector = $('.snap.ui-droppable');
					$('.draggable').each(function(){
						$(this).mouseenter(function () {
							var this_selector = $(this);
							this_selector.draggable({
								delay: 300,
								distance: 10,
								cursor: "grabbing",
								revert: "invalid",
								addClasses: false,
								containment: [
									snap_ui_droppable_selector.first().position().left,
									this_selector.position().top,
									snap_ui_droppable_selector.last().position().left,
									this_selector.position().top
								],
								axis: "x",
								snap: ".snap",
								snapTolerance: 80,
								start: function(event, ui) {
									this_selector.css("top", 0);
								},
								drag: function(event, ui) {
									this_selector.css("z-index", 50);
									this_selector.css("top", 0);
								},
								stop: function(event, ui) {
									this_selector.css("z-index", 50);
									this_selector.css("top", 0);
								}
							});
						});

						//$(this).mouseleave(function () {
						//	$(this).droppable('destroy');
						//});
					});
				<?php print javascript_debugger("make_draggable", 1); ?>
				}

				<?php print javascript_debugger("make_draggable_pseudo"); ?>
				make_draggable();
				<?php print javascript_debugger("make_draggable_pseudo", 1); ?>
				<?php print javascript_debugger("make_snappable_pseudo", 1); ?>
<?php
			}
?>
		});
		<?php print javascript_debugger("begin_left_right", 1); ?>
	}
	function activate_left_right_handle() {
		$(".moveleftrighthandle").each(function () {
			$(this).on("click", function(){
				var anlage_id = $(this).attr("data-anlageid");
				var termin_id = $(this).attr("data-terminid");
				var monatdiff = $(this).attr("data-monatdiff");
				verschiebe_termin_um_monat(anlage_id, termin_id, monatdiff);
			});
		});
	}

	activate_left_right_handle();

	function verschiebe_termin_um_monat (anlage_id, id, monat_diff) {
		<?php print javascript_debugger("verschiebe_termin"); ?>
		if(!last_verschiebung_time || (Date.now() - last_verschiebung_time) > 1) {
			var submitfile = dir + '/submit.php';
			var verschiebe_spaetere_confirm = verschiebe_spaetere_confirm_selector.is(':checked') ? 1: 0;

			var verschiebe_spaetere = 0;
			if(verschiebe_spaetere_confirm) {
				verschiebe_spaetere = 1;
			}

			var data = { 
				"json": 1,
				"terminid": id,
				"monat_diff": monat_diff,
				"verschiebe_spaetere": verschiebe_spaetere
			};

			ajax_post(submitfile, data, function (data) { show_info(data, wait_ms_ok); }, function (jXHR, textStatus, errorThrown) { show_info(errorThrown, wait_ms_error); });

			reload_line("anlage_id=" + String(anlage_id) + "&terminid=" + String(id), anlage_id);
		}
		last_verschiebung_time = Date.now();
		<?php print javascript_debugger("verschiebe_termin", 1); ?>
	}


	function verschiebe_termin (id, anlage_id, new_jahr, new_monat) {
		<?php print javascript_debugger("verschiebe_termin"); ?>
		var submitfile = dir + '/submit.php';
		var verschiebe_spaetere_confirm = verschiebe_spaetere_confirm_selector.is(':checked') ? 1: 0;

		var verschiebe_spaetere = 0;
		if(verschiebe_spaetere_confirm) {
			verschiebe_spaetere = 1;
		}

		var data = { 
			"json": 1,
			"terminid": id,
			"new_jahr": new_jahr,
			"new_monat": new_monat,
			"verschiebe_spaetere": verschiebe_spaetere
		};

		ajax_post(submitfile, data, function (data) { show_info(data, wait_ms_ok); }, function (jXHR, textStatus, errorThrown) { show_info(errorThrown, wait_ms_error); });

		update_letzte_wartung();

		reload_line("anlage_id=" + String(anlage_id) + "&terminid=" + String(id), anlage_id);

		<?php print javascript_debugger("verschiebe_termin", 1); ?>
	}

	begin_left_right();
<?php
	if(get_setting("check_for_changed_table")) {
?>
		window.setInterval(function(){
			show_warning_if_newer_update();
		}, 5000);
<?php
	}
?>

});

