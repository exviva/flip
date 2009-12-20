<?php
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

	if (!isset($_GET['org_id'])) {
		display_warning('Musisz wybraæ organizacjê!');
		exit;
	}

	if (!is_responsible($_SESSION['valid_user_id'], $_GET['org_id']) && !is_admin()) {
		display_warning('Nie mo¿esz zmieniaæ danych tej organizacji!');
		exit;
	}

	display_html_header();
	display_document_header();
	display_menu();

	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Uzupe³nij dane o organizacji <i>'.
		 htmlspecialchars(get_org_name($_GET['org_id']))."</i><hr></td></tr>\n";

	echo '<tr><td><table align="center">';
	echo '<form method="POST" action="add_org_info.php">'."\n";
	echo '<input type="hidden" name="org_id" value="'.$_GET['org_id'].'">'."\n";
	
	if (isset($_GET['project_id'])) {
		echo '<input type="hidden" name="project_id" value="'.$_GET['project_id'].'">'."\n";
	}
	
	$org_info = get_org_info($_GET['org_id']);
	$defaults = array();
	$defaults['name'] = get_org_name($_GET['org_id']);

	if ($org_info === false) {
		$defaults['street'] = 'ul. ';
		$defaults['city'] = '£ód¼';
		$defaults['phone'] = '+4842 ';
		$defaults['fax'] = '+4842 ';
		$defaults['www'] = 'http://';
		$defaults['profile'] = 'Tu wpisz krótko, czym zajmuje siê organizacja';
	} else {
		$org_info['phone'] = parse_phone_number($org_info['phone']);
		$org_info['fax'] = parse_phone_number($org_info['fax']);
		$defaults = array_merge($defaults, $org_info);
	}
	
	$fields = array();

	if (is_admin()) {
		$fields['Nazwa'] = array('name', 100, 30);
	}
	
	$fields += array(	'Ulica, nr'				=> array('street'	, 40, 30)	,
						'Miejscowo¶æ' 			=> array('city'		, 25, 30)	,
						'Telefon' 				=> array('phone'	, 17, 30)	,
						'Fax' 					=> array('fax'		, 17, 30)	,
						'Strona WWW' 			=> array('www'		, 100, 30)	,
						'Profil dzia³alno¶ci' 	=> array('profile'	, 255, 80)
					);

	foreach ($fields as $label => $form) {
		echo '<tr><td align="right">'.htmlspecialchars($label).':</td>';
		echo '<td align="left"><input type="text" name="'.htmlspecialchars($form[0]).
			 '" value="'.htmlspecialchars($defaults[$form[0]]).
			 '" maxlength="'.$form[1].'" size="'.$form[2].'"></td></tr>'."\n";
	}

	echo "</table>\n</td></tr>";

	echo '<tr><td align="center"><input type="submit" value="Gotowe"></td></tr>'."\n";
	echo '</form></table>'."\n";

	display_document_footer();
?>
