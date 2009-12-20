<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	$_GET['search'] = trim($_GET['search']);
	
	if (empty($_GET['search'])) {
		display_warning('¬le wype³niony formularz!');
		exit;
	}

	$condition = array();
	$select_flag = 0;
	
	if ($_GET['in_questions'] == 'on') {
		$select_flag += 1;
		$condition['in_questions'] = "question like '%".$_GET['search']."%'";
	}

	if ($_GET['in_answers'] == 'on') {
		$select_flag += 2;
		$condition['in_answers'] = "answer like '%".$_GET['search']."%'";
	}

	switch ($select_flag) {
		case 0:
			display_warning('Wybierz elementy do wyszukiwania!');
			exit;
		case 1:
			$final_condition = $condition['in_questions'];
			break;
		case 2:
			$final_condition = $condition['in_answers'];
			break;
		case 3:
			$final_condition = $condition['in_questions'].' or '.$condition['in_answers'];
			break;
	}

	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%">'."\n";

	echo '<tr><td align="center" class="naglowek">Pomoc - wyniki wyszukiwania<hr></td></tr>'."\n";

	echo '<tr><td class="naglowek_maly">Szukane wyra¿enie: '."'".htmlspecialchars(stripslashes($_GET['search'])).
		 "'".'. Oto rezultaty wyszukiwania:</td></tr>';
		 
	$results = help_search($final_condition);

/*	$results[i] = array (	'question_id' 	=> '...',
							'category_id' 	=> '...',
							'label'			=> '...'
						  );
*/

	echo '<tr><td>';
	
	if ($results === false) {
		echo "B³±d bazy danych, spróbuj pó¼niej.\n";
	} else if (empty($results)) {
		echo "Nic nie znaleziono.\n";
	} else {
		echo "<table>\n";
		
		foreach ($results as $result) {
			echo '<tr><td class="help_question">'.$result['category_id'].'.'.$result['question_id'].'.</td>';
			echo '<td><a href="'.get_www_root().'help/?category_id='.$result['category_id'].
				 '&question_id='.$result['question_id'].'" class="help_question">'.
				 htmlspecialchars($result['question'])."</a></td></tr>\n";
		}

		echo "</table>\n";
	}		

	echo "</td></tr>\n";
	echo "</table>\n";
	display_document_footer();
?>
