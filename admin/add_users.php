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

if (!isset($_POST['users'])) {
	header('location: add_users_form.php');
	exit;
}

if (empty($_POST['users'])) {
	display_warning('Wypełnij poprawnie formularz!');
	exit;
}

$users = str_replace("\r", '', $_POST['users']);
$users = explode("\n", $users);
$users = array_map('trim', $users);

$valid_users = array();

foreach ($users as $user) {
	if (preg_match('/^[a-z.]+$/', $user) === 1) {
		$valid_users[] = $user;
	}
}

if (isset($_POST['confirmed'])) {
	if (!insert_users($valid_users)) {
		display_warning('Dodanie użytkowników zakończone niepowodzeniem!');
		exit;
	}

	display_warning('Dodanie użytkowników zakończone sukcesem! Ich nowe hasło to '
		. DEFAULT_PASSWORD . '.');
} else {

	display_html_header();
	display_document_header();
	display_menu();
	display_add_users_conf_form($valid_users);
	display_document_footer();
}
?>
