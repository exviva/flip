<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	$_POST['project_name'] = trim($_POST['project_name']);
	$_POST['added_orgs'] = trim($_POST['added_orgs']);

	if (!isset($_POST['project_id'])) {
		display_warning('Niepoprawny adres!');
		exit;
	}
	
	if (empty($_POST['project_name']) || !isset($_POST['ocp_id']) ||
		!isset($_POST['added_orgs'])) {
		header('location: edit_project_form.php?project_id='.$_POST['project_id']);
	}
	
	if (!is_admin()) {
		display_html_header();
		display_document_header();
		display_menu();
		display_no_auth();
		display_document_footer();
		exit;
	}

	if (get_project_name($_POST['project_id']) !== stripslashes($_POST['project_name'])) {
		if (project_exists($_POST['project_name'])) {
			display_warning('Projekt o nazwie '.htmlspecialchars(stripslashes($_POST['project_name'])).' ju¿ istnieje!');
			exit;
		}
	}
	
	if (!isset($_POST['confirmed'])) {
		display_html_header();
		display_document_header();
		display_menu();
		
		echo '<table width="90%">'."\n";
		echo '<tr><td align="center" class="naglowek">Za chwilê zmienisz ustawienia projektu <i>';
		display_link_to_project($_POST['project_id']);
		echo "</i></td></tr>\n";

		echo '<tr><td align="center"><table>'."\n";
		
		if (stripslashes($_POST['project_name']) !== get_project_name($_POST['project_id'])) {
			echo '<tr><td align="right" valign="top" width="40%"><b>Nowa nazwa:</b></td>';
			echo '<td align="left" valign="top">'.htmlspecialchars(stripslashes($_POST['project_name']))."</td></tr>\n";
		}

		if ($_POST['ocp_id'] !== get_project_ocp($_POST['project_id'])) {
			echo '<tr><td align="right" valign="top" width="40%"><b>Nowy OCP:</b></td>';
			echo '<td align="left" valign="top">';
			display_link_to_user($_POST['ocp_id']);
			echo "</td></tr>\n";
		}
		
		if (isset($_POST['del_orgs'])) {
			echo '<tr><td align="right" valign="top" width="40%"><b>Organizacje do usuniêcia:</b></td>';
			echo '<td align="left" valign="top">';
			
			foreach ($_POST['del_orgs'] as $del_org => $on) {
				display_link_to_org($del_org);
				echo "<br>\n";
			}
			
			echo "</td></tr>\n";
		}

		if (!empty($_POST['added_orgs'])) {
			$orgs = str_replace("\r", '', $_POST['added_orgs']);
			$orgs = explode("\n", $orgs);
			$orgs = array_map('trim', $orgs);
			
			$upper_orgs = array();
			
			foreach ($orgs as $o) {
				if (!empty($o)) {
					$upper_orgs[strtoupper($o)] = $o;
				}
			}
			
			$orgs = array_values($upper_orgs);
			natcasesort($orgs);

			$new_orgs = get_new_orgs($orgs);
			$orgs = array_diff($orgs, $new_orgs);
			$project_new_orgs = get_project_new_orgs($_POST['project_id'], $orgs);
			$project_has_orgs = array_diff($orgs, $project_new_orgs);
			
			echo '<tr><td align="right" valign="top" width="40%"><b>Organizacje ju¿ przyznane projektowi (zostan± zignorowane):</b></td>';
			echo '<td align="left" valign="top">';
			
			if (!empty($project_has_orgs)) {
				foreach ($project_has_orgs as $org) {
					display_link_to_org(get_org_id($org));
					echo "<br>\n";
				}
			} else {
				echo '-';
			}

			echo "</td></tr>\n";
			
			echo '<tr><td align="right" valign="top" width="40%"><b>Organizacje kontaktowane:</b></td>';
			echo '<td align="left" valign="top">';
			
			if (!empty($project_new_orgs)) {
				foreach ($project_new_orgs as $org) {
					display_link_to_org(get_org_id($org));
					echo "<br>\n";
				}
			} else {
				echo '-';
			}

			echo "</td></tr>\n";
			
			echo '<tr><td align="right" valign="top" width="40%"><b>Nowe organizacje:</b></td>';
			echo '<td align="left" valign="top">';
			
			if (!empty($new_orgs)) {
				foreach ($new_orgs as $org) {
					echo htmlspecialchars(stripslashes($org))."<br>\n";
				}
			} else {
				echo '-';
			}

			echo "</td></tr>\n";
		}	
		
		echo '<tr><td align="right">';
		echo '<form action="edit_project_form.php" method="post">'."\n";

		foreach ($_POST as $name => $value) {
			if (is_array($value)) {
				foreach($value as $arr_k => $arr_v) {
					echo '<input type="hidden" name="'.$name.'['.$arr_k.']" value="'.$arr_v.'">'."\n";
				}
			} else {
				echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars(stripslashes($value)).'">'."\n";
			}
		}
		
		echo '<input type="Submit" value="Wstecz">'."\n</form>\n";
		echo '</td><td align="left">';
		echo '<form action="edit_project.php" method="post">'."\n";
			
		foreach ($_POST as $name => $value) {
			if ($name != 'added_orgs') {
				if (is_array($value)) {
					foreach ($value as $arr_k => $arr_v) {
						echo '<input type="hidden" name="'.$name.'['.$arr_k.'] value="'.$arr_v.'">'."\n";
					}
				} else {
					echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars(stripslashes($value)).'">'."\n";
				}
			}
		}
			
		echo '<input type="hidden" name="new_orgs" value="'.
			 htmlspecialchars(stripslashes(join("\n", $new_orgs))).'">'."\n";
		echo '<input type="hidden" name="project_new_orgs" value="'.
			 htmlspecialchars(stripslashes(join("\n", $project_new_orgs))).'">'."\n";
		echo '<input type="hidden" name="confirmed" value="yes">'."\n";
		echo '<input type="submit" value="Zatwierd¼">'."\n";
		echo "</form></td></tr></table>\n";
		echo "</td></tr></table>\n";
		
		display_document_footer();
	} else {
		if (stripslashes($_POST['project_name']) !== get_project_name($_POST['project_id']) && 
				!change_project_name($_POST['project_id'], $_POST['project_name'])) {
			display_warning('Zmiana nazwy projektu zakoñczona niepowodzeniem!');
			exit;
		}

		if ($_POST['ocp_id'] !== get_project_ocp($_POST['project_id']) &&
				!change_project_ocp($_POST['project_id'], $_POST['ocp_id'])) {
			display_warning('Zmiana OCPa zakoñczona niepowodzeniem!');
			exit;
		}
	
		if (isset($_POST['del_orgs']) && !delete_orgs_from_project(array_keys($_POST['del_orgs']), $_POST['project_id'])) {
			display_warning('Usuniêcie organizacji zakoñczone niepowodzeniem!');
			exit;
		}
	
		if (isset($_POST['new_orgs'])) {
			$new_orgs = str_replace("\r", '', $_POST['new_orgs']);
			$new_orgs = explode("\n", $new_orgs);

			if (!empty($new_orgs[0])) {
				if (!insert_new_orgs($new_orgs)) {
					display_warning('Dodanie nowych organizacji zakoñczone niepowodzeniem!');
					exit;
				} elseif (!insert_orgs_into_project($new_orgs, $_POST['project_id'])) {
					display_warning('Przypisanie nowych organizacji zakoñczone niepowodzeniem!');
					exit;
				}
			}
		}

		if (isset($_POST['project_new_orgs'])) {
			$project_new_orgs = str_replace("\r", '', $_POST['project_new_orgs']);
			$project_new_orgs = explode("\n", $project_new_orgs);

			if (!empty($project_new_orgs[0]) && !insert_orgs_into_project($project_new_orgs, $_POST['project_id'])) {
				display_warning('Przypisanie istniej±cych organizacji do projektu zakoñczone niepowodzeniem!');
				exit;
			}
		}
		
		display_warning('Operacja zakoñczona sukcesem!');
	}
?>
