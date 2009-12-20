<?php
	require_once('lib/flip.php');
	session_start();
	check_valid_user();

	if (!isset($_POST['subject']) || !isset($_POST['body'])) {
		header('location: feedback_form.php');
	}

	$valid_user_login = get_user_login($_SESSION['valid_user_id']);
	$long_subject = date('Y-m-d H:i').' '.$valid_user_login.': "'.
					stripslashes($_POST['subject']).'"';

	$long_body = 'U¿ytkownik '.$valid_user_login.
				 ' przesy³a nastêpuj±c± uwagê:'.
				 "\n----------------------------------\n".
				 stripslashes($_POST['body']).
				 "\n----------------------------------\n";
				 
	$long_body .= "Aby odpowiedzieæ na t± uwagê, u¿yj opcji 'Odpowied¼' lub napisz na ten adres: ".
				  $valid_user_login.'@aiesec.uni.lodz.pl';
	$long_body .= "\n\n\nFLIP";

	$headers =  "From: FLIP <flip@aiesec.uni.lodz.pl>\r\n".
				'Reply-To: '.$valid_user_login."@aiesec.uni.lodz.pl\r\n";
	
	if (mail(ADMIN_MAIL, $long_subject, $long_body, $headers)) {
		$warning = 'Dziêkujemy! Twoje uwagi zosta³y przyjête.';
	} else {
		$warning = 'Wys³anie uwag zakoñczone niepowodzeniem!';
	}

	display_warning($warning);	
?>
