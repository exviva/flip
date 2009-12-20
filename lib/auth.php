<?php
// auth.php is responsible for validating user logins and checking user rights.

// check_valid_user() is used from every script, where it is necessary to be logged in.
// It first checks, if user is already logged in (session_is_registered('valid_user_id'),
// if yes, it has nothing more to do. If user is not logged in, it either shows the 
// login screen or evaluates the incoming parameters 'user' and 'password'.
// If the login and password match, the user is redirected to the page, which called the
// function.

function check_valid_user() {
	//user logged in
    if (session_is_registered('valid_user_id')) {
		return;
	} else {
		//user tries to log in
        if (!empty($_POST['user']) && !empty($_POST['password'])) {
			$valid_user_id = user_ok($_POST['user'], $_POST['password']);
			
			switch ($valid_user_id) {
				case false: // something went wrong with the DB
					$title = 'B³±d bazy danych, spróbuj pó¼niej.';
				break;
				case -1: //user cannot be logged in
					$title = 'Nie mogê zalogowaæ u¿ytkownika '.htmlspecialchars(stripslashes($_POST['user'])).'!';
				break;
				default: //everything OK
					$_SESSION['valid_user_id'] = $valid_user_id;
					$url = $_SERVER['PHP_SELF'].(empty($_SERVER['QUERY_STRING']) ? '' : '?'.$_SERVER['QUERY_STRING']);

					header('location: '.$url); // reload page
					exit;
			}
		}
             // form is not completed at all
		else if (!isset($_POST['user']) && !isset($_POST['password'])) {
			$title = 'Zaloguj siê';
		}
			//form incomplete
		else  {
			$title = '¬le wype³niony formularz! Spróbuj ponownie';
		}

		display_html_header();
		display_document_header(true); // true = with setfocus script
		display_menu();
		display_login_form($title);
		display_document_footer();
		exit;
	}
}

// user_ok() checks in the DB, if user $user has password $password.
// Return values:
// 		* false: DB error
//		* -1: data incorrect
//		* user_id: everything OK
function user_ok($user, $password) {
	db_connect();
	$query = "select user_id from users where login='".$user."' and password=old_password('".$password."') and status!=0";
	$result = mysql_query($query);

	if (false === $result) {
		return false;
	}
	
    if (mysql_num_rows($result)==0) {
		return -1;
	} else {
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		return $row['user_id'];
	}
}

// is_admin() checks if currently logged in user 
// has administrator priviliges.
function is_admin() {
	if (!isset($_SESSION['valid_user_id'])) {
		return false;
	}

	return (get_user_status($_SESSION['valid_user_id']) == 2);
}
?>
