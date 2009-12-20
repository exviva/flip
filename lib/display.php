<?php
// display.php is responsible for echoing everything to the browser.

// display_html_header() echoes the html header. 
function display_html_header() {
?><html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-2" />
<title><?=OFFICIAL_NAME?></title>
<link href="<?=get_www_root()?>cfg/style.php" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" href="<?=get_www_root().'img/flip.ico'?>">
</head>
<?php
}

// display_document_header() display the header of each page.
function display_document_header($with_setfocus = false) {
?>
<body<?php echo ($with_setfocus === true) ? ' onload="setfocus()"' : '' ?>>
<table width="100%" height="110" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FF9933">
<tr>
	<td colspan="6" height="70" valign="center">
	<img src="<?=get_www_root()?>img/aiesec.gif" height="43" align="center" border="0" />
	</td>
</tr>
<tr valign="top" height="20">
	<td colspan="6" width="100%" class="naglowek_maly" align="center">
<?
	if (session_is_registered('valid_user_id')) {
		echo 'Jesteś zalogowany jako ';
		display_link_to_user($_SESSION['valid_user_id'], 'nagl');
	} else {
		echo 'Nie jesteś zalogowany';
	}
?>
	</td>
</tr>
<tr valign="center" height="20">
	<td width="250" align="right" class="naglowek_maly">Przejdź do&nbsp;&nbsp;[</td>
	<td width="100" align="center"><a href="http://www.aiesec.net/" class="nagl">.:AIESEC.net:.</a></td>
	<td width="100" align="center"><a href="http://aiesec.uni.lodz.pl/poczta/src/login.php" class="nagl">.:E-mail:.</a></td>
	<td width="100" align="center"><a href="http://www.lodz.aiesec.pl/" class="nagl">.:KL Łódź:.</a></td>
	<td width="10" align="center" class="naglowek_maly"> ]</td>
	<td width="190" align="right">

<?
	if (session_is_registered('valid_user_id')) {
		echo '<a href="'.get_www_root().'logout.php" class="nagl"> .:wyloguj się:.</a>';
	} else {
		echo '<a href="'.get_www_root().'." class="nagl"> .:zaloguj się:.</a>';
	}
?>
	</td>
</tr>
</table>
<br><br><br>
<table border="0" width="100%" align="center"><tr>
<?php
}

// display_menu() shows the left panel (user or admin).
// Input parameter $with_ranking toggles the top 5 ranking in the left pane.
function display_menu($with_ranking = false) {
    if (session_is_registered('valid_user_id')) {
        switch (get_user_status($_SESSION['valid_user_id'])) {
	        case 1: // normal users have $user_menu
    	        global $user_menu;
        	    $menu = $user_menu;
           	break;
	        case 2: // admins have $admin_menu
	            global $admin_menu;
    	        $menu = $admin_menu;
        	break;

			// $user_menu and $admin_menu are defined in cfg/config.php
        }
	} else {
		// user not logged in, menu consists only of login option
		$menu = array('' => 'Zaloguj się');
	}
?>
<td width="170" valign="top">
<table bgcolor="#D9E1F0" border="1" width="100%" cellpadding="3" cellspacing="0">
<tr>
	<th class="naglowek_maly">M E N U</th>
</tr>
<?php
	foreach($menu as $link => $title) {
		echo '<tr><td><a href="'.get_www_root().$link.
			 '" class="menu">'.$title.'</a></td></tr>'."\n";
	}
?>
</table>
<?php
	if ($with_ranking && is_admin()) { // only admins can see the ranking.
		echo "<br>\n";
		echo '<table bgcolor="#d9e1f0" border="1" width="100%" cellpadding="3" cellspacing="0">'."\n";
		echo '<tr><th class="naglowek_maly">';
		echo 'T O P 5';
		echo "</th></tr>\n";
		
		$what_top = array(	'user_id' 	 => 'Użytkownicy',
							'project_id' => 'Projekty'
						 );

		foreach($what_top as $field => $label) {
			$top_5 = get_top_5($field, true);

			if ($top_5 !== false && !empty($top_5)) {
				echo '<tr><td align="center">';
				display_star();
				echo '&nbsp;<b>'.$label.'</b>&nbsp;';
				display_star();
				echo "</td></tr>\n";

				$cur = 1;
				
				foreach ($top_5 as $id => $count) {
					echo '<tr><td>'.$cur.'.&nbsp;';
					
					if ($field == 'user_id') {
						display_link_to_user($id);
					} elseif ($field == 'project_id') {
						display_link_to_project($id);
					}

					echo '&nbsp;('.$count.")</td></tr>\n";
					++$cur;
				}
			}
		}

		echo "</table>\n";
	}
?>
</td><td align="left" valign="top">
<?php
}

