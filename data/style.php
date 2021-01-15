<?php
	header('Content-Type: text/css');
	$included_files = get_included_files();
	$included_files = array_map('basename', $included_files);

	if(!in_array('functions.php', $included_files)) {
		include_once('../functions.php');
	}
?>
td, th {
<?php
	if(get_setting("th_border_size") >= 1) {
?>
		border: <?php print get_setting("th_border_size"); ?>px solid <?php print get_setting('th_border_color'); ?>;
<?php
	}
?>
	transition: all 0.3s;
	font-weight: bold;
}

body {
	font-family: Arial, Helvetica, sans-serif;
	background-color: #009ee1;
	font-size: 14px;
	margin-top: 0px;
}

table {
	font-size: <?php print get_setting('table_font_size') ?>;
}

.ui-draggable, .ui-droppable {
	background-position: top;
}

.ui-draggable-dragging {
	top:0 !important;
}

#mainindex {
	padding: 5px;
	background-color: white;
	border-radius: 5px;
	text-align: center;
	margin-left: auto;
	margin-right: auto;
	min-width: 600px;
}

.center_content {
	margin-left: auto;
	margin-right: auto;
}

#main {
	min-width: 99%;
	padding: 5px;
	padding-top: 0px;
	display: inline-block;
	background-color: white;
	border-radius: 0px;
	height: auto;
}

#centered {
	width: 400px;
	height: 240px;

	position:absolute;
	left:0; right:0;
	top:0; bottom:0;
	margin:auto;

	-width:100%;
	max-height:100%;
	overflow:auto;
}

.datepicker {
	width: 80px;
}

#tabs {
	/*display: inline-block;*/
}

.small_input {
	width: 80px;
}

.ui-datepicker-trigger { display: none; }

a:focus { outline: none; } 

.small_text {
	font-size: 8pt;
}

.uebersicht_td {
	min-width: 100px;
}

.subsubheadline {
	background: #568abc;
	color: white;
}

.subheadline {
	background: #32597E;
	color: white;
}

div#menu ul {
	list-style-type: none;
	margin: 0;
	padding: 0;
	overflow: hidden;
	background-color: #32597E;
}

div#menu li {
	float: left;
}

div#menu li a {
	display: block;
	color: black;
	text-align: center;
	padding: 14px 16px;
	text-decoration: none;
}

div#menu li a:hover {
	background-color: #111;
}

#menu {
	text-align: justify;
}

#menu * {
	display: inline;
}

.topnav {
	background-color: #009ee1;
	overflow: hidden;
	padding-left: 0px;
	padding-right: 0px;
}

.topnav ul {
	list-style-type: none;
	margin: 0;
	padding: 0;
	overflow: hidden;
	background-color: #009ee1;
}

.topnav li {
	float: left;
	list-style-type: none;
	z-index: 400;
}

.topnav li a {
	display: block;
	color: white;
	text-align: center;
	padding: 5px;
	text-decoration: none;
	border-style: solid;
	border-width: 1px;
}

.topnav li a:hover {
	background-color: #black;
}

.dropdown {
	background-color: #00209D;
}

.selected_tab_important {
	background-color: #00caa1!important;
}


.selected_tab {
	background-color: #00caa1;
}

.topnav ul {
	background: #ffffff;
	list-style: none;
	position: absolute;
	left: -9999px;
	z-index: 100;
}
.topnav ul li {
	padding-top: 0px;
	float: none;
}
.topnav ul a {
	white-space: nowrap;
}
.topnav li:hover ul {
	left: auto;
}
.topnav li:hover a {
	background: #1d8bc9;
}
.topnav li:hover ul li a:hover {
	background: #333;
}

textarea {
	display: block;
	margin: 0;
	box-shadow: none;
	border-radius: none;
	padding: 4px;
	outline: none;
	border: solid 1px #707070;
}

textarea:focus {
	outline: none;
	border: solid 1px #707070;
	box-shadow: 0 0 5px 1px #969696;
}

input[type="password"] {
	display: block;
	margin: 0;
	width: 100%;
	font-family: sans-serif;
	box-shadow: none;
	border-radius: none;
	padding: 4px;
	border: solid 1px #dcdcdc;
	transition: box-shadow 0.3s, border 0.3s;
}

input[type="password"]:focus,
input[type="password"].focus {
	outline: none;
	border: solid 1px #707070;
	box-shadow: 0 0 5px 1px #969696;
}

input[type="text"] {
	display: block;
	margin: 0;
	width: 100%;
	font-family: sans-serif;
	box-shadow: none;
	border-radius: none;
	padding: 4px;
	border: solid 1px #dcdcdc;
	transition: box-shadow 0.3s, border 0.3s;
}

input[type="text"]:focus,
input[type="text"].focus {
	outline: none;
	border: solid 1px #707070;
	box-shadow: 0 0 5px 1px #969696;
}

