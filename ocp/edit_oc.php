<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

    if (!isset($_POST['project_id'])) {
	    display_warning('Musisz wybra� projekt!');
		exit;
	}
    
	if (!is_ocp($_SESSION['valid_user_id'], $_POST['project_id']) &&
            !is_admin()) {
		display_html_header();
		display_document_header();
		display_menu();
		display_no_auth();
		display_document_footer();
		exit;
    }
	
	$success = delete_oc($_POST['project_id']);

	if ($success === false) {
		display_warning('B��d bazy danych, spr�buj p�niej.');
		exit;
	} else if (isset($_POST['oc_ids'])) {
		foreach ($_POST['oc_ids'] as $oc_member) {
			$success_insert = insert_oc_member($oc_member, $_POST['project_id']);

			if ($success_insert === false) {
				display_warning('B��d bazy danych, spr�buj p�niej.');
				exit;
			}
		}
    }
    
	update_projects_orgs($_POST['project_id']);

	display_warning('Operacja zako�czona sukcesem! Przejd� '.
					'<a href="dispense_orgs_form.php?project_id='.$_POST['project_id'].'&show=all" class="org_nowa">tu</a>'.
					', aby rozdysponowa� organizacje teraz.');
?>
