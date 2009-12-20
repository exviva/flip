<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();
	
	display_html_header();
	display_document_header();
	display_menu();
	
	if (!is_admin()) {
		display_no_auth();
		display_document_footer();
		exit;
	}

	echo '<table width="90%">';
	echo '<tr><td align="center" class="naglowek">Poka¿ statystyki projektów<hr></td></tr>'."\n";

	echo '<form action="show_project_stats.php" method="POST"><tr><td><table>'.
		 '<tr><td class="naglowek_maly">Wybierz projekty:</td></tr>';

	$active_projects = get_active_projects();

	foreach ($active_projects as $project_id => $name) {
		echo '<tr><td><input type="checkbox" name="projects[]" value="'.
			 $project_id.'" checked>';
			 
		display_link_to_project($project_id);
		
		echo '</td></tr>';
	}

	$default = array();
	$default['start'] = getdate(strtotime('-7 days'));
	$default['end'] = getdate(strtotime('now'));

	foreach (array('Data pocz±tku' => 'start', 'Data koñca' => 'end') as $label => $which_date) {
		$input_string = array();

		foreach (array('year', 'mon', 'mday') as $date) {
			$input_string[] = '<input type="text" size="10" name="'.
							  $which_date.'['.$date.']" value="'.$default[$which_date][$date].'" maxlength="4">';
		}
		
		echo '<tr><td class="naglowek_maly">'.$label.' (rrrr-mm-dd):</td></tr>';
		echo '<tr><td>'.join(' -', $input_string).'</td></tr>';
	}

	echo '<tr><td><input type="submit" value="Poka¿"></form></td></tr></table>';

	echo '</td></tr></table>'."\n";
	display_document_footer();
?>
