<?php
session_start();
require_once('lib/flip.php');
check_valid_user();

display_html_header();
display_document_header();
display_menu();
display_change_password_form();
display_document_footer();
?>
