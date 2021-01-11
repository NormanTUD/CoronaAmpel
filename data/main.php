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
