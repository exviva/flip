<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	if (!is_admin()) {
		display_warning('Musisz mieæ uprawnienia administratora!');
		exit;
	}

	$readable_start = make_date($_POST['start']);
	$readable_end = make_date($_POST['end']);

	if (!my_check_date($_POST['start']) || !my_check_date($_POST['end'])) {
		display_warning('Z³a data!');
		exit;
	}

	$stats = get_project_stats($_POST['projects'], $readable_start, $readable_end);

	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Statystyki projektów od '.$readable_start.' do '.$readable_end.'<hr></td></tr>';
	echo '<tr><td align="center"><table cellpadding="5" cellspacing="0" border="1" bgcolor="#fefffe"><tr>';

	foreach (array('Projekt', 'Telefonów', 'Spotkañ', 'Ogó³em') as $label) {
		echo '<th>'.$label.'</th>';
	}

	echo '</tr>';

	$suma_telefonow = 0;
	$suma_spotkan = 0;

	foreach ($stats as $project_id => $details) {
		$telefon = (int) $details['telefon'];
		$spotkanie = (int) $details['spotkanie'];
		echo '<tr><td>';
		display_link_to_project($project_id);
		echo '</td><td>'.join('</td><td>', array($telefon, $spotkanie, $telefon+$spotkanie)).'</td></tr>';

		$suma_telefonow += $telefon;
		$suma_spotkan += $spotkanie;
	}
	
	echo '<tr><td>'.join('</td><td>', array('SUMA:', $suma_telefonow, $suma_spotkan, $suma_telefonow+$suma_spotkan)).'</td></tr>';
	echo '</table></td></tr></table>'."\n";
	display_document_footer();

function make_date($date_array) {
	return $date_array['year'].'-'.$date_array['mon'].'-'.$date_array['mday'];
}

function my_check_date($date_array) {
	return checkdate($date_array['mon'], $date_array['mday'], $date_array['year']);
}
?>
