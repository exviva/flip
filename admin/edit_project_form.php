<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();
	
	if (isset($_GET['project_id'])) {
		$project_id = $_GET['project_id'];
	} elseif (isset($_POST['project_id'])) {
		$project_id = $_POST['project_id'];
	}
	
	if (!isset($project_id)) {
		header('location: ../show/show_project.php');
	}

	if (isset($_POST['project_name'])) {
		$project_name = stripslashes($_POST['project_name']);
	} else {
		$project_name = get_project_name($project_id);
	}

	display_html_header();
	display_document_header();
	display_menu();
	
	if (!is_admin()) {
		display_no_auth();
		display_document_footer();
		exit;
	}

	echo '<table width="90%">';
	echo '<tr><td align="center" class="naglowek">Edytuj dane projektu <i>'.
		 htmlspecialchars(get_project_name($project_id))."</i><hr></td></tr>\n";

	if (isset($_POST['ocp_id'])) {
		$ocp_id = $_POST['ocp_id'];
	} else {
		$ocp_id = get_project_ocp($project_id);
	}
	?>
<form action="edit_project.php" method="POST">
<input type="hidden" name="project_id" value="<?=$project_id?>">
<tr>
	<td><table>
		<tr>
			<td align="right">Nazwa projektu:</td>
			<td align="left"><input type="text" name="project_name" value="<?
				echo htmlspecialchars($project_name)
			?>" maxlength="25" size="35"></td>
		</tr>
		<tr>
			<td align="right">OCP:</td>
			<td align="left"><select name="ocp_id">
<?php
		$active_users = get_active_users();

		if (get_user_status($ocp_id) == 0) {
			$active_users[$ocp_id] = get_user_login($ocp_id);
			asort($active_users);
		}
		
		foreach ($active_users as $user_id => $login) {
			echo '<option value="'.$user_id.'"';
		
			if ($user_id == $ocp_id) {
				echo ' selected';
			}
			
			echo '>'.$login."</option>\n";
		}
?>
			</select></td>
		</tr>
		</table>
	</td>
</tr>

<?php
	$orgs = array_keys(get_project_orgs($project_id));

	echo '<tr><td class="naglowek_maly">Zaznacz organizacje do usuniêcia:</td></tr>'."\n";

	echo "<tr><td>\n";
	
	if (empty($orgs)) {
		echo "Brak organizacji.\n";
	} else {
		echo '<table cellspacing="0" cellpadding="0">'."\n";
		
		foreach ($orgs as $o) {
			echo '<tr><td><input type="checkbox" name="del_orgs['.$o.']"';
			
			if (isset($_POST['del_orgs'][$o])) {
				echo ' checked';
			}
			
			echo '>';
			
			display_link_to_org($o);
			echo "</td></tr>\n";
		}

		echo "</table>\n";
	}

	echo "</td></tr>\n";
	echo '<tr><td class="naglowek_maly">Dodaj nowe organizacje:</td></tr>'."\n";
	echo '<tr><td><textarea name="added_orgs" cols="60" rows="20">';
	echo isset($_POST['added_orgs']) ? htmlspecialchars(stripslashes($_POST['added_orgs'])) : '';
	echo "</textarea></td></tr>\n";
	echo '<tr><td><input type="submit" value="Zmieñ"></td></tr>'."\n";
	echo '</form>';
	echo "</table>\n";

	echo '<hr width="90%">'."\n";

	if (project_is_closed($_GET['project_id'])) {
		$action = 'open';
		$label = 'Otwórz';
	} else {
		$action = 'close';
		$label = 'Zamknij';
	}
	
	echo '<table><tr><td><form action="'.$action.'_project.php" method="post">'.
		 '<input type="hidden" name="project_id" value="'.$_GET['project_id'].'">'.
		 '<input type="submit" value="'.$label.' projekt"></form></td></tr></table>';

	display_document_footer();
?>
