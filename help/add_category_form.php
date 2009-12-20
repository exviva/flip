<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	display_html_header();
	display_document_header();
	display_menu();
	
	if (!is_admin()) {
		display_no_auth();
		display_document_footer();
		exit;
	}
	
	if (isset($_GET['category_id'])) {
		$cat_label = htmlspecialchars(help_get_category_label($_GET['category_id']));
	} else {
		$cat_label = '';
	}

	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">';
	
	if (isset($_GET['category_id'])) {
		echo 'Edytuj kategoriê';
		echo ' [<a href="add_question_form.php?category_id='.$_GET['category_id'].'" class="menu">Dodaj pytanie</a>]';
	} else {
		echo 'Nowa kategoria';
	}

	echo "<hr></td></tr>\n";
	
	echo '<form action="add_category.php" method="post">'."\n";
	echo '<tr><td><input type="text" name="label" value="'.$cat_label.'" maxlength="255" size="50">'."\n";

	if (isset($_GET['category_id'])) {
		echo '<input type="hidden" name="category_id" value="'.$_GET['category_id'].'">'."\n";
	}
	
	echo '<input type="submit" value="Zatwierd¼"></td></tr>'."\n";
	echo "</form>\n";

	if (isset($_GET['category_id'])) {
		echo '<tr><td class="naglowek_maly">Pytania w tej kategorii:</td></tr>'."\n";

		$questions = help_get_category_questions($_GET['category_id']);

		if ($questions === false) {
			echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>';
		} else if (empty($questions)) {
			echo '<tr><td>Brak pytañ.</td></tr>';
		} else {
			echo "<table>\n";
			
			foreach($questions as $qsn_id => $qsn_label) {
				echo '<tr><td class="help_question">'.$_GET['category_id'].'.'.$qsn_id.'.</td>';
				echo '<td><a href="'.get_www_root().'help/?category_id='.$_GET['category_id'].
					 '&question_id='.$qsn_id.'" class="help_question">'.
					 htmlspecialchars($qsn_label).'</a></td>';

				echo '<td>[<a href="add_question_form.php?category_id='.$_GET['category_id'].'&question_id='.$qsn_id.
					 '" class="menu">Edytuj</a>]</td></tr>'."\n";
			}

			echo "</table>\n";
		}
	}

	echo "</table>\n";
	display_document_footer();
?>
	
