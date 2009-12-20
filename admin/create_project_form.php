<?php
	session_start();
	require_once('../lib/flip.php');
	check_valid_user();

	display_html_header();
	display_document_header();
	display_menu();

    if (!is_admin()) {
		display_no_auth();
	} else {
        display_create_project_form($_POST);
	}

	display_document_footer();
?>