select {
	border: 1px solid lightblue;
	border-radius: 3px;
	color: black;
	padding: 4px;
}

select:active {
	border: 1px solid #000;
}

button, input[type="button"], input[type="submit"] {
	border-radius: 3px;
	border: 0;
	background: none;
	box-shadow:none;
	border-radius: 0px;
	padding: 4px;
	background-color: #acacac;
}

.invisible_table {
	background-color: #ffffff !important;
}


.invisible_td { background-color: #ffffff !important; }

table {
	color: #333;
	//border-collapse: collapse;
	border-spacing: 0;
}

.invisible {
	color: #fff;
	border-spacing: 0;
	border: 0;
}

.invisible_td {
	font-family: Arial, Helvetica, sans-serif;
	color: #000000;
	background: #ffffff !important;
	border-spacing: 0;
	font-size: 15px;
	border: 0 !important;
	font-weight: normal;
}

th {
	background: #009ee1;
	font-weight: bold;
	color: white;
	font-size: 9px;
}

td {
	text-align: center;
	padding: 0;
	margin: 0;
}

tr:nth-child(even) td { background-color: <?php print get_setting("tr_even_color"); ?>; }

tr:nth-child(odd) td { background-color: <?php print get_setting("tr_odd_color"); ?>; }

tr:hover { color: blue; }

#filter {
	display: none;
}

pre {
	white-space: pre-wrap;       /* Since CSS 2.1 */
	white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
	white-space: -pre-wrap;      /* Opera 4-6 */
	white-space: -o-pre-wrap;    /* Opera 7 */
	word-wrap: break-word;       /* Internet Explorer 5.5+ */
}

.class_red {
	color: red;
}
.class_red a {
	color: red;
}
.class_red a:visited {
	color: red;
}

.class_green {
	color: green;
}
.class_green a {
	color: green;
}
.class_green a:visited {
	color: green;
}

.class_hotpink {
	color: hotpink;
}
.class_hotpink a {
	color: hotpink;
}
.class_hotpink a:visited  {
	color: hotpink;
}

.class_blue {
	color: blue;
}
.class_blue a {
	color: blue;
}
.class_blue a:visited  {
	color: blue;
}

.class_orange {
	color: orange;
}
.class_orange a {
	color: orange;
}
.class_orange a:visited {
	color: orange;
}

.trenner td {
	border-bottom: 3px solid black;
}

.autocenter {
	margin-left: auto;
	margin-right: auto;
	width: 500px;
}

.autocenter_large {
	margin-left: auto;
	margin-right: auto;
	min-width: 500px;
	width: auto;
	max-width: 1200px;
}

.tooltip {
	position: relative;
	display: inline-block;
	border-bottom: 1px dotted black;
}

.tooltip .tooltiptext {
	visibility: hidden;
	width: auto;
	background-color: black;
	color: #fff;
	text-align: center;
	padding: 5px 0;
	border-radius: 6px;
	position: absolute;
	z-index: 1;
}

.tooltip:hover .tooltiptext {
	visibility: visible;
}

/*
form {
	margin: 8px;
	border: 1px solid silver;
	padding: 8px;    
	border-radius: 4px;
}
*/

.Differences {
	width: 100%;
	border-collapse: collapse;
	border-spacing: 0;
	empty-cells: show;
}

.Differences thead th {
	text-align: left;
	border-bottom: 1px solid #000;
	background: #aaa;
	color: #000;
	padding: 4px;
}
.Differences tbody th {
	text-align: right;
	background: #ccc;
	width: 4em;
	padding: 1px 2px;
	border-right: 1px solid #000;
	vertical-align: top;
	font-size: 13px;
}

.Differences td {
	padding: 1px 2px;
	font-family: Consolas, monospace;
	font-size: 13px;
}

.DifferencesSideBySide .ChangeInsert td.Left {
	background: #dfd;
}

.DifferencesSideBySide .ChangeInsert td.Right {
	background: #cfc;
}

.DifferencesSideBySide .ChangeDelete td.Left {
	background: #f88;
}

.DifferencesSideBySide .ChangeDelete td.Right {
	background: #faa;
}

.DifferencesSideBySide .ChangeReplace .Left {
	background: #fe9;
}

.DifferencesSideBySide .ChangeReplace .Right {
	background: #fd8;
}

.Differences ins, .Differences del {
	text-decoration: none;
}

.DifferencesSideBySide .ChangeReplace ins, .DifferencesSideBySide .ChangeReplace del {
	background: #fc0;
}

.Differences .Skipped {
	background: #f7f7f7;
}

.DifferencesInline .ChangeReplace .Left,
.DifferencesInline .ChangeDelete .Left {
	background: #fdd;
}

.DifferencesInline .ChangeReplace .Right,
.DifferencesInline .ChangeInsert .Right {
	background: #dfd;
}