// display_link_to_project() displays link to project details page.
// Project is given by $project_id.
function display_link_to_project($project_id, $status=1) {
echo '<a href="'.get_www_root().'show/show_project.php?project_id='.$project_id.'&show_orgs=0&show_contacts=0" class="'.
	($status==0 ? 'closed_project' : 'menu').'">'.htmlspecialchars(get_project_name($project_id)).'</a>';
}

// display_link_to_org() displays a link to organisation details page.
// Organisation is given by $org_id, parameter $add toggles, if the
// link should lead to add/add_org_info_form.php or show/show_org.php.
// $project_id 
function display_link_to_org($org_id, $add = false, $project_id = false) {
	$class = 'org_otwarta';
	$href = get_www_root().'show/show_org.php?org_id='.$org_id;
	$org_info = get_org_info($org_id);
	
	$title = 'Adres: '.(empty($org_info['street']) || empty($org_info['city'])?'-':htmlspecialchars($org_info['city'].
			 ', '.$org_info['street'])).
			 "\nTelefon: ".htmlspecialchars(parse_phone_number($org_info['phone'])).
			 "\nOsoba kontaktowana: ".htmlspecialchars(get_contact_person($org_id));
	
	if ($add) {
		if (empty_org($org_id)) {
			$class = 'org_nowa';
			$href = get_www_root().'add/add_org_info_form.php?org_id='.$org_id.'&project_id='.$project_id;
			$title = 'Uzupełnij dane o organizacji';
		} elseif (!org_is_contacted($org_id, $_SESSION['valid_user_id'], $project_id)) {
			$class = 'org_nowa';
		}
	}
?>
	<a href="<?=$href?>" class="<?=$class?>" title="<?=$title?>"><?=htmlspecialchars(stripslashes(get_org_name($org_id)))?></a><?php
}

function display_link_to_contact($cid, $label = '', $with_img = true, $pre = '', $post = '', $with_title = true) {
	$det = get_contact_details($cid);
	$img = get_www_root().'img/icon_'.($det['type'] == 'telefon' ? 'phone':'meeting').'.gif';
	
	if ($label === '') {
		$label = $det['date'];
	}

	echo '<table><tr><td nowrap valign="center">'.$pre.
		 '<a href="'.get_www_root().'show/show_contact.php?cid='.$cid.'" class="menu"';
	
	if ($with_title) {
		echo ' title="'.htmlspecialchars($det['comments']).'"';
	}
	
	echo '>'.$label.'</a>'.$post.'</td><td>';
	
	if ($with_img) {
		echo '<img src="'.$img.'" width="22" height="17" alt="'.$det['type'].'" border="0">';
	}
	
	echo '</td></tr></table>';
}

function display_link_to_user($user_id, $class='menu') {
	echo '<a href="'.get_www_root().'show/show_user.php?user_id='.$user_id.'" class="'.$class.'">'.
		 htmlspecialchars(get_user_login($user_id)).'</a>';
}

function display_add_contact($org_id, $project_id, $type) {
	$query_string = 'org_id='.$org_id.'&project_id='.$project_id.'&type='.$type;
	$img_type = ($type == 'telefon') ? 'phone' : 'meeting';
?>
	<a href="<?=get_www_root()?>add/add_contact_form.php?<?=$query_string?>"><img border="0" width="25" height="25" src="<?=get_www_root()?>img/icon_<?=$img_type?>.gif" alt="Dodaj <?=$type?>"></a>
<?php
}

function display_document_footer() {
?>
</td></tr>
</table>
<p class="timestamp">.:<?=OFFICIAL_NAME?> wersja <?=VER_NO?>:.
<br>
(C) 2004 
<a href="http://www.aiesec.org/" target="_blank" class="timestamp">AIESEC</a>
<a href="http://pl.aiesec.org/"  target="_blank" class="timestamp">Polska</a>
<a href="http://lodz.aiesec.pl/" target="_blank" class="timestamp">Komitet Lokalny Łódź</a></p>
</body>
</html>
<?php
}

function display_login_form($title) {
	$url = $_SERVER['PHP_SELF'].(empty($_SERVER['QUERY_STRING']) ? '' : '?'.$_SERVER['QUERY_STRING']);
?>
<script type="text/javascript">
<!--
function setfocus() {
	document.login_form.user.focus();
}
// -->
</script>
<table width="90%">
<form action="<?=$url?>" method="post" name="login_form">
<tr>
	<td align="center" class="naglowek"><?=$title?><hr></td>
</tr>
<tr>
	<td><table align="center">
	<tr>
		<td align="right">Użytkownik:</td>
		<td align="left"><input type="text" name="user" maxlength="30"></td>
	</tr>
	<tr>
		<td align="right">Hasło:</td>
		<td align="left"><input type="password" name="password" maxlength="16"></td>
	</tr>
	</table>
	</td>
<tr>
	<td align="center"><input type="submit" value="Zaloguj"></td>
</tr>
<?php
/*<tr><td colspan="2" align="center"><br>Nie masz konta? <a href="<?
echo get_www_root()?>register_form.php" class="menu">Zarejestuj się</a></td></tr>*/
?>
</form>
</table>
<?php
}

