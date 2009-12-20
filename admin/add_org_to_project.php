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

	if (in_array($_POST['project_id'], array_keys(get_org_projects($_POST['organisation_id'])))) {
		display_warning('Ta organizacja jest ju¿ w projekcie!');
		exit;
	}

    if (!isset($_POST['project_id']) || !isset($_POST['organisation_id'])) {
		header('location: ..');
		exit;
	}

	if (add_org_to_project($_POST['organisation_id'], $_POST['project_id'])) {
		header('location: ../show/show_org.php?org_id='.$_POST['organisation_id']);
	} else {
		display_warning('Dodanie organizacji do projektu zakoñczone niepowodzeniem!');
	}
?>
