<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	$fields = array('org_id', 'street', 'city', 'phone', 'fax', 'www', 'profile');
	
	$_POST = array_map('trim', $_POST);

	foreach($fields as $f) {
		if (empty($_POST[$f])) {
			display_warning('Uzupe³nij poprawnie formularz!');
			exit;
		}
	}

	if (isset($_POST['name'])) {
		if (empty($_POST['name'])) {
			display_warning('Uzupe³nij poprawnie formularz!');
			exit;
		}
		
		if (!is_admin()) {
			display_document_header();
			display_html_header();
			display_menu();
			display_no_auth();
			display_document_footer();
			exit;
		} else {
			if (get_org_name($_POST['org_id']) !== stripslashes($_POST['name']) && 
					org_exists($_POST['name'])) {
				display_warning('Organizacja o nazwie '.
								htmlspecialchars(stripslashes($_POST['name'])).
								' ju¿ istnieje!');
				exit;
			} 
		}
	}

	foreach (array('phone', 'fax') as $type) {
		$_POST[$type] = str_replace(array(' ', '-'), '', $_POST[$type]);
	}

	if (update_org($_POST)) {
		$warning = 'Operacja zakoñczona sukcesem!';

		if (isset($_POST['project_id'])) {
			$types = array('telefon', 'spotkanie');
		
			foreach ($types as $type) {
				$warning .= '</p><p class="naglowek_maly">Kliknij <a href="add_contact_form.php?org_id='.
						 $_POST['org_id'].'&project_id='.$_POST['project_id'].'&type='.$type.
						 '" class="org_nowa">tu</a>, aby dodaæ '.$type.'.';
			}
		}
	} else {
		$warning = 'Nie zaktualizowano danych!';
	}

	display_warning($warning);
?>
