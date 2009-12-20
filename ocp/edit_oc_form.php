<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

    if (!isset($_GET['project_id'])) {
	    display_warning('Nie wybrano projektu!');
	    exit;
    }
    
	display_html_header();
	display_document_header();
	display_menu();

    if (is_ocp($_SESSION['valid_user_id'], $_GET['project_id']) || is_admin()) {
	    display_edit_oc_form($_GET['project_id']);
	} else {
	    display_no_auth();
    }
    
	display_document_footer();
?>
