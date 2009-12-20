<?php
// add_contact_form.php shows the form for adding a new contact.

    require_once('../lib/flip.php');
    session_start();
    check_valid_user();

	// if $_GET['cid'], then the form is supposed to edit the data of the contact with contact_id $_GET['cid'].
	if (!isset($_GET['cid'])) {
		$edit = false; // 'insert a new contact' mode, set flag
		
		// input data validation...
		if (!isset($_GET['org_id'])) {
			display_warning('Musisz wybraæ organizacjê!');
			exit;
		}

		if (!isset($_GET['project_id'])) {
			display_warning('Musisz wybraæ projekt!');
			exit;
		}

		if (!isset($_GET['type']) || ($_GET['type'] != 'telefon' && $_GET['type'] != 'spotkanie')) {
			display_warning('Wybierz poprawnie typ kontaktu!');
			exit;
		}
	
		// cannot add contacts to this organisation
		if (!is_responsible($_SESSION['valid_user_id'], $_GET['org_id']) && !is_admin()) {
			display_warning('Nie mo¿esz dodawaæ zdarzeñ dla tej organizacji!');
			exit;
		}

		// if organisation data is not set, redirect to add_org_info_form.php
		if (empty_org($_GET['org_id'])) {
			header('location: add_org_info_form.php?org_id='.$_GET['org_id'].'&project_id='.$_GET['project_id']);
		}

		// the $data array holds all the information about the contact
		$data['organisation_id'] = $_GET['org_id'];
		
		$data['project_id'] = $_GET['project_id'];
		
		$today = getdate();
		// default date of contact is today
		$data['year'] = $today['year'];
		$data['month'] = $today['mon'];
		$data['day'] = $today['mday'];
		
		$data['type'] = $_GET['type'];
		
		// for easier use, last contact person is copied
		$org_contacts = get_contacts('organisation_id', $data['organisation_id']);
		$last_contact = get_contact_details($org_contacts[0]);
		
		$data['contact_person'] = $last_contact['contact_person'];
		$data['contact_function'] = $last_contact['contact_function'];
		// the default content of comments for meeting and phone call
		$data['comments'] = $_GET['type'] == 'spotkanie' ? "PRZEBIEG: \n\nATMOSFERA: \n\nREZULTAT: " : '';
		$data['aim'] = $last_contact['aim'];
		$data['nc_type'] = 'spotkanie';

		// next contact date (to days after today)
		$nc_date = getdate(strtotime('+2 days'));
		$data['nc_year'] = $nc_date['year'];
		$data['nc_month'] = $nc_date['mon'];
		$data['nc_day'] = $nc_date['mday'];
	} else { // "edit a contact" mode
		$edit = true; // set flag
		$data = get_contact_details($_GET['cid']);

		if (empty($data)) {
			display_warning('Brak kontaktu.');
			exit;
		}

		if ($_SESSION['valid_user_id']!=$data['user_id'] && !is_admin()) {
			display_warning('Nie jeste¶ uprawniony do edycji tego kontaktu!');
			exit;
		}

		$data['contact_id'] = $_GET['cid'];

		list($data['year'], $data['month'], $data['day']) = explode('-', $data['date']);
		unset($data['date']);
		
		$data['nc_type'] = (empty($data['next_contact_type']) ? 'brak' : $data['next_contact_type']);
		
		list($data['nc_year'], $data['nc_month'], $data['nc_day']) =
				(empty($data['next_contact_date']) ? array(0,0,0) : explode('-', $data['next_contact_date']));
		unset($data['next_contact_type']);
		unset($data['next_contact_date']);
	}
	
	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%">'."\n";

	echo '<tr><td align="center" class="naglowek">'.($edit ? 'Edycja kontaktu' : 'Nowy kontakt').': ';
	display_link_to_org($data['organisation_id']);
	echo "<hr></td></tr>\n";

	echo '<tr><td><table align="center">';
	echo '<form method="POST" action="add_contact.php">'."\n";
	
	if (!$edit) {
		echo '<input type="hidden" name="organisation_id" value="'.$data['organisation_id'].'">'."\n";
		echo '<input type="hidden" name="project_id" value="'.$data['project_id'].'">'."\n";
	} else {
		echo '<input type="hidden" name="contact_id" value="'.$data['contact_id'].'">'."\n";
	}

	echo '<tr><td align="right">Typ:</td><td align="left">';

	foreach (array('phone' => 'telefon', 'meeting' => 'spotkanie') as $avail_key => $avail_type) {
		echo '<input type="radio" name="type" value="'.$avail_type.'" id="'.$avail_key.'"';

		if ($avail_type == $data['type']) {
			echo ' checked';
		}

		echo '><label for="'.$avail_key.'">'.$avail_type."</label>\n";
	}

	echo "</td></tr>\n";

	$fields = array('Osoba kontaktowana'    			=> array('contact_person'   , 30),
	                'Stanowisko osoby kontaktowanej'    => array('contact_function' , 40) 
					);
	
	foreach ($fields as $label => $form) {
		echo '<tr><td align="right">'.htmlspecialchars($label).':</td>';
		echo '<td align="left"><input type="text" name="'.htmlspecialchars($form[0]).
			 '" value="'.htmlspecialchars($data[$form[0]]).
			 '" maxlength="'.$form[1].'"></td></tr>'."\n";
	}

	echo '<tr><td align="right">Cel kontaktu:</td><td align="left"><select name="aim_id">'."\n";
	
	$aims = get_aims();

	foreach ($aims as $aid => $aim) {
		echo '<option value="'.$aid.'"';
		
		if ($data['aim'] === $aim) {
			echo ' selected';
		}
		
		echo '>'.$aim.'</option>'."\n";
	}
		
	echo '</select></td></tr>';

	echo '<tr><td align="right">Data kontaktu:</td>';
	echo '<td align="left"><table><tr>';

	$min_year = 1990;
	$today = getdate();
	$ranges['year'] = range($min_year, $today['year']);
	$ranges['month'] = range(1, 12);
	$ranges['day'] = range(1, 31);

	$label = array('year' => 'Rok', 'month' => 'Miesi±c', 'day' => 'Dzieñ');
	
	foreach ($ranges as $period => $ran) {
		echo '<td><select name="'.$period.'">'."\n";
		
		foreach ($ran as $temp) {
			echo '<option';

			if ($temp == $data[$period]) {
				echo ' selected';
			}
			
			echo '>'.$temp."</option>\n";
		}

		echo '</select><font size="-2">'.$label[$period]."</font></td>\n";
	}

	echo "</tr></table>\n</td></tr>";

	echo '<tr><td align="right" valign="top">Opisz przebieg kontaktu:</td>';
	echo '<td align="left" valign="top"><textarea cols="50" rows="10" name="comments">'.htmlspecialchars($data['comments']).
		 '</textarea></td></tr>'."\n";

	echo '<tr><td align="right">Kolejny kontakt:</td><td align="left">'."\n";
	
	$types = array('brak', 'telefon', 'spotkanie');
	
	foreach ($types as $type) {
		echo '<input type="radio" name="nc_type" id="'.$type.'" value="'.$type.'"'.
			 ($type == $data['nc_type'] ? ' checked':'').'><label for="'.$type.'">'.$type."</label>\n";
	}
	
	echo '</td></tr>';

	echo '<tr><td align="right">Data kolejnego kontaktu:</td><td align="left"><table><tr>';

	$nc = array ('year' => 'nc_year', 'month' => 'nc_month', 'day' => 'nc_day');
	
	$ranges['year'][] = $today['year']+1;

	foreach ($ranges as $period => $ran) {
		echo '<td><select name="'.$nc[$period].'">'."\n";
		
		foreach ($ran as $temp) {
			echo '<option';
			
			if ($temp == $data[$nc[$period]]) {
				echo ' selected';
			}
			
			echo '>'.$temp."</option>\n";
		}

		echo '</select><font size="-2">'.$label[$period]."</font></td>\n";
	}
	
	echo "</tr></table>\n</td></tr>";
	echo "</table></td></tr>\n";
	echo '<tr><td align="center"><input type="submit" value="Gotowe"></td></tr>'."\n";
	echo '</form></table>'."\n";

	display_document_footer();
?>
