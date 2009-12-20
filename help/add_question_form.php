<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	if (!isset($_GET['category_id'])) {
		display_warning('Wybierz kategoriê!');
		exit;
	}
	
	display_html_header();
	display_document_header();
	display_menu();
	
	if (!is_admin()) {
		display_no_auth();
		display_document_footer();
		exit;
	}

	echo '<table width="90%">'."\n";

	echo '<tr><td align="center" class="naglowek">';

	if (isset($_GET['question_id'])) {
		echo 'Edytuj pytanie';
		$qsn_details = help_get_question_details($_GET['category_id'], $_GET['question_id']);
		
		$question = htmlspecialchars($qsn_details['question']);
		$answer = htmlspecialchars($qsn_details['answer']);
	} else {
		echo 'Nowe pytanie';
		$question = '';
		$answer = '';
	}
	
	echo "<hr></td></tr>\n";

	echo '<form action="add_question.php" method="post">'."\n";
	echo '<input type="hidden" name="category_id" value="'.$_GET['category_id'].'">'."\n";

	if (isset ($_GET['question_id'])) {
		echo '<input type="hidden" name="question_id" value="'.$_GET['question_id'].'">'."\n";
	}

	echo '<tr><td align="center"><table>'."\n";
	
	echo '<tr><td align="right" width="20%">Kategoria:</td><td align="left">';
	echo '&nbsp;<a href="'.get_www_root().'help/?category_id='.$_GET['category_id'].'" class="help_category">'.
		 htmlspecialchars(help_get_category_label($_GET['category_id']))."</a></td></tr>\n";

	echo '<tr><td align="right">Pytanie:</td>';
	echo '<td align="left"><input type="text" name="question" value="'.$question.
		 '" maxlength="255" size="70"></td></tr>'."\n";
	echo '<tr><td align="right" valign="top">Odpowied¼:</td>';
	echo '<td align="left"><textarea name="answer" cols="70" rows="20">'.$answer."</textarea></td></tr>\n";
	echo '<tr><td colspan="2" align="center"><input type="submit" value="Zatwierd¼"></td></tr>'."\n";
	echo "</form>\n";

	echo "</table></td></tr>\n";

	echo "</table>\n";
	display_document_footer();
?>
