<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	array_map($_POST, 'trim');
	
	if (empty($_POST['question']) || empty($_POST['answer'])) {
		display_warning('Wype³nij poprawnie formularz!');
		exit;
	}

	$data = array();
	
	$data['question'] = $_POST['question'];
	$data['answer'] = $_POST['answer'];
	$data['helper_id'] = $_SESSION['valid_user_id'];
	$data['category_id'] = $_POST['category_id'];

	if (isset($_POST['question_id'])) { //edit the question
		$data['question_id'] = $_POST['question_id'];
		$result = help_edit_question($data);		
	} else { //add new question
		$result = help_add_question($data);
	}
	
	if ($result === false) {
		display_warning('Operacja zakoñczona niepowodzeniem!');
	} else {
		display_warning('Operacja zakoñczona sukcesem!');
	}
?>
