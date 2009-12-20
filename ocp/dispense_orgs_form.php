<?
	require_once('../lib/flip.php');
	session_start();
	check_valid_user();

    if (!isset($_GET['project_id'])) {
	    display_warning('Nie wybrano projektu!');
		exit;
	} 

	if (!isset($_GET['show'])) {
		display_warning('Nie wybrano widoku!');
		exit;
	}

    display_html_header();
	display_document_header();
	display_menu();
	
	if (!is_ocp($_SESSION['valid_user_id'], $_GET['project_id']) && !is_admin()) {
		display_no_auth();
		display_document_footer();
		exit;
    }
	
	echo '<table width="90%">'."\n";
	echo '<tr><td align="center" class="naglowek">Rozdysponuj organizacje w projekcie <i>';
	display_link_to_project($_GET['project_id']);
	echo '</i><hr></td></tr>';

	$involved = get_project_involved($_GET['project_id']);

	if ($involved === false) {
		echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr></table>';
		display_document_footer();
		exit;
	}
	
	echo '<tr><td class="naglowek_maly"><form action="'.$_SERVER['PHP_SELF'].'" method="get">';
	echo '<input type="hidden" name="project_id" value="'.$_GET['project_id'].'">';
	echo 'Poka¿ organizacje: <select name="show">';
		
	$positions = array( 'all' 	=> 'Wszystkie',
						'new' 	=> 'Nowe',
						'0'		=> 'Nieprzydzielone',
					  );
	
	foreach ($involved as $inv_k => $inv_v) {
		$positions[$inv_k] = $inv_v;
	}
	
	$num_pos = count($positions);
	$was_optgroup = false;
	$i = 0;
	
	foreach ($positions as $key => $value) {
		if ($i > 2 && !$was_optgroup) {
			$was_optgroup = true;
			echo '<optgroup label="Przydzielone:">'."\n";
		}

		++$i;

		echo '<option value="'.$key.'"';
	
		if (!strcmp($_GET['show'], $key)) {
			echo ' selected';
		}
	
		echo '>'.htmlspecialchars($value)."</option>\n";
	}
	
	if ($was_optgroup) {
		echo '</optgroup>';
	}

	echo "</select>\n";
	echo '<input type="submit" value="Poka¿">'."\n";
	echo "</form></td></tr>\n";
	
    $orgs = get_project_orgs($_GET['project_id']);

	if ($_GET['show'] == 'all') {
		$filter_orgs = &$orgs;
	} else {
		foreach ($orgs as $org_id => $resp_id) {
			if (($_GET['show'] == 'new' && $resp_id===null) || ($_GET['show'] == $resp_id)) {
				$filter_orgs[$org_id] = $resp_id;
			}
		}
	}
    
	if ($filter_orgs === false) {
		echo '<tr><td>B³±d bazy danych, spróbuj pó¼niej.</td></tr></table>';
		display_document_footer();
		exit;
	}

	if (!empty($filter_orgs)) {
	    $num_involved = count($involved);
    
		echo '<form method="post" action="dispense_orgs.php">';
		echo '<tr><td><table align="center">';

	    $which_org = 0;
    
        foreach($filter_orgs as $org_id => $resp_id) {
			echo '<tr><td align="right">';
			display_link_to_org($org_id);
			echo '</td><td><select name="responsible['.$org_id.']">';
			echo '<option value="0"';
			
			if ($resp_id === '0') { 
				echo ' selected';
			}
			
			echo '>Nieprzydzielone</option>'."\n";
				 
		    $i = 0;
    
			foreach ($involved as $inv_k => $inv_v) {
				echo '<option value="'.$inv_k.'"';
				
				if ($resp_id === null) {
					echo (($which_org % $num_involved == $i) ? ' selected' : '');
				}

				if ($resp_id == $inv_k) {
					echo ' selected';
				}
				
    	  		echo '>'.htmlspecialchars($inv_v).'</option>';
        	    ++$i;
			}
        
			echo '</select>';
			
			if ($resp_id === null) {
				display_exclamation('Nowa organizacja');
				echo '&nbsp;';
        		++$which_org;
			}
			
			echo '</td></tr>';
		}
        
		echo '</table></td></tr>';
    	echo '<tr><td align="center"><input type="submit" value="Przydziel"></td></tr>';
		echo '<input type="hidden" name="project_id" value="'.$_GET['project_id'].'">';
    	echo '</form></table>';
    } else {
		echo '<tr><td>Brak organizacji.</td></tr></table>';
	}
	
	display_document_footer();
?>
