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

	if (!isset($_GET['group'])) {
		$_GET['group'] = 'none';
	}

	$overdue_contacts = get_overdue_contacts();
	$table_content = array( '', //number
							'Data, typ',
							'Organizacja',
							'Osoba kontaktuj±ca',
							'Projekt',
							'Wcze¶niejszy kontakt'
						  );

	$group_types = array('none' => 'Nie grupuj', 'users' => 'U¿ytkownikami', 'projects' => 'Projektami');

	$html_group = array();

	foreach ($group_types as $key => $label) {
		if ($key == $_GET['group']) {
			$html_group[] = $label;
		} else {
			$html_group[] = '<a href="?group='.$key.'" class="menu">'.$label.'</a>';
		}
	}

	echo '<table width="90%">'."\n".
		 '<tr><td align="center" class="naglowek">Zaleg³e kontakty<hr></td></tr>'."\n".
		 '<tr><td class="naglowek_maly" align="center">Grupuj: '.join(' | ', $html_group).'<hr></td></tr>'."\n";

    if ($overdue_contacts === false) {
		echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>';
	} else if (empty($overdue_contacts)) {
		echo '<tr><td>Brak kontaktów.</td></tr>';
	} else {
		if ($_GET['group'] == 'none') {
			echo '<tr><td><b>Wszystkich zaleg³ych kontaktów: '.count($overdue_contacts)."</b><br><br>\n";
			
			$num_contact = 1;
			echo '<table cellspacing="0" cellpadding="2" border="1"><tr><th>'.
				 join('</th><th>', $table_content)."</th></tr>\n";
		
			foreach ($overdue_contacts as $cid) {
				echo '<tr><td align="center">'.$num_contact.'. </td>';
				++$num_contact;
			
				$details = get_contact_details($cid);
			
				echo '<td align="center"><table><tr><td align="center" nowrap>'.$details['next_contact_date'].'</td>';
		
				echo '<td><img src="../img/icon_'.($details['next_contact_type'] == 'telefon' ? 'phone' : 'meeting').
				'.gif" border="0" height="17" width="22"></td></tr></table></td>';
			
				echo '<td align="center">';
				display_link_to_org($details['organisation_id']);
				echo '</td><td align="center">';
				display_link_to_user($details['user_id']);
				echo '</td><td align="center" nowrap>';
				display_link_to_project($details['project_id']);
				echo '</td><td align="center" nowrap>';		
				display_link_to_contact($cid);
				echo '</td></tr>';
			}

			echo "</table>\n</td></tr>";
		} else if ($_GET['group'] == 'users') {
			$users_overdue_contacts = array();

			foreach ($overdue_contacts as $cid) {
				$details = get_contact_details($cid);

				$users_overdue_contacts[$details['user_id']][] = array('contact_id'=>$cid) + $details;
			}
			
			echo '<tr><td><table align="center" cellpadding="2" cellspacing="0">'.
				 '<tr><th></th> <th>U¿ytkownik</th><th>Zaleg³ych kontaktów</th></tr>';

			$num_users = 1;
			foreach ($users_overdue_contacts as $user_id => $details) {
				$show_user = $_GET['show'] == $user_id;

				echo '<tr><td align="center">'.$num_users.'</td><td align="center">';
				$num_users++;
				display_link_to_user($user_id);
				echo '</td><td align="center">'.count($details).' [';
				
				if ($show_user) {
					echo '<a href="?group=users" class="menu">Ukryj</a>';
				} else {
					echo '<a href="?group=users&show='.$user_id.'" class="menu">Poka¿</a>';
				}
					
				echo "]</td></tr>\n";

				if ($show_user) {
					echo '<tr><td colspan="3"><table bgcolor="#fffff8" border="1" cellpadding="2" cellspacing="0">'."\n";

					foreach ($details as $detail) {
						echo '<td align="center"><table><tr><td align="center" nowrap>'.$detail['next_contact_date'].'</td>';

						echo '<td><img src="../img/icon_'.($detail['next_contact_type'] == 'telefon' ? 'phone' : 'meeting').
							 '.gif" border="0" height="17" width="22"></td></tr></table></td>';
			
						echo '<td>';
						display_link_to_org($detail['organisation_id']);
						echo '</td><td align="center" nowrap>';
						display_link_to_project($detail['project_id']);
						echo '</td><td align="center" nowrap>';		
						display_link_to_contact($detail['contact_id']);
						echo "</td></tr>\n";
					}

					echo "</table></td></tr>\n";
				}
			}

			echo '</table></td></tr>';
		} else if ($_GET['group'] == 'projects') {
			$projects_overdue_contacts = array();

			foreach ($overdue_contacts as $cid) {
				$details = get_contact_details($cid);

				$projects_overdue_contacts[$details['project_id']][] = array('contact_id'=>$cid) + $details;
			}
			
			echo '<tr><td><table align="center" cellpadding="2" cellspacing="0">'.
				 '<tr><th></th> <th>Projekt</th><th>Zaleg³ych kontaktów</th></tr>';

			$num_projects = 1;
			foreach ($projects_overdue_contacts as $project_id => $details) {
				$show_project = $_GET['show'] == $project_id;

				echo '<tr><td align="center">'.$num_projects.'</td><td align="center">';
				$num_projects++;
				display_link_to_project($project_id);
				echo '</td><td align="center">'.count($details).' [';
				
				if ($show_project) {
					echo '<a href="?group=projects" class="menu">Ukryj</a>';
				} else {
					echo '<a href="?group=projects&show='.$project_id.'" class="menu">Poka¿</a>';
				}
					
				echo "]</td></tr>\n";

				if ($show_project) {
					echo '<tr><td colspan="3"><table bgcolor="#fffff8" border="1" cellpadding="2" cellspacing="0">'."\n";

					foreach ($details as $detail) {
						echo '<td align="center"><table><tr><td align="center" nowrap>'.$detail['next_contact_date'].'</td>';

						echo '<td><img src="../img/icon_'.($detail['next_contact_type'] == 'telefon' ? 'phone' : 'meeting').
							 '.gif" border="0" height="17" width="22"></td></tr></table></td>';
			
						echo '<td>';
						display_link_to_user($detail['user_id']);
						echo '</td><td align="center">';
						display_link_to_org($detail['organisation_id']);
						echo '</td><td align="center" nowrap>';		
						display_link_to_contact($detail['contact_id']);
						echo "</td></tr>\n";
					}

					echo "</table></td></tr>\n";
				}
			}

			echo '</table></td></tr>';
		}
	}	
	
	echo "</table>\n";
	display_document_footer();
?>
