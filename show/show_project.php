<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%"'.">\n";
	
	if (!isset($_GET['project_id'])) {
		echo '<tr><td colspan="2" align="center" class="naglowek">Wybierz projekt<hr></td></tr>'."\n";	

		$active_projects = get_active_projects();

		echo '<tr><td width="50%">';
		show_select_project_form($active_projects, 'Projekty aktywne');
		echo '</td><td width=50%">';

		$inactive_projects = array_diff(get_projects(), $active_projects);

		show_select_project_form($inactive_projects, 'Projekty nieaktywne');
		echo '</td></tr></table>'."\n";
		
		display_document_footer();
		exit;
	}

	$show_orgs = isset($_GET['show_orgs']) ? $_GET['show_orgs'] : 0;
	$show_contacts = isset($_GET['show_contacts']) ? $_GET['show_contacts'] : 0;
	
	echo '<tr><td align="center" class="naglowek">Dane projektu <i>'.
		 htmlspecialchars(get_project_name($_GET['project_id'])).'</i>';
		 
	if (is_admin()) {
		echo '&nbsp;&nbsp;&nbsp;[<a href="../admin/edit_project_form.php?project_id='.$_GET['project_id'].
			 '" class="menu">Edytuj</a>]';
	}

	echo "<hr></td></tr>\n";
?>
<tr><td align="center">
	<table border="1" cellpadding="4" cellspacing="0" bgcolor="#eeeeee">
	<tr>
		<th rowspan="3" align="center" valign="center">U¿ytkownik</th>
		<th colspan="3" align="center">Organizacje</th>
		<th colspan="3" align="center">Kontakty</th>
	</tr>
	<tr>
		<th rowspan="2" align="center">Przyznane</th>
		<th colspan="2" align="center">Skontaktowane?</th>
		<th rowspan="2" align="center">Telefony</th>
		<th rowspan="2" align="center">Spotkania</th>
		<th rowspan="2" align="center">W sumie</th>
	</tr>
	<tr>
		<th align="center">TAK</th>
		<th align="center">NIE</th>
	</tr>
<?php
	$involved = array_keys(get_project_involved($_GET['project_id']));

	$suma = array('all' => 0, 'contacted' => 0, 'telefon' => 0, 'spotkanie' => 0);
	
	foreach ($involved as $inv_id) {
		$stats = get_user_stats($inv_id, $_GET['project_id']);
		foreach ($stats as $key => $value) {
			$suma[$key] += $value;
		}
		
		echo '<tr><td align="center" nowrap>';
		display_link_to_user($inv_id);

		if (is_ocp($inv_id, $_GET['project_id'])) {
			echo ' (OCP)';
		}
		
		echo '</td><td align="center">'.join('</td><td align="center">', array(
																				$stats['all'], 
																				$stats['contacted'], 
																				$stats['all']-$stats['contacted'],
																				$stats['telefon'],
																				$stats['spotkanie'],
																				$stats['telefon']+$stats['spotkanie']
																				)
							 )."</td></tr>\n";
	}

	echo '<tr><td align="center"><b>W SUMIE</b></td><td align="center"><b>'.
		 join('</b></td><td align="center"><b>', array(
		 										   $suma['all'],
												   $suma['contacted'],
												   $suma['all']-$suma['contacted'],
												   $suma['telefon'],
												   $suma['spotkanie'],
												   $suma['telefon']+$suma['spotkanie']
												   )
			 )."</td></tr>\n";
?>
</table>
<br><br></td></tr>
<?php
	echo '<tr><td class="naglowek_maly">Kontaktowane organizacje:</td></tr>';
	echo '<tr><td>';

	if ($show_orgs == 1) {
		echo '[<a href="'.$_SERVER['PHP_SELF'].'?project_id='.$_GET['project_id'].
			 '&show_orgs=0&show_contacts='.$show_contacts.'" class="menu">Ukryj</a>]</td></tr>';

		echo '<tr><td>';
		
		$project_orgs = get_project_orgs($_GET['project_id']);
		if (empty($project_orgs)) {
			echo '-';
		} else {
			$num_org = 1;
			echo "<table><tr><th></th><th>Organizacja</th><th>Przydzia³</th></tr>\n";
			
			foreach($project_orgs as $org_id => $oc_resp_id) {
				echo '<tr><td>'.$num_org.'.</td><td>';
				++$num_org;
				display_link_to_org($org_id);
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
	} else {
		echo '[<a href="'.$_SERVER['PHP_SELF'].'?project_id='.$_GET['project_id'].
			 '&show_orgs=1&show_contacts='.$show_contacts.'" class="menu">Poka¿</a>]';
	}
	
	echo "<br><br></td></tr>\n";

	echo '<tr><td class="naglowek_maly">Kontakty:</td></tr>';
	echo '<tr><td>';

	if ($show_contacts == 1) {
		echo '[<a href="'.$_SERVER['PHP_SELF'].'?project_id='.$_GET['project_id'].
			 '&show_orgs='.$show_orgs.'&show_contacts=0" class="menu">Ukryj</a>]</td></tr>';

		echo '<tr><td>';
		
		$contacts = get_contacts('project_id', $_GET['project_id']);
		if ($contacts === false) {
			echo 'B³±d bazy danych, spróbuj pó¼niej.';
		} else if (empty($contacts)) {
			echo '-';
		} else {
			echo '<table>';
			echo '<tr><th></th><th>Data, typ</th><th>Organizacja</th><th>Osoba kontaktuj±ca</th></tr>';
			$num_contact = 1;
		
			foreach ($contacts as $cid) {
				echo '<tr><td>'.$num_contact.'. </td><td>';
				++$num_contact;
				display_link_to_contact($cid);
				$details = get_contact_details($cid);
				echo '</td><td>';
				display_link_to_org($details['organisation_id']);
				echo '</td><td>';
				display_link_to_user($details['user_id']);
				echo '</td></tr>'."\n";
			}
			
			echo "</table>\n";
		}
	} else {
		echo '[<a href="'.$_SERVER['PHP_SELF'].'?project_id='.$_GET['project_id'].
			 '&show_orgs='.$show_orgs.'&show_contacts=1" class="menu">Poka¿</a>]';
	}
		
	echo "</td></tr></table>\n";
	display_document_footer();

function show_select_project_form($projects, $label) {
	echo '<table><tr><th>'.$label.':</th></tr>';

	if ($projects === false) {
		echo 'B³±d bazy danych, spróbuj pó¼niej.</table>';
		display_document_footer();
		exit;
	}
	
	echo '<tr><td><form action="'.$_SERVER['PHP_SELF'].'" method="get"><select name="project_id">';

	foreach ($projects as $pid => $name) {
		echo '<option value="'.$pid.'">'.htmlspecialchars($name)."</option>\n";
	}

	echo '</select></td></tr>';
	echo '<tr><td><input type="submit" value="Wybierz"></form></td></tr></table>'."\n";
}
?>
