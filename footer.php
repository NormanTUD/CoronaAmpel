		<div class="footer">
<?php
	//include("mem.php");
	$this_page_file = ($_SERVER['REQUEST_URI']);
	if(preg_match('/\/(\?.*)?$/', $this_page_file)) {
		$this_page_file = 'admin.php';

	}
	$this_page_file = basename($this_page_file);
	$this_page_file = preg_replace('/\?.*/', '', $this_page_file);
?>
	<br />
	<div id="footermenu">
<?php
		echo "Memory-Usage: ".(memory_get_peak_usage(true)/1024/1024)." MB<br />\n\n";
		echo "PHP build time: ".sprintf("%.2fs", microtime(true) - $GLOBALS['php_start']);
?>
		<br />
		&copy; <?php
				$thisyear = date('Y');
				$startyear = 2021;
				if($thisyear == $startyear) {
					print date('Y');
				} else if($thisyear <= date('Y')) {
					print "$startyear&nbsp;&mdash;&nbsp;$thisyear";
				} else {
					print "$startyear &mdash;<span class='class_red'>An die Administratoren: Falsch eingestellte Server-Zeit. Bitte überprüfen.</span> &mdash; ";
				}
			?> Norman Koch
		</div>
	</div>
<?php
	include('query_analyzer.php');

	if($GLOBALS['end_html']) {
?>
	</div>
	<script>
		function colorHashMe () {
			$(".colorhashme").each(function () {
				var input = this;
				var colorHash = new ColorHash();
				var str = input.innerHTML;
				var hex = colorHash.hex(str);
				input.innerHTML = '<span class="hexcolored" style="color: ' + hex.toUpperCase() + ' !important;">' + str + '</span>';
			});
		}
		$(document).ready(function() {
			colorHashMe();
		});
	</script>
	</body>
</html>
<?php
	}
?>
