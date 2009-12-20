<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%"'.">\n";

	if (!isset($_GET['user_id'])) {
		$users = get_all_users();

		if (!$users) {
			echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr></table>';
			display_document_footer();
			exit;
		}
		
		echo '<tr><td align="center" class="naglowek">Wybierz u¿ytkownika<hr></td></tr>';
		echo '<tr><td><form method="GET" action="'.$_SERVER['PHP_SELF'].'"><select name="user_id">'."\n";
		foreach ($users as $id => $name) {
			echo '<option value="'.$id.'">'.htmlspecialchars($name)."</option>\n";
		}

		echo "</select></td></tr>\n";
		echo '<tr><td><input type="submit" value="Poka¿"></form></td></tr>';
		echo "\n</table>\n";
		
		display_document_footer();
		exit;
	}

	echo '<tr><td align="center" class="naglowek">Dane u¿ytkownika <i>'.
		 htmlspecialchars(get_user_login($_GET['user_id'])).
		 "</i><hr></td></tr>\n";
	
	$ocp_projects = get_ocp_projects($_GET['user_id']);

	echo '<tr><td><table>'."\n";
	echo '<tr><td class="naglowek_maly">U¿ytkownik jest OCPem:</td></tr>'."\n";

	if ($ocp_projects === false) {
		echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>';
	} else if (empty($ocp_projects)) {
		echo "<tr><td>Brak projektów.</td></tr>\n";
	} else {
		$num_project = 1;
		
		foreach ($ocp_projects as $pid => $name) {
			echo '<tr><td>'.$num_project.'. ';
			++$num_project;
			display_link_to_project($pid);
			echo "</td></tr>\n";
		}
	}

	echo "</table>\n<br><br><br></td></tr>";
	
	$oc_projects = get_oc_projects($_GET['user_id']);
	echo '<tr><td><table>'."\n";
	echo '<tr><td class="naglowek_maly">U¿ytkownik jest cz³onkiem OC:</td></tr>'."\n";

	if ($oc_projects === false) {
		echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>';
	} else if (empty($oc_projects)) {
		echo "<tr><td>Brak projektów.</td></tr>\n";
	} else {
		$num_oc = 1;
		
		foreach ($oc_projects as $pid) {
			echo '<tr><td>'.$num_oc.'. ';
			++$num_oc;
			display_link_to_project($pid);
			echo "</td></tr>\n";
		}
	}

	echo "</table>\n<br><br><br></td></tr>\n";

	$user_orgs = get_user_orgs($_GET['user_id']);
	echo '<tr><td><table>'."\n";
	echo '<tr><td class="naglowek_maly">U¿ytkownik jest odpowiedzialny za organizacje:</td></tr>'."\n";

	if ($user_orgs === false) {
		echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>';
	} else if (empty($user_orgs)) {
		echo "<tr><td>Brak organizacji.</td></tr>\n";
	} else {
		echo "<tr><td><table>\n";
		echo "<tr><th></th><th>Organizacja</th><th>Projekt</th></tr>\n";
		$num_org = 1;
		
		foreach ($user_orgs as $org) {
			echo '<tr><td>'.$num_org.'. </td><td>';
			++$num_org;
			display_link_to_org($org['organisation_id']);
			echo '</td><td nowrap>';
			display_link_to_project($org['project_id']);
			echo "</td></tr>\n";
		}
		echo "</table></td></tr>\n";
	}
	
	echo "</table>\n<br><br><br></td></tr>\n";
		
	$contacts = get_contacts('user_id', $_GET['user_id']);
	echo '<tr><td><table>'."\n";
	echo '<tr><td class="naglowek_maly">Kontakty u¿ytkownika:</td></tr>'."\n";
	echo '<tr><td>';

	if ($contacts === false) {
		echo 'B³±d bazy danych, spróbuj pó¼niej.';
	} else if (empty($contacts)) {
		echo 'Brak kontaktów.';
	} else {
		echo '<table>';
		echo '<tr><th></th><th>Data, typ</th><th>Organizacja</th><th>Projekt</th></tr>';
		$num_contact = 1;
		
		foreach ($contacts as $cid) {
			echo '<tr><td>'.$num_contact.'. </td><td>';
			++$num_contact;
			display_link_to_contact($cid);
			$details = get_contact_details($cid);
			echo '</td><td>';
			display_link_to_org($details['organisation_id']);
			echo '</td><td nowrap>';
			display_link_to_project($details['project_id']);
			echo '</td></tr>';
		}
		echo '</table>';
	}

	echo "</td></tr></table>\n";
	
	echo "<br><br><br></td></tr>\n";
	
	echo "</table>\n";
	display_document_footer();
?>
