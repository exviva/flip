<?php
	require_once('lib/flip.php');
	session_start();
	check_valid_user();

	display_html_header();
	echo "<body>\n";
	
	echo 'FLIP - wydruk organizacji u¿ytkownika '.htmlspecialchars(get_user_login($_SESSION['valid_user_id'])).' wygenerowany dnia '.
		 date('Y-m-d')."<br><br>\n";
	
    $fields = array('name'      => 'Nazwa',
					'city'      => 'Miejscowo¶æ',
					'street'    => 'Ulica',
					'phone'     => 'Telefon',
					'fax'       => 'Fax',
					'www'       => 'WWW',
					'profile'   => 'Profil dzia³alno¶ci',
					'date'      => 'Data aktualizacji danych',
					'updater_id'=> 'Osoba aktualizuj±ca dane'
			);
	
	echo '<table border="1" cellspacing="0" cellpadding="2"><tr><th>Nazwa</th>';
	
	$num_info = 0;
	$info_fields = array();
	
	foreach (array_keys($_POST) as $key) {
		if ('orgs' !== $key && 'comments' !== $key) {
			echo '<th>'.$fields[$key].'</th>';
			++$num_info;
			$info_fields[] = $key;
		}
	}

	echo "</tr>\n";

	foreach (array_keys($_POST['orgs']) as $org_id) {
		echo '<tr><td>'.htmlspecialchars(get_org_name($org_id)).'</td>';
		
		$org_info = get_org_info($org_id);

		if (false === $org_info) {
			for ($i=0; $i<$num_info; ++$i) {
				echo '<td>-</td>';
			}
		} else {
			reset($info_fields);
			
			for ($i=0; $i<$num_info; ++$i) {
				$field = each($info_fields);
				$field = $field[1];
				
				if ($field == 'updater_id') {
					$content = htmlspecialchars(get_user_login($org_info[$field]));
				} else if ($field == 'phone' || $field == 'fax') {
					$content = parse_phone_number($org_info[$field]);
				} else {
					$content = htmlspecialchars($org_info[$field]);
				}
				
				echo '<td>'.$content.'</td>';
			}
		}

		echo "</tr>\n";

		if (isset($_POST['comments'])) {
			echo '<tr><td colspan="'.($num_info+1).'" align="left" valign="top" height="50"><i>Uwagi:<i></td></tr>'."\n";
		}
	}
?>	
</table>

<script type="text/javascript">
<!--
window.print();
//-->
</script>

</body></html>
