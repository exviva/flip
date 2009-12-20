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

	if (!isset($_POST['privs'])) {
		header('location: edit_privs_form.php');
		exit;
	}

	if (update_privs($_POST['privs'])) {
		$result = 'sukcesem';
	} else {
		$result = 'niepowodzeniem';
	}

	display_warning('Zmiana praw dostêpu zakoñczona '.$result.'!');
?>
