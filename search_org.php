<?php
	require_once('lib/flip.php');
	session_start();
	check_valid_user();

	if (!isset($_POST['search'])) {
		header('location: search_org_form.php');
		exit;
	}

	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Wyniki wyszukiwania organizacji <i>'.
		 htmlspecialchars(stripslashes($_POST['search'])).
		 '</i><hr></td></tr><tr><td>';

	$found = search_org($_POST['search']);

	if (!$found) {
		echo 'Brak wyników.';
	} else {
		$num_org = 1;
		
		foreach ($found as $org_id) {
			echo $num_org.'. ';
			++$num_org;
			display_link_to_org($org_id);
			echo "<br>\n";
		}
	}

	echo '</td></tr></table>';
	display_document_footer();
