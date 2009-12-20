<?
	require_once('lib/flip.php');
	session_start();

	$old_user_id = $_SESSION['valid_user_id'];

	$result_unreg = session_unregister('valid_user_id');
	$result_dest = session_destroy();

	display_html_header();
	display_document_header();

	echo '<table align="center"><tr><td class="naglowek">';
	
	if (!empty($old_user_id)) {
		if ($result_unreg && $result_dest) {
			echo 'Wylogowanie zako�czone sukcesem.';
		} else {
			echo 'Nie mo�na wylogowa� u�ytkownika.';
		}
	} else {
		echo 'U�ytkownik nie by� zalogowany, dlatego nie nast�pi�o wylogowanie.';
	}

	echo '</td></tr></table>';
	display_document_footer();
?>