function display_no_auth()
{
?>
<table><tr><td align="center" class="naglowek">Nie masz uprawnień do oglądania tej strony! Wróć do <a href="<?=get_www_root()?>" class="menu">strony głównej</a></td></tr></table>
<?php
}

function display_create_project_form($values)
{
?>
<table width="90%">
<tr>
	<td class="naglowek" align="center">Stwórz nowy projekt<hr></td>
</tr>
<form action="<?=get_www_root()?>admin/create_project.php" method="POST">
<tr>
	<td><table><tr>
		<td align="right">Wpisz nazwę projektu:</td>
		<td align="left"><input type="text" name="project_name" value="<?
		echo htmlspecialchars(stripslashes($values['project_name']))
		?>" maxlength="25" size="35"></td>
	</tr>
	<tr>
		<td align="right">Wybierz OCPa:</td>
		<td align="left"><select name="ocp_id">
<?php
	$active_users = get_active_users();

    foreach ($active_users as $user_id => $login) {
		echo '<option value="'.$user_id.'"';
		
		if ($user_id == $values['ocp_id']) {
			echo ' selected';
		}
		
		echo '>'.$login."</option>\n";
    }
?>
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Przypnij organizacje pod projekt:</td>
		<td align="left" valign="top"><textarea name="orgs" cols="60" rows="20"><?
		echo stripslashes($values['orgs'])
		?></textarea></td>
	</tr>
	</table>
	</td>
</tr>
<tr>
	<td align="center"><input type="submit" value="Dalej"></td>
</tr>
</form>
</table>
<?php
}

function display_warning($warning)
{
display_html_header();
display_document_header();
display_menu();
echo '<p class="naglowek">'.$warning."</p>\n";
display_document_footer();
}

function display_project_stats()
{
echo '<td><table width="100%" align="center" border="0">'."\n";
echo '<tr><td colspan="2"><p class="naglowek" align="center">Statystyki projektów:</p></td></tr>'."\n";

$projects = get_project_stats();
$num_projects = count($projects);

    for ($i=0; $i<$num_projects; ++$i)
    {
    echo '<tr><td colspan="2" align="center" class="naglowek_maly">Projekt '.htmlspecialchars($projects[$i]['name']).':</td></tr>'."\n";
    echo '<tr><td width="250" align="right"><b>OCP:</b></td>';
    echo '<td width="500" align="left">'.htmlspecialchars($projects[$i]['login']).'</td></tr>'."\n";
    echo '<tr><td width="250" align="right" valign="top"><b>Członkowie OC:</b></td><td width="500" align="left">';
        
        if (empty($projects[$i]['oc']))
        {
        echo '-';
        }
        else
        {
            foreach($projects[$i]['oc'] as $oc_member)
            {
            echo htmlspecialchars($oc_member)."<br>\n";
            }
        }
                
    echo '</td></tr>'."\n".'<tr><td colspan="2"><br></td></tr>';
    }
    
echo '</td></tr></table>'."\n</td>";
}

