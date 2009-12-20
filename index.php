<?php
	session_start();
	require_once('lib/flip.php');
	check_valid_user();

	display_html_header();
	display_document_header();
	display_menu(true);

	if (!isset($_GET['show_closed'])) {
		$_GET['show_closed'] = 0;
	}

	echo '<table width="90%" valign="top">'."\n".
		 '<tr><td align="center" class="naglowek">Moje projekty<hr></td></tr>'."\n";
	
	$my_projects = get_my_projects($_SESSION['valid_user_id']);

	echo '<tr><td>';
	
	if ($my_projects === false) {
		echo 'B³±d bazy danych, spróbuj pó¼niej.';
	} else if (empty($my_projects)) {
		echo 'Brak projektów.';
	} else {
		foreach ($my_projects as $project_id) {
			if (project_is_closed($project_id)) {
				display_link_to_project($project_id, 0);
				echo ' (projekt zamkniêty)<br>';
				continue;
			}

			$orgs = get_my_project_orgs($_SESSION['valid_user_id'], $project_id);

			$filter_orgs = array();

			if ($_GET['show_closed'] == 1) {
				$filter_orgs = $orgs;
			} else {
				foreach ($orgs as $org_id) {
					if (org_is_open($org_id, $_SESSION['valid_user_id'], $project_id)) {
						$filter_orgs[] = $org_id;
					}
				}
			}

			echo '<table cellspacing="0" cellpadding="0">';
			echo '<tr><td><table align="center"><tr><td class="naglowek_maly">';
			display_link_to_project($project_id);
			echo '</td>';

			if (is_ocp($_SESSION['valid_user_id'], $project_id)) {
				if (!project_has_oc($project_id)) {
					echo '<td>';
					display_exclamation('Nie masz jeszcze cz³onków OC');
					echo '</td>';
				}
				
				$null_resp = project_has_null_resp($project_id);
				echo '<td>[<a href="ocp/edit_oc_form.php?project_id='.
					 $project_id.'" class="menu">'.'Edytuj OC</a>]</td><td>[<a href="ocp/dispense_orgs_form.php?project_id='.
					 $project_id.'&show='.($null_resp ? 'new' : 'all').'" class="menu">Rozdziel organizacje</a>]</td>';
					 
				if ($null_resp) {
					echo '<td>';
					display_exclamation('Masz nowe organizacje');
					echo '</td>';
				}
			}

			echo "</tr></table></td></tr>\n";
			echo "<tr><td><table>\n";
	
			if (empty($filter_orgs)) {
				echo '<tr><td>Brak organizacji.</td></tr>'."\n";
			} else {
				echo '<tr><td colspan="4" align="right" class="naglowek_maly">[Dodaj kontakt]</td></tr>'."\n";
				
				$num_org = 1;

				foreach ($filter_orgs as $org) {
					echo '<tr><td>'.$num_org.'. </td><td width="500">';
					++$num_org;

					if (empty_org($org)) {
						echo '<table><tr><td>';
						display_link_to_search_org($org);
						echo '</td><td>';
						display_link_to_org($org, true, $project_id);
						echo "</td></tr></table>\n";
					} else {
						display_link_to_org($org, true, $project_id);
					}

					echo '</td><td>';	
					display_add_contact($org, $project_id, 'telefon');
					echo '</td><td>';
					display_add_contact($org, $project_id, 'spotkanie');
					echo "</td></tr>\n";
				}
			}

			echo "</table>\n";
			echo '</td></tr></table>';
		}
	}

	echo "</td></tr>\n".
		 '<tr><td class="naglowek_maly">[<a class="menu" href="?show_closed='.($_GET['show_closed'] == 1 ? 0:1);
		 
	if (isset($_GET['show_history'])) {
		echo '&show_history='.$_GET['show_history'];
	}
	
	echo '" title="'.
		 "Ta opcja pozwala ukryæ/pokazaæ organizacje, z którymi kontakt ju¿ siê zakoñczy³ (ustawiono 'Kolejny kontakt' na 'brak')".
		 '">';
	
	if ($_GET['show_closed'] == 1) {
		echo 'Ukryj skontaktowane';
	} else {
		echo 'Poka¿ wszystkie';
	}
	
	echo '</a>]</td></tr></table>'."\n";
	
	
	$pc = get_planned_contacts($_SESSION['valid_user_id']);
	$today = date('Y-m-d');
	
	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Moje zaplanowane kontakty</td></tr>'."\n";
	echo '<tr><td align="center" class="naglowek_maly">Dzisiaj jest '.$today."<hr></td></tr>\n";
	echo '<tr><td>';

	if ($pc === false) {
		echo 'B³±d bazy danych, spróbuj pó¼niej.';
	} else if (empty($pc)) {
		echo 'Brak kontaktów.';
	} else {
		echo '<table bgcolor="#dbb188"><tr>';
		
		foreach (array(	'', 'Data, typ', 'Organizacja', 
						'Projekt', 'Wcze¶niejszy kontakt') as $th) {
			echo '<th>'.$th.'</th>';
		}
		
		echo "</tr>\n";
		$num_contact = 1;
	
		foreach ($pc as $cid) {
			echo '<tr bgcolor="#f8f8ea"><td>'.$num_contact.'. </td>';
			++$num_contact;
			$details = get_contact_details($cid);
			$contact_is_urgent = $details['next_contact_date'] <= $today;
			
			echo '<td align="center"><table cellspacing="0"><tr><td nowrap>';
			
			if ($contact_is_urgent) {
				echo '<a href="add/add_contact_form.php?org_id='.$details['organisation_id'].
					 '&project_id='.$details['project_id'].'&type='.$details['next_contact_type'].
					 '" class="menu" title="Dodaj kontakt teraz"><b><font color="#FF0000">';
			}

			echo $details['next_contact_date'];

			if ($contact_is_urgent) {
				echo '</font></b></a>';
			}
			
			echo '</td><td align="right"><img src="img/icon_'.
				 ($details['next_contact_type'] == 'telefon' ? 'phone' : 'meeting').
				 '.gif" border="0" height="17" width="22"></td></tr></table></td><td>';
				 
			display_link_to_org($details['organisation_id']);
			echo '</td><td nowrap>';
			display_link_to_project($details['project_id']);
			echo '</td><td nowrap align="center">';
			display_link_to_contact($cid);
			echo '</td></tr>';
		}
		
		echo '</table>';
	}

	echo '</td></tr></table>';



	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Moja historia kontaktów [<a class="menu" href="?show_closed='.$_GET['show_closed'];

	if ($_GET['show_history']==1) {
		echo '">Ukryj';
	} else {
		echo '&show_history=1">Poka¿';
	}

	echo "</a>]<hr></td></tr>\n";

	if ($_GET['show_history']==1) {
		echo '<tr><td>';
		display_contact_history(); //defined in this file at the end
		echo "</td></tr>\n";
	}

	echo '</table>';
	
	//display today's contacts and orgs updated today
	if (is_admin()) {
		$today_contacts = get_contacts('date', 'curdate()');

		echo '<table width="90%">'."\n";
		echo '<tr><td class="naglowek" align="center">Dzisiejsze kontakty<hr></td></tr>'."\n";
	
		if ($today_contacts === false) {
			echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>';
		} else if (empty($today_contacts)) {
			echo '<tr><td>Brak kontaktów.</td></tr>';
		} else {
			echo '<tr><td><table>';
			echo '<tr><th></th><th>Typ</th><th>Organizacja</th><th>Osoba kontaktuj±ca</th>';
			echo '<th>Projekt</th></tr>';
			$num_contact = 1;
			
			foreach ($today_contacts as $cid) {
				$details = get_contact_details($cid);
				echo '<tr><td>'.$num_contact.'. ';
				++$num_contact;
				echo '</td><td>';
				echo '<a href="show/show_contact.php?cid='.$cid.
					 '" title="'.htmlspecialchars($details['comments']).
					 '"><img src="img/icon_'.($details['type'] == 'telefon' ? 'phone' : 'meeting').
					 '.gif" width="22" height="17" border="0"></a>';
				echo '</td><td>';
				display_link_to_org($details['organisation_id']);
				echo '</td><td>';
				display_link_to_user($details['user_id']);
				echo '</td><td nowrap>';
				display_link_to_project($details['project_id']);
				echo '</td></tr>';
			}

			echo '</table></td></tr>';
		}

		echo "</table>\n";

		$today_updated_orgs = get_today_updated_orgs();

		echo '<table width="90%">'."\n";
		echo '<tr><td class="naglowek" align="center">Dzisiaj aktualizowane organizacje<hr></td></tr>'."\n";

		if ($today_updated_orgs === false) {
			echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>';
		} else if (empty($today_updated_orgs)) {
			echo '<tr><td>Brak organizacji.</td></tr>';
		} else {
			echo '<tr><td align="left"><table>'."\n";
			echo '<tr><th></th><th>Organizacja</th><th>Osoba aktualizuj±ca</th></tr>'."\n";
			$num_added = 1;
			
			foreach ($today_updated_orgs as $added_info) {
				echo '<tr><td>'.$num_added.'. </td>';
				++$num_added;
				echo '<td>';
				display_link_to_org($added_info['organisation_id']);
				echo '</td><td>';
				display_link_to_user($added_info['updater_id']);
				echo "</td></tr>\n";
			}

			echo "</table></td></tr>\n";
		}

		echo "</table>\n";
	}

	display_document_footer();





/**************************************************
				WRAPPING FUNCTIONS
**************************************************/

function display_contact_history() {
	$contacts = get_contacts('user_id', $_SESSION['valid_user_id']);
	
	if ($contacts === false) {
		echo 'B³±d bazy danych, spróbuj pó¼niej.';
	} else if (empty($contacts)) {
		echo 'Brak kontaktów.';
	} else {
		echo '<table><tr><th></th><th>Data, typ</th><th>Organizacja</th><th>Projekt</th></tr>';
		$num_contact = 1;
	
		foreach ($contacts as $cid) {
			echo '<tr><td>'.$num_contact.'. </td><td>';
			++$num_contact;
			display_link_to_contact($cid);
			$details = get_contact_details($cid);
			echo '</td><td>';
			display_link_to_org($details['organisation_id']);
			echo '</td><td nowrap>';
			display_link_to_project($details['project_id']);
			echo '</td></tr>';
		}
		
		echo '</table>';
	}
}
	
?>
