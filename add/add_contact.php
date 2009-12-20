<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	$_POST = array_map('trim', $_POST);

	foreach ($_POST as $field) {
		if (empty($field)) {
			display_warning('Wype³nij poprawnie formularz!');
			exit;
		}
	}

	if (!checkdate($_POST['month'], $_POST['day'], $_POST['year']) ||
	  mktime(0,0,0,$_POST['month'], $_POST['day'], $_POST['year'])>mktime() ||
	  !checkdate($_POST['nc_month'], $_POST['nc_day'], $_POST['nc_year']) ||
	  ($_POST['nc_type'] !== 'brak' && mktime(0,0,0,$_POST['month'], $_POST['day'], $_POST['year']) > 
	  		mktime(0,0,0,$_POST['nc_month'], $_POST['nc_day'], $_POST['nc_year']) ) ) {
		display_warning('Wprowad¼ poprawn± datê!');
		exit;
	}
	
	$data = array();

	if (!isset($_POST['contact_id'])) {
		$data['organisation_id'] = $_POST['organisation_id'];
		$data['user_id'] = $_SESSION['valid_user_id'];
		$data['project_id'] = $_POST['project_id'];

		$warning[true] = 'Dodanie kontaktu zakoñczone sukcesem!';
		$warning[false] = 'Dodanie kontaktu zakoñczone niepowodzeniem!';
	} else {
		$data['contact_id'] = $_POST['contact_id'];

		$warning[true] = 'Edycja kontaktu zakoñczona sukcesem!';
		$warning[false] = 'Edycja kontaktu zakoñczona niepowodzeniem!';
	}
	
	$data['type'] = "'".$_POST['type']."'";
	$data['date'] = "'".$_POST['year'].'-'.$_POST['month'].'-'.$_POST['day']."'";
	$data['contact_person'] = "'".$_POST['contact_person']."'";
	$data['contact_function'] = "'".$_POST['contact_function']."'";
	$data['comments'] = "'".str_replace("\r", '', $_POST['comments'])."'";
	$data['aim_id'] = $_POST['aim_id'];
	$data['next_contact_type'] = $_POST['nc_type'] == 'brak' ? 'null' : "'".$_POST['nc_type']."'";
	$data['next_contact_date'] = $_POST['nc_type'] == 'brak' ? 'null' : "'".$_POST['nc_year'].'-'.$_POST['nc_month'].'-'.$_POST['nc_day']."'";

	if (!isset($_POST['contact_id']) && contact_exists($data)) {
		display_warning('Kontakt zosta³ ju¿ dodany!');
		exit;
	}
	
	display_warning($warning[add_contact($data)]);
?>
