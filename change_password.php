<?php
	if (empty($_POST['old_password']) || empty($_POST['new_password1']) || empty($_POST['new_password2'])) {
        header('location: change_password_form.php');
	} else {
        require_once('lib/flip.php');
        session_start();
        check_valid_user();

		$go_back = '<a href="change_password_form.php" class="menu">Spr�buj ponownie</a>.';

		if (user_ok(get_user_login($_SESSION['valid_user_id']), $_POST['old_password']) == -1) {
			display_warning('Twoje has�o jest nieprawid�owe! '.$go_back);
		} else if ($_POST['new_password1'] != $_POST['new_password2']) {
			display_warning('Nowe has�a nie s� identyczne! '.$go_back);
		} else if (strlen($_POST['new_password1']) < 3) {
			display_warning('Nowe has�o jest za kr�tkie! '.$go_back);
		} else {
			db_connect();
			$q = "update users set password=old_password('".$_POST['new_password1'].
				 "') where user_id=".$_SESSION['valid_user_id'];
			$r = mysql_query($q);

			if (!$r) {
				$warning = 'Zmiana has�a zako�czona niepowodzeniem!';
			} else {
				$warning = 'Zmiana has�a zako�czona sukcesem!';
			}

			display_warning($warning);
        }
	}
?>
