<?php
	$included_files = get_included_files();
	$included_files = array_map('basename', $included_files);

	if(!in_array('functions.php', $included_files)) {
		include_once('../functions.php');
	}

	if(check_page_rights(get_page_id_by_filename(basename(__FILE__)))) { // Wichtig, damit Niemand ohne Anmeldung etwas ändern kann

?>
		<div id="welcome">
<?php
			include_once('hinweise.php');
?>
			<h2>Was befindet sich in der Navigationsleiste?</h2>

			<p>
				<ul style="list-style: none;">
<?php
					$pagedata = create_page_info();

					$page_ids = array();
					foreach ($pagedata as $thispage){
						$page_ids[] = $thispage[0];
					}

					$page_rights_data = check_page_rights($page_ids, 0);
					
					foreach ($pagedata as $thispage){
						# 0	   1	   2		3		    4
						#`name`, `file`, `page_id`, `show_in_navigation`, `parent`
						if(in_array($thispage[0], $page_rights_data)) {
							if(!$thispage[4]) {
								$linkname = 'page';
								if(!$thispage[2]) {
									$linkname = 'show_items';
								}
								if(!$thispage[3]) {
									$thispage[3] = "<i>Diese Seite wurde noch nicht beschrieben.</i>";
								}
								print "<li style='margin: 10px 0;'>&raquo;<b><a href='admin.php?$linkname=$thispage[0]'>".$thispage[1]."</a></b>&laquo; &mdash; ".$thispage[3];
								$subpagedata = create_page_info_parent($thispage[0], $GLOBALS['user_role_id']);
								if(count($subpagedata)) {
									print "<ul>\n";
									foreach ($subpagedata as $thissubpage){
										if(!$thissubpage[3]) {
											$thissubpage[3] = '<i>Diese Unterseite wurde noch nicht beschrieben.</i>';
										}
										print "<li style='margin: 3px 0;'>&raquo;<b><a href='admin.php?page=$thissubpage[0]'>".$thissubpage[1]."</a></b>&laquo; &mdash; ".$thissubpage[3]."</li>\n";
									}
									print "</ul>\n";
								}
								print "</li>\n";
							}
						}
					}
?>
				</ul>
			</p>
		</div>
<?php
	}
?>
