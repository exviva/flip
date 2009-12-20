<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	$_POST['label'] = trim($_POST['label']);
	
	if (empty($_POST['label'])) {
		display_warning('¬le wype³niony formularz!');
	}

	if (!is_admin()) {
		display_html_header();
		display_document_header();
		display_menu();
		display_no_auth();
		display_document_footer();
		exit;
	}

	if (isset($_POST['category_id'])) {
		$result = help_edit_category($_POST['category_id'], $_POST['label']);
	} else {
		$result = help_add_category($_POST['label']);
	}

	if ($result === false) {
		display_warning('Operacja zakoñczona niepowodzeniem!');
	} else {
		display_warning('Operacja zakoñczona sukcesem!');
	}
?>