.DifferencesInline .ChangeReplace ins {
	background: #9e9;
}

.DifferencesInline .ChangeReplace del {
	background: #e99;
}

a, a:visited, a:hover, a:active {
	color: #0000EE;
}

.outline_text {
	color: #000;
	text-shadow:
	1px 1px 0 #fff,
	1px 1px 0 #fff,
	1px 1px 0 #fff,
	1px 1px 0 #fff;
}

.rainbow {
	font-variant-caps: small-caps;
	-webkit-animation: rainbow 1s infinite; 
	-ms-animation: rainbow 1s infinite;
	animation: rainbow 1s infinite; 
}

@-webkit-keyframes rainbow {
	20%{color: red;}
	40%{color: yellow;}
	60%{color: green;}
	80%{color: blue;}
	100%{color: orange;}	
}
@-ms-keyframes rainbow {
	20%{color: red;}
	40%{color: yellow;}
	60%{color: green;}
	80%{color: blue;}
	100%{color: orange;}	
}

@keyframes rainbow {
	20%{color: red;}
	40%{color: yellow;}
	60%{color: green;}
	80%{color: blue;}
	100%{color: orange;}	
}

.tiny_text {
	font-size: 9pt;
	font-style: italic;
	font-weight: bold;
}

#json {
	display: none;
}

.square {
	min-width: 400px;
	min-height: 60px;
	border-style: dotted;
	background-color: white;
}

.one {
	width: 40px;
	height: auto;
	min-height: 60px;
	float: left;
	vertical-align: middle
}

.two {
	margin-left: 15%;
	height: auto;
}

.message_text {
	color: black;
	font-size: 15px;
}

.blue_text {
	color: blue;
}

#headerfixed { 
	position: fixed; 
	top: 0px;
	display: block;
}

.selectboxstatus {
	width: 62px;
	font-size: 9px;
}

.info {
	min-height: 40px;
	position: fixed;
	bottom: 0%;
	width: 98%;
	background-color: white;
	color: black;
	opacity: 1;
	width: 100%;
	text-align: center;
}

.unbreakable {
	white-space: nowrap;
}

.warningbg {
	background-color: orange;
}

.spinnercontainer {
	background-color: #000;
	opacity: 0.5;
	width: 100%;
	height: 100%;
	z-index: 500;
	top: 0;
	left: 0;
	position: fixed;
}

div.spinnercontainer2 {
	width: 100%;
	height: 100%;
	z-index: 501;
	top: 50%;
	left: 50%;
	position: fixed;
}

.fullwidth {
	width: 100%;
}

.warning_td {
	font-color: #FFCC00;
	color: #FFCC00;
	text-decoration: underline dotted #FFCC00;
	min-width: 40px;
	font-size: 20px;
}

.corrected_td {
	font-color: green;
	color: green;
	text-decoration: underline dotted green;
	min-width: 40px;
	font-size: 20px;
}

.info_td {
	font-color: purple;
	color: purple;
	text-decoration: underline dotted purple;
	min-width: 80px;
}

.error_td {
	font-color: red;
	color: red;
	text-decoration: underline dotted red;
	min-width: 40px;
	font-size: 20px;
}

#mainlogo {
	left: -5px;
	top: 0px;
	position: relative;
}

.write_manually {
	min-width: 200px;
	min-height: 40px;
	width: 80%;
	font-size: 30px;
}

.no_decoration {
	text-decoration: none;
	color: white;
}

.no_decoration:visited {
	text-decoration: none;
	color: white;
}

.line_tr {
	z-index: 300;
}
.table_font {
	font-size: <?php print get_setting("table_font_size"); ?>;
}

.box_around_termin {
	padding: 0px;
	margin: 0px; 
	min-width: <?php print get_setting("min_width_column"); ?>px;
}

.version {
	font-family: monospace;
	background-color: #f0f0f0;
}

.version_string {
	font-size: 12px;
}

.footer {
	width: 100%;
	text-align: center;
	margin-top: 20px;
}

.pseudobutton {
	background-color: #009fe3;
	border: none;
	color: white;
	padding: 15px 32px;
	text-align: center;
	text-decoration: none;
	display: inline-block;
	font-size: 16px;
}



.pseudobutton_red, .pseudobutton_red:visited, .pseudobutton_red:hover, .pseudobutton_red:active {
	background-color: #f44336;
	border: none;
	color: white;
	padding: 15px 32px;
	text-align: center;
	text-decoration: none;
	display: inline-block;
	font-size: 16px;
}

.welcome_text {
	font-size: 25px;
}

.skull {
	width: 120px;
}

.einstellungen_td {
	width: 400px;
}

.keyword {
	border: 2px grey solid;
    margin-bottom: 4px;
}

.keyword_delete {
	position: relative;
	top: 0;
	right: 0;
	text-align: center;
	color: red;
}

.keyword_delete:hover {
	cursor: pointer;
}