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

	$all_users_status = get_all_users_status();

	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Zmieñ prawa dostêpu<hr></td></tr>'."\n";
	echo '<tr><td>';
	
	if ($all_user_status === false) {
		echo 'B³±d bazy danych.</td></tr>';
	} else {
		echo '<table align="center">'."\n";
		echo "<tr><th></th><th>U¿ytkownik</th><th>Nieaktywny</th><th>Aktywny</th><th>Administrator</th></tr>\n";
		echo '<form method="post" action="edit_privs.php">'."\n";
		$num_user = 1;
	
		foreach ($all_users_status as $user_id => $status) {
			echo '<tr><td>'.$num_user.'. </td><td>';
			++$num_user;
			display_link_to_user($user_id);
			echo '</td>';
			
			for ($i=0; $i<3; ++$i) {
				echo '<td align="center"><input type="radio" name="privs['.$user_id.']" value="'.$i.'"';
				
				if ($i == $status) {
					echo ' checked';
				}
			
				echo '></td>';
			}
		
			echo "</tr>\n";
		}

		echo "</table>\n</td></tr>\n";
		echo '<tr><td align="center"><input type="submit" value="Zmieñ"></td></tr>'."\n";
		echo '</form>';
	}
	
	echo "</table>\n";
	display_document_footer();
?>
