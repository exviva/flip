<?php
	require_once('lib/flip.php');
	session_start();
	check_valid_user();

	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Przygotuj wersjê do druku<hr></td></tr>'."\n";

	echo '<tr><td class="naglowek_maly">Wybierz pola, które chcesz uwzglêdniæ na wydruku:</td></tr>'."\n";

	//$fields is an array of record fields that will be visible in the print-out.
	//The array keys are the MySQL labels and the values are arrays of: printed label, checked, disabled
	
	$fields = array('name' 		=> array('Nazwa', 1, 1),
					'city' 		=> array('Miejscowo¶æ', 1, 0),
					'street' 	=> array('Ulica', 1, 0),
					'phone' 	=> array('Telefon', 1, 0),
					'fax' 		=> array('Fax', 0, 0),
					'www' 		=> array('WWW', 0, 0),
					'profile'	=> array('Profil dzia³alno¶ci', 0, 0),
					'date'		=> array('Data aktualizacji danych', 0, 0),
					'updater_id'=> array('Osoba aktualizuj±ca dane', 0, 0),
					'comments'	=> array('Miejsce na uwagi', 1, 0)
			);

	echo '<form action="print_orgs.php" method="post">'."\n";

	foreach ($fields as $key => $value) {
		echo '<tr><td><input type="checkbox" name="'.$key.'"';

		if (1 === $value[1]) {
			echo ' checked';
		}

		if (1 === $value[2]) {
			echo ' disabled';
		}

		echo '>'.$value[0]."</td></tr>\n";
	}

	echo '<tr><td class="naglowek_maly">Wybierz organizacje, które chcesz uwzglêdniæ na wydruku:</td></tr>'."\n";
	
	$user_orgs = get_user_orgs($_SESSION['valid_user_id']);

	if ($user_orgs === false) {
		echo "<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr>\n";
	} else if (empty($user_orgs)) {
		echo "<tr><td>Brak organizacji.</td></tr>\n";
	} else {
		foreach ($user_orgs as $org) {
			echo '<tr><td><input type="checkbox" name="orgs['.$org['organisation_id'].']" checked>';
			display_link_to_org($org['organisation_id']);
			echo "</td></tr>\n";
		}
	}

	echo '<tr><td><input type="submit" value="Zatwierd¼"></td></tr>'."\n";
	echo "</form>\n";

	echo "</table>\n";
	display_document_footer();

?>
