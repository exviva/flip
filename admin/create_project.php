<?php
	session_start();
	require_once('../lib/flip.php');
	check_valid_user();

    if (!is_admin()) {
		display_html_header();
		display_document_header();
		display_menu();
		display_no_auth();
		display_document_footer();
		exit;
	}

    if (!isset($_POST['project_name']) || !isset($_POST['ocp_id'])) {
		header('location: create_project_form.php');
		exit;
	}
	
	$_POST['project_name'] = trim($_POST['project_name']);

	if (empty($_POST['project_name']) || empty($_POST['ocp_id'])) {
		display_warning('Wype³nij poprawnie formularz!');
		exit;
	} else if (project_exists($_POST['project_name'])) {
		display_warning('Projekt o nazwie '.htmlspecialchars(stripslashes($_POST['project_name'])).' ju¿ istnieje!');
		exit;
	}

	if (isset($_POST['confirmed'])) {
		if (!insert_project($_POST['project_name'], $_POST['ocp_id'])) {
			display_warning('Utworzenie projektu zakoñczone niepowodzeniem!');
			exit;
		}

		$orgs = str_replace("\r", '', $_POST['orgs']);
		$orgs = explode("\n", $orgs);
	
		$new_orgs = get_new_orgs($orgs);
		
		if (!insert_new_orgs($new_orgs)) {
			display_warning('Dodanie nowych organizacji zakoñczone niepowodzeniem!');
			exit;
		}
		
	    if (!insert_orgs_into_project($orgs, get_project_id($_POST['project_name'])) ) {
			display_warning('Przypisanie organizacji do projektu zakoñczone niepowodzeniem!');
			exit;
		}

    	display_warning('Stworzenie projektu '.
						htmlspecialchars(stripslashes($_POST['project_name'])).
						' zakoñczone sukcesem!');
    } else {
		$orgs = str_replace("\r", '', $_POST['orgs']);
    	$orgs = explode("\n", $orgs);
		$orgs = array_map('trim', $orgs);

		$upper_orgs = array();

		foreach ($orgs as $o) {
			if (!empty($o)) {
				$upper_orgs[strtoupper($o)] = $o;
			}
		}

		$orgs = array_values($upper_orgs);
		natcasesort($orgs);
		
		display_html_header();
		display_document_header();
		display_menu();
	    display_create_project_conf_form($_POST['project_name'], $_POST['ocp_id'], $orgs);
    	display_document_footer();
    }
?>
