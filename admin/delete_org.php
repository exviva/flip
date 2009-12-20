<?php
	/*	This script deletes an organisation given by $_GET['org_id'].
		It is available only for administrators and uses a one-step
		confirmation.

		This is how the script works:
		1)	if the user doesn't have administrator privileges, the
			script informs about it and quits.
		2)	if the $_GET['org_id'] variable is not defined, the admin
			is asked to choose an organisation and the script quits.
		3)	if the $_GET['org_id'] variable is set, the script runs
			in confirm mode (it asks for confirmation) and sets
			$_POST['org_id'] and $_POST['confirmed'] variables.
		4)	If the administrator confirms deletion, the script tries
			to remove the organisation from MySQL and, if fails, 
			informs about it. If the script succeeds, the user is
			redirected (by header('location: ...') ) to 
			show/show_org.php?$_POST['org_id'], to see the new
			organisation located under the $_POST['org_id'] id.
			This also prevents the administrator to unconciously
			refresh the 'confirmed' form, thus deleting the next
			organisation in a row.

		NOTE: 	because the deletion operation is quite complex, it is
				recommended to run the script when there is no other
				activity in FLIP (so that the changes can be made
				uninterruptedly).
	*/

	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	if (!is_admin()) {
		display_html_header();
		display_document_header();
		display_menu();
		display_no_auth();
		display_document_footer();
		exit;
	}

	if (!isset($_GET['org_id']) && (!isset($_POST['org_id']) || !isset($_POST['confirmed'])) ) {
		display_warning('Wybierz organizacjê do usuniêcia!');
		exit;
	}

	if (isset($_GET['org_id'])) { //confirm mode
		display_html_header();
		display_document_header();
		display_menu();

		echo '<table width="90%">'."\n";
		echo '<tr><td align="center" class="naglowek">Usuwanie organizacji ';
		display_link_to_org($_GET['org_id']);
		echo "<hr></td></tr>\n";
		
		echo '<tr><td><font size="+1"><b>Czy jeste¶ pewien, ¿e chcesz usun±æ organizacjê ';
		display_link_to_org($_GET['org_id']);
		echo "?</b></font><br><br>\n".
			 '<font size="+1" color="red">Przeczytaj uwa¿nie t± wiadomo¶æ za ka¿dym razem, '.
			 'gdy chcesz usun±æ organizacjê i lepiej dwa razy pomy¶l!<br><br>'."\n".
			 '</font><font size="+1">'."\n".
			 'Upewnij siê, ¿e organizacja jest dodana do bazy przez pomy³kê, i ¿e naprawdê istnieje potrzeba usuniêcia jej.<br><br>'."\n".
			 'Poniewa¿ operacja usuwania jest skomplikowana, upewnij siê, ¿e wykonujesz j±, kiedy w bazie nie ma ¿adnej aktywno¶ci'.
			 ' (najlepsza pora to noc).<br><br>'."\n".
			 "Ze wzglêdów bezpieczeñstwa, je¶li usuniêcie powiedzie siê, zobaczysz stronê 'Szczegó³y organizacji'".
			 ' dla organizacji, która zajê³a miejsce tej w³a¶nie usuniêtej. W przeciwnym razie, zostaniesz przeniesiony'.
			 ' na Stronê g³ówn±.<br><br>'."\n".
			 "Je¶li jeste¶ pewien, naci¶nij przycisk 'Skasuj!'.</font></td></tr>\n";

		echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'."\n".
			 '<input type="hidden" name="org_id" value="'.$_GET['org_id'].'">'."\n".
			 '<input type="hidden" name="confirmed" value="yes">'."\n".
			 '<tr><td><input type="submit" value="Skasuj!"></td></tr>'."\n".
			 "</form>\n</table>";

		display_document_footer();
	} else { 											//delete mode
		if ($_POST['confirmed'] != 'yes') {
			display_warning('B³±d potwierdzenia!');
			exit;
		}

		if (delete_org($_POST['org_id']) === true) {
			$location = '../show/show_org.php?org_id='.$_POST['org_id'];
		} else {
			$location = '../';
		}

		header('location: '.$location);
	}
?>
