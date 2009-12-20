<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	if (!isset($_GET['org_id'])) {
		display_warning('Wybierz organizacjê!');
		exit;
	}

	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Dane organizacji <i>'.
		 htmlspecialchars(stripslashes(get_org_name($_GET['org_id']))).'</i>';
	
	if (is_responsible($_SESSION['valid_user_id'], $_GET['org_id']) || is_admin()) {
		echo '&nbsp;&nbsp;&nbsp;[<a href="../add/add_org_info_form.php?org_id='.
			 $_GET['org_id'].'" class="menu">Edytuj</a>]';
	}
	
	echo "<hr></td></tr>\n";

	$org_info = get_org_info($_GET['org_id']);
	
	echo '<tr><td>';
	if (!$org_info) {
		echo 'Brak danych o organizacji.</td></tr>';
	} else {
		echo '<table align="center">';

		$content = array('Adres' => htmlspecialchars($org_info['city'].', '.$org_info['street']),
						 'Telefon' => htmlspecialchars(parse_phone_number($org_info['phone'])),
						 'Fax' => htmlspecialchars(parse_phone_number($org_info['fax'])),
						 'WWW' => (strpos($org_info['www'], '.') === false ? '-': 
						 		  '<a href="'.htmlspecialchars($org_info['www']).
								  '" target="_blank" class="menu">'.
								  htmlspecialchars($org_info['www']).'</a>'),
						 'Profil dzia³alno¶ci' => htmlspecialchars($org_info['profile']),
						 'Data ostatniej aktualizacji' => $org_info['date']
						 );

		$contact_person = get_contact_person($_GET['org_id']);

		if ($contact_person !== false && !empty($contact_person)) {
			$content['Osoba kontaktowa'] = htmlspecialchars($contact_person);
		}

		foreach ($content as $label => $value) {
			echo '<tr><td width="50%" align="right"><b>'.$label.':</b></td><td width="50%" align="left">';
			echo $value;
			echo "</td></tr>\n";
		}

		echo '<tr><td width="50%" align="right"><b>Osoba aktualizuj±ca dane:</b></td><td>';
		display_link_to_user($org_info['updater_id']);
		echo "</td></tr>\n";

		echo "</table></td></tr>\n";
	}
	
	echo "</table>\n<br><br><br><br>";
	
	$contacts = get_contacts('organisation_id', $_GET['org_id']);
	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Historia kontaktów<hr></td></tr>'."\n";

	if ($contacts === false) {
		echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.';
	} else if (empty($contacts)) {
		echo '<tr><td>Brak kontaktów.</td></tr>'."\n";
	} else {
		echo '<tr><td align="center"><table>';
		echo '<tr><th></th><th>Data, typ</th><th>Osoba kontaktuj±ca</th><th>Projekt</th></tr>';
		$num_contact = 1;
		
		foreach ($contacts as $cid) {
			echo '<tr><td>'.$num_contact.'. </td><td>';
			++$num_contact;
			display_link_to_contact($cid);
			$details = get_contact_details($cid);
			echo '</td><td>';
			display_link_to_user($details['user_id']);
			echo '</td><td nowrap>';
			display_link_to_project($details['project_id']);
			echo "</td></tr>\n";
		}

		echo '</table></td></tr>';
	}
	
	echo "</table>\n<br><br><br><br>";

	$projects = get_org_projects($_GET['org_id']);
	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Projekty kontaktuj±ce organizacjê<hr></td></tr>'."\n";

	echo '<tr><td align="center">';
	if ($projects === false) {
		echo 'B³±d bazy danych, spróbuj pó¼niej.';
	} else if (empty($projects)) {
		echo "Brak projektów.";
	} else {
		echo "<table><th></th><th>Projekt</th><th>Przydzia³</th></tr>\n";
		$num_project = 1;
		
		foreach ($projects as $pid => $oc_resp_id) {
			echo '<tr><td>'.$num_project.'. </td><td>';
			++$num_project;
			display_link_to_project($pid);
			echo '</td><td>';

			if ($oc_resp_id === null) {
				display_exclamation('Nowa organizacja');
				echo ' Nowa';
			} else if ($oc_resp_id == 0) {
				echo 'Nieprzydzielona';
			} else {
				display_link_to_user($oc_resp_id);
			}
			
			echo "</td></tr>\n";
		}

		echo "</table>\n";
	}

	if (is_admin()) {
		echo add_org_to_project_form(array_keys($projects)); // defined in this file, below.
	}

	echo "</td></tr></table>\n<br><br><br><br>";

	display_document_footer();

function add_org_to_project_form($existing_projects) {
	echo "\n<br><br><br><br>";

	echo '<table width="100%">'."\n";
	echo '<tr><td align="center" class="naglowek">Administrator<hr width="90%"></td></tr>'."\n";
	echo '<form action="../admin/add_org_to_project.php" method="post">'."\n";
	echo '<input type="hidden" name="organisation_id" value="'.$_GET['org_id'].'">';
	echo '<tr><td><table><tr><td>Dodaj organizacjê do projektu:</td><td><select name="project_id">';

	$projects = array_flip(get_active_projects());
	$projects = array_diff($projects, $existing_projects);
	$projects = array_flip($projects);
	
	foreach ($projects as $project_id => $name) {
		echo '<option value="'.$project_id.'">'.htmlspecialchars($name).'</option>'."\n";
	}

	echo '</select></td>';
	echo '<td><input type="submit" value="Dodaj"></form></td></tr></table>';
	
	echo '</td></tr></table>'."\n";
}
?>
