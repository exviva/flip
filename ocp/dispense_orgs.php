<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();


	if (!is_ocp($_SESSION['valid_user_id'], $_POST['project_id']) && !is_admin()) {
		display_html_header();
		display_document_header();
		display_menu();
		display_no_auth();
		display_footer();
		exit;
	}
	
	foreach ($_POST['responsible'] as $org_id => $oc_member_id) {
		set_org_responsible($_POST['project_id'], $org_id, $oc_member_id);
	}
		
	display_warning('Operacja zakoñczona sukcesem!');
?>
