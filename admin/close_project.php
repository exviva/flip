<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	if (!is_admin()) {
		display_html_header();
		display_document_header();
		display_menu();
 		display_no_auth();
		display_document_footer();
		exit;
	}

	if (!isset($_POST['project_id'])) {
		header('location: ../');
		exit;
	}

	if (close_project($_POST['project_id'])) {
		$result = 'sukcesem';
	} else {
		$result = 'niepowodzeniem';
	}

	display_warning('Zakmniêcie projektu zakoñczone '.$result.'!');
?>
