<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	if (!isset($_GET['cid'])) {
		display_warning('Wybierz kontakt!');
		exit;
	}

	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%">'."\n";
	
	$details = get_contact_details($_GET['cid']);

	if ($details === false) {
		echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>'."\n";
	} else if (empty($details)) {
		echo '<tr><td>Brak kontaktu.</td></tr>';
	} else {
		echo '<tr><td align="center" class="naglowek">Szczegó³y kontaktu';
		
		if ($details['user_id']==$_SESSION['valid_user_id'] || is_admin()) {
			echo ' [<a href="../add/add_contact_form.php?cid='.$_GET['cid'].'" class="menu">Edytuj</a>]';
		}

		echo '<hr></td></tr>'."\n";
		echo '<tr><td align="center"><table><tr><td width="50%" align="right">';
		
		$prev_cid = get_other_contact($_GET['cid'], '<');
		
		if ($prev_cid) {
			display_link_to_contact($prev_cid, '<< Poprzedni', false, '[', ']', false);
		} else {
			echo '&nbsp;';
		}
		
		echo '</td><td width="50%" align="left">';
	
		$next_cid = get_other_contact($_GET['cid'], '>');
		if ($next_cid) {
			display_link_to_contact($next_cid, 'Nastêpny >>', false, '[', ']', false);
		} else {
			echo '&nbsp;';
		}
		
		echo "</td></tr></table></td></tr>\n";

		echo '<tr><td>';
	
		echo '<table align="center">'."\n";

		$html_label = array ('type' => 'Typ',
							 'date' => 'Data',
							 'contact_person' => 'Osoba kontaktowana',
							 'contact_function' => 'Stanowisko osoby kontaktowanej',
							 'aim' => 'Cel',
							 'comments' => 'Opis',
							 'organisation_id' => 'Nazwa organizacji',
							 'user_id' => 'Osoba kontaktuj±ca',
							 'project_id' => 'Projekt',
							 'next_contact_type' => 'Kolejny kontakt',
							 'next_contact_date' => 'Data kolejnego kontaktu'
							 );

		foreach ($details as $label => $value) {
			echo '<tr><td width="50%" align="right" valign="top"><b>'.htmlspecialchars($html_label[$label]).
				 ':</b></td><td width="50%" align="left">';
			
			switch($label) {
				case 'organisation_id':
					display_link_to_org($value);
				break;
				case 'user_id':
					display_link_to_user($value);
				break;
				case 'project_id':
					display_link_to_project($value);
				break;
				case 'comments':
					echo nl2br(htmlspecialchars(stripslashes($value)));
				break;
				case 'next_contact_type':
					echo (empty($value) ? 'brak' : $value);
				break;
				case 'next_contact_date':
					echo (empty($value) ? '-' : $value);
				break;
				default:
					echo htmlspecialchars(stripslashes($value));
				break;
			}
					
			echo "</td></tr>\n";
		}
		
		echo "</table>\n";
	}
	
	echo "</table>\n";

	display_document_footer();
?>