function display_create_project_conf_form($project_name, $ocp_id, $orgs) {
//$orgs is an array with seperate different orgs' names at each index- with addslashes (by magic_quotes)
?>
<table width="90%">
<tr>
	<td align="center" class="naglowek">Zaraz utworzysz projekt<hr></td>
</tr>
<tr>
	<td>
	<table>
	<tr>
		<td align="right"><b>Nazwa projektu:</b></td>
		<td align="left"><?=htmlspecialchars(stripslashes($project_name))?></td>
	</tr>
	<tr>
		<td align="right"><b>OCP:</b></td>
		<td align="left"><?display_link_to_user($ocp_id)?></td>
	</tr>
<?php
	if (!empty($orgs)) {
?>
	<tr>
		<td align="right" valign="top"><b>Ogranizacje już kontaktowane:</b></td>
		<td align="left">
<?php
		$contacted_orgs = get_contacted_orgs($orgs);

		if ($contacted_orgs === false) {
			echo 'Błąd bazy danych, spróbuj później.';
		} else if (empty($contacted_orgs)) {
	        echo '-';
        } else {
			foreach($contacted_orgs as $org_id) {
				display_link_to_org($org_id);
				echo "<br>\n";
			}
		}
?>
		</td>
	</tr>
	<tr>
		<td align="right" valign="top"><b>Nowe ogranizacje:</b></td>
		<td align="left">
<?php
		$new_orgs = get_new_orgs($orgs);

		if (empty($new_orgs)) {
			echo '-';
		} else {
			foreach($new_orgs as $org) {
				echo htmlspecialchars(stripslashes($org))."<br>\n";
			}
		}
?>
        </td>
	</tr>
<?php
	}
?>
	<tr>
		<td align="right"><form action="create_project_form.php" method="post">
		<input type="hidden" name="project_name" value="<?=htmlspecialchars(stripslashes($project_name))?>">
		<input type="hidden" name="ocp_id" value="<?=$ocp_id?>">
		<input type="hidden" name="orgs" value="<?=htmlspecialchars(join("\n", array_map('stripslashes', $orgs)))?>">
		<input type="submit" value="Wstecz"></form>
		</td>
		<td align="left">
		<form action="create_project.php" method="post">
		<input type="hidden" name="confirmed" value="yes">
		<input type="hidden" name="project_name" value="<?=htmlspecialchars(stripslashes($project_name))?>">
		<input type="hidden" name="ocp_id" value="<?=$ocp_id?>">
		<input type="hidden" name="orgs" value="<?=htmlspecialchars(join("\n", array_map('stripslashes', $orgs)))?>">
		<input type="submit" value="Zatwierdź">
		</form>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
<?php
}

function display_change_password_form()
{
?>
<table width="90%">
<tr>
	<td align="center" class="naglowek">Zmień hasło<hr></td>
</tr>
<form action="change_password.php" method="POST">
<tr>
	<td>
	<table align="center">
	<tr>
		<td align="right">Stare hasło:</td>
		<td align="left"><input name="old_password" type="password" maxlength="16"></td>
	</tr>
	<tr>
		<td align="right">Nowe hasło:</td>
		<td align="left"><input name="new_password1" type="password" maxlength="16"></td>
	</tr>
	<tr>
		<td align="right">Powtórz nowe hasło:</td>
		<td align="left"><input name="new_password2" type="password" maxlength="16"></td>
	</tr>
	</table>
	</td>
</tr>
<tr>
	<td align="center"><input type="submit" value="Zmień"></td>
</tr>
</form>
</table>
<?php
}

function display_edit_oc_form($project_id) {
?>
<table width="90%">
<tr>
	<td align="center" class="naglowek">Wybierz skład OC do projektu <i><?=display_link_to_project($project_id)?></i><hr></td>
</tr>
<tr>
	<td class="naglowek_maly">Aby wybrać kilka osób, przytrzymaj klawisz CTRL</td>
</tr>
<tr>
	<td><form method="POST" action="<?=get_www_root()?>ocp/edit_oc.php">
	<select name="oc_ids[]" size="20" multiple>
<?php
	$active_users = get_active_users();

    foreach ($active_users as $user_id => $login) {
        if (!is_ocp($user_id, $project_id)) {
       		echo '<option value="'.$user_id.'"';
    
            if (is_oc_member($user_id, $project_id)) {
	            echo ' selected';
            }
        
    	    echo '>'.htmlspecialchars(stripslashes($login))."</option>\n";
        }
    }

?>
	</select></td>
</tr>
<tr>
	<td><input type="hidden" name="project_id" value="<?=$project_id?>">
	<input type="submit" value="Zmień"></form>
	</td>
</tr>
</table>
<?php
}

function display_exclamation($alt) {
	echo '<img src="'.get_www_root().'img/exclamation.jpg" height="15" width="15" alt="'.htmlspecialchars($alt).'">';
}

function display_star() {
	echo '<img src="'.get_www_root().'img/star.gif" height="12" width="12">';
}

function display_link_to_search_org($org_id) {
?>
<a href="http://www.pf.pl/portal/YP1?keyword=<?=urlencode(get_org_name($org_id))?>&city=%C5%81%C3%B3dzkie&kw=f" target="_blank"><img src="<?
	echo get_www_root();
?>img/magnify.jpg" border="0" height="18" width="18" alt="Szukaj w Internecie"></a>
<?php
}

function parse_phone_number($number) {
	if (12 === strlen($number)) {
		if (substr($number, 3, 2) == '42') {
			return join(' ', array(substr($number, 0, 5),
								   substr($number, 5, 3),
								   substr($number, 8, 2),
								   substr($number, 10, 2)
								  )
					   );
		} else {
			return join(' ', array(substr($number, 0, 3),
								   substr($number, 3, 3),
								   substr($number, 6, 3),
								   substr($number, 9, 3)
								  )
					   );
		}
	} else {
		return '-';
	}
}
?>
