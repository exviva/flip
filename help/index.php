<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	display_html_header();
	display_document_header();
	display_menu();

	$categories = help_get_categories();

	echo '<table width="90%">'."\n";
	
	echo '<tr><td align="center" class="naglowek">Pomoc';

	if (is_admin()) {
		echo ' [<a href="add_category_form.php" class="menu">Dodaj kategoriê</a>]';
	}

	echo "<hr></td></tr>\n";

// search form
	echo '<tr><td align="center"><table cellpadding="4" cellspacing="0" border="1" bgcolor="#f8ddb8">';
	echo '<tr><td><table cellpadding="0" cellspacing="0">'."\n";
	
	echo '<form action="search.php" method="get">'."\n";
	echo '<tr><td class="naglowek_maly" align="right" valign="center">Szukaj w Pomocy:</td>'.
		 '<td><input type="text" name="search" size="35"></td>'.
		 '<td><input type="submit" value="Szukaj"></td></tr>'."\n";
		 
	echo '<tr><td align="right" colspan="3">'.
		 '<input type="checkbox" name="in_questions" id="id_q" checked><label for="id_q">w pytaniach</label>'.
		 '<input type="checkbox" name="in_answers" id="id_a" checked><label for="id_a">w odpowiedziach</label>'.
		 '</td></tr>'."\n";
	
	echo "</form>\n</tr>";
	echo "</table></td></tr></table>\n</td></tr>\n";
// end of search form
	
	echo '<tr><td class="naglowek"><br>Spis tre¶ci:</td></tr>'."\n";
	echo '<tr><td><table cellpadding="5" cellspacing="0">'."\n"; // open category table

	// FOR EACH CATEGORY
	foreach ($categories as $cat_id => $cat_label) {
		display_category($cat_id, $cat_label);
	}

	echo "</table></td></tr>\n";
	echo "</table>\n";
	display_document_footer();





/*******************************************************

					WRAPPING FUNCTIONS

*******************************************************/

function display_category($cat_id, $cat_label) {
	$is_selected = $cat_id==$_GET['category_id'];
	
// DISPLAY CATEGORY NUMBER AND LABEL
	echo '<tr><td valign="top" class="help_category">'.$cat_id.'.</td><td><a href="'.
		 ($is_selected ? '' : '?category_id='.$cat_id.'#go_category').
		 '" class="help_category">'.htmlspecialchars($cat_label).'</a>';
		
	if (is_admin()) {
		echo ' [<a href="add_category_form.php?category_id='.$cat_id.'" class="menu">Edytuj</a>]';
	}

	if ($is_selected) {
	// DISPLAY CATEGORY QUESTIONS (still same <td> as category label!)
		$questions = help_get_category_questions($cat_id);

		echo '<table id="go_category" cellpadding="2" cellspacing="0">'."\n"; //open questions table

		if ($questions === false ) {
			echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>';
		} else if (empty($questions)) {
			echo '<tr><td>Brak pytañ.</td></tr>';
		} else {
			// DISPLAY ALL QUESTIONS OF A CATEGORY (seperate table)
			
			foreach ($questions as $qsn_id => $qsn_label) {
				display_question($cat_id, $qsn_id, $qsn_label);
			}
		}
		
		echo "</table>\n";
	}

	echo "</td></tr>\n";
}

function display_question($cat_id, $qsn_id, $qsn_label) {
	$is_selected = $_GET['question_id']==$qsn_id;
	
// EACH QUESTION IS A SINGLE ROW
	echo '<tr><td valign="top" class="help_question">'.$cat_id.'.'.$qsn_id.'.</td>';
	echo '<td><a href="?category_id='.$cat_id.
		 ($is_selected ? '#go_category' : '&question_id='.$qsn_id.'#go_question').
		 '" class="help_question">'.htmlspecialchars($qsn_label)."</a>";
			   
	if (is_admin()) {
		echo ' [<a href="add_question_form.php?category_id='.$cat_id.'&question_id='.$qsn_id.'" class="menu">Edytuj</a>]';
	}
	
// DISPLAY ANSWER (still same <td> as the question!)
	if ($is_selected) {
		$qsn_details = help_get_question_details($cat_id, $qsn_id);

		echo '<table id="go_question" width="500" cellpadding="7" cellspacing="0">'."\n";
		
		if ($qsn_details === false) {
			echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>';
		} else {
		// DISPLAY ANSWER
			echo '<tr><td class="help_answer">'.nl2br(htmlspecialchars($qsn_details['answer']))."</td></tr>\n";
			
			echo '<tr><td align="right" class="help_update">(Aktualizacja: '.$qsn_details['date'].' ';
			display_link_to_user($qsn_details['helper_id']);
			echo ")</td></tr>\n";
		}

		echo "</table>\n";
	}

// CLOSE QUESTION ROW
	echo "</td></tr>\n";
}

?>
