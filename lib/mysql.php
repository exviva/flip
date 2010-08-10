<?php
// This file contains all functions operating on the MySQL database: 
// connecting to the DB and retrieving, inserting and updating the information.

// db_connect() connects with the MySQL server and returns false,
// if something went wrong, or the connection handler (not used anyway) otherwise.
function db_connect() {
        $config = db_config();
        $result = mysql_pconnect($config['host'], $config['user'], $config['password']);
        
	if (!$result) {
        return false;
	}
                
	if (!mysql_select_db($config['db'])) {
        return false;
	}
                
	return $result;
}

// db_get() retrieves data from table $table, column $field, 
// selecting only rows which fulfill the condition $condition. 
// The function retrieves only the first row or false on failure.
function db_get($table, $field, $condition) {
	db_connect();

	$q = "select $field from $table where $condition";
	$r = mysql_query($q);

    if (mysql_num_rows($r) == 0) {
		return false;
	} else {
		$row = mysql_fetch_array($r);
		return $row[$field];
	}
}

// This bunch of functions retrieve information giving one
// value from the one-to-one relations (such as project_id <=> name; 
// login <=> user_id; organisation_id <=> name).
function get_user_login($user_id) { return db_get('users', 'login', "user_id=$user_id"); }
function get_user_id($login) { return db_get('users', 'user_id', "login='$login'"); }
function get_project_id($project_name) { return db_get('projects', 'project_id', "name='$project_name'"); }
function get_project_name($project_id) { return db_get('projects', 'name', "project_id=$project_id"); }
function get_project_ocp($project_id) { return db_get('projects', 'ocp_id', 'project_id='.$project_id); }
function get_org_id($org_name) { return db_get('organisations', 'organisation_id', "name='".$org_name."'"); }
function get_org_name($org_id) { return db_get('organisations', 'name', 'organisation_id='.$org_id); }

// get_contacted_orgs() returns an array containing 
// organisation_ids of organsiations, whose names are 
// already in the table organisations. The input parameter 
// $orgs is an array of organisations' names. The function
// then gets all the organisation_ids, which fulfill the
// following condition: name='$orgs[0]' or name='$orgs[1] or...
// Function join() is used for joining these values into one condition.
function get_contacted_orgs($orgs) {
	$condition = "name='".join("' or name='", $orgs)."'";

	db_connect();
	$query = "select organisation_id from organisations where $condition";
	$result = mysql_query($query);

	if ($result === false) {
		return false;
	}

	$num_orgs = mysql_num_rows($result);
	
	$orgs_array = array();

	for ($i=0; $i<$num_orgs; ++$i) {
		$row = mysql_fetch_array($result);
		$orgs_array[] = $row['organisation_id'];
	}

	return $orgs_array;
}

// get_new_orgs() checkes all the organisations' names 
// (from the input parameter - array $orgs), whether or not they 
// exist in table organisations and returns all the organisations'
// names, which don't.
function get_new_orgs($orgs) {
	$orgs_array = array();
	db_connect();

	foreach ($orgs as $org) {
	    $query = "select * from organisations where name='$org'";
    	$result = mysql_query($query);
        
        if (mysql_num_rows($result) == 0) {
	        $orgs_array[] = $org;
        }
    }
        
	return $orgs_array;
}

// projects_exists() checks, if project name $project_name is
// already in the table projects.
function project_exists($project_name) {
	return db_get('projects', 'name', "name='$project_name'");
}

// project_is_closed() checks whether a project has 'closed' status (=0).
function project_is_closed($project_id) {
	return db_get('projects', 'status', 'project_id='.$project_id)==0;
}

// close_project() sets the status of the project to 0.
function close_project($project_id) {
	$q = 'update projects set status=0 where project_id='.$project_id;

	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

// open_project() sets the status of the project to 1.
function open_project($project_id) {
	$q = 'update projects set status=1 where project_id='.$project_id;

	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

// get_ocp_projects() returns an array (of format project_id => name)
// of projects, whose OCP is person with user_id $ocp_id.
function get_ocp_projects($ocp_id) {
	db_connect();
	$q = 'select project_id, name from projects where ocp_id='.$ocp_id;
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$num_projects = mysql_num_rows($r);
	$projects = array();

    for ($i=0; $i<$num_projects; ++$i) {
	    $row = mysql_fetch_array($r);
    	$projects[$row['project_id']] = $row['name'];
    }
    
	return $projects;
}

// is_ocp() checks, if person with user_id $ocp_id 
// is OCP of project with project_id $project_id.
function is_ocp($ocp_id, $project_id) {
	return db_get('projects', 'ocp_id', 'ocp_id='.$ocp_id.' and project_id='.$project_id);
}

// project_has_oc() checks, if project with project_id $project_id
// has already some OC selected.
function project_has_oc($project_id) {
	return db_get('oc', 'project_id', 'project_id='.$project_id);
}

// get_active_users() returns an array (of format user_id => login)
// of all users.
function get_active_users() {
	db_connect();
	
	$q = 'select user_id, login from users where status>0 order by login';
	$r = mysql_query($q);
	$users = array();
	$num_users = mysql_num_rows($r);
	
    for ($i=0; $i<$num_users; ++$i) {
		$row = mysql_fetch_array($r);
		$users[$row['user_id']] = $row['login'];
    }
    
	return $users;
}

// get_existing_users($users) returns an array of user_id => login
// of users whose logins exist in the $users array
function get_existing_users($users) {
	if (empty($users)) {
		return array();
	}

	db_connect();

	$users_condition = array();

	foreach ($users as $user) {
		$users_condition[] = "'$user'";
	}

	$q = 'select user_id, login from users where login in ('.join(', ', $users_condition).')';
	$r = mysql_query($q);
	$result = array();
	$num_users = mysql_num_rows($r);

	for ($i=0; $i<$num_users; ++$i) {
		$row = mysql_fetch_array($r);
		$result[$row['user_id']] = $row['login'];
	}

	return $result;
}

// get_new_users($users) returns an array of user_id's
// of users whose logins do not exist in the $users array
function get_new_users($users) {
	$existing_users = get_existing_users($users);

	return array_diff($users, $existing_users);
}

// is_oc_member() checks, whether or not person with user_id $user_id
// is member of OC of project with project_id $project_id.
function is_oc_member($user_id, $project_id) {
	return db_get('oc', 'project_id', "oc_member_id=$user_id and project_id=$project_id");
}

function delete_oc($project_id) {
	db_connect();
	$q = 'delete from oc where project_id='.$project_id;
	$r = mysql_query($q);
}

function insert_oc_member($oc_member_id, $project_id) {
	db_connect();
	$q = "insert into oc values ($oc_member_id, $project_id)";
	$r = mysql_query($q);
}

function get_my_projects($user_id) {
	if (false === ($ocp_projects = get_ocp_projects($user_id)) ) {
		return false;
	}

	if (false === ($oc_projects = get_oc_projects($user_id)) ) {
		return false;
	}

	$ocp_projects = array_keys($ocp_projects);

	$my_projects = array_merge($ocp_projects, $oc_projects);

	rsort($my_projects);
	return $my_projects;
}

function get_oc_projects($user_id) {
	$q = 'select project_id from oc where oc_member_id='.$user_id;
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$num_projects = mysql_num_rows($r);
	$oc_projects = array();

    for ($i=0; $i<$num_projects; ++$i) {
		$row = mysql_fetch_array($r);
    	$oc_projects[] = $row['project_id'];
    }

	return $oc_projects;
}

function get_my_project_orgs($user_id, $project_id) {
	$q = 'select organisation_id from projects_orgs where oc_responsible_id='.$user_id.
		   ' and project_id='.$project_id.' order by organisation_id';
	db_connect();
	$r = mysql_query($q);

	if (!$r || 0 === ($num_orgs = mysql_num_rows($r)) ) {
		return false;
	}
	
	$orgs = array();
	
	for ($i=0, $num_orgs = mysql_num_rows($r); $i<$num_orgs; ++$i) {
		$row = mysql_fetch_array($r, MYSQL_ASSOC);
		$orgs[] = $row['organisation_id'];
	}

	return $orgs;
}		

function get_oc($project_id) {
	db_connect();
	$q = 'select oc_member_id from oc where project_id='.$project_id;
	$r = mysql_query($q);

	$num__oc = mysql_num_rows($r);
	$oc = array();

    for ($i=0; $i<$num_oc; ++$i) {
		$row = mysql_fetch_array($r);
		$oc[] = $row['oc_member_id'];
    }
    
return $oc;
}

function insert_project($name, $ocp_id) {
	$q = "insert into projects (name, ocp_id, status) values ('".$name."', ".$ocp_id.', 1)';
	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

function insert_new_orgs($new_orgs) {
	$q = 'insert into organisations (name, date) values ';

	$no_array = array();

    foreach ($new_orgs as $no) {
		$no_array[] = "('".$no."', date_format(now(), '%Y-%m-%d'))";
	}

	$q .= join(', ', $no_array);
	
    $r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

function insert_orgs_into_project($orgs, $project_id) {
	$q = 'insert into projects_orgs (project_id, organisation_id) values ';

	$vals_array = array();

    foreach ($orgs as $org) {
		$vals_array[] = '( '.$project_id.', '.get_org_id($org).' )';
    }

	$q .= join(', ', $vals_array);

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

function get_project_orgs($project_id) {
	$q = "select organisation_id, oc_responsible_id from projects_orgs where project_id=$project_id order by organisation_id";
	
	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$orgs = array();
    
    while ($row = mysql_fetch_array($r)) {
	    $orgs[$row['organisation_id']] = $row['oc_responsible_id'];
    }
    
	return $orgs;
}

function get_project_involved($project_id) {
	db_connect();
	$involved = array();

	$ocp_id = get_project_ocp($project_id);
	$involved[$ocp_id] = get_user_login($ocp_id);

	$q = 'select oc_member_id from oc where project_id='.$project_id;
	$r = mysql_query($q);

    while ($row = mysql_fetch_array($r)) {
	    $involved[$row['oc_member_id']] = get_user_login($row['oc_member_id']);
    }
    
	return $involved;
}

function set_org_responsible($project_id, $org_id, $oc_responsible_id) {
	db_connect();
	$query = "update projects_orgs set oc_responsible_id=$oc_responsible_id where project_id=$project_id and organisation_id=$org_id";

	$result = mysql_query($query);
}

function empty_org($org_id) {
	db_connect();
	$q = 'select street from organisations where organisation_id='.$org_id;
	$r = mysql_query($q);

	$row = mysql_fetch_array($r);

	return empty($row['street']);
}

function is_responsible($user_id, $org_id) {
	return db_get('projects_orgs', 'oc_responsible_id', 'organisation_id='.$org_id.
		' and oc_responsible_id='.$user_id);
}

function update_org($array) {
	if (false !== strpos($array['www'], '.')) {
		if (false === strpos($array['www'], addslashes('http://')) ) {
			$array['www'] = addslashes('http://').$array['www'];
		}
	} else {
		$array['www'] = '-';
	}

	db_connect();
	$q = 'update organisations set ';

	foreach($array as $field => $value) {
		if ('org_id' != $field && 'project_id' != $field) {
			$q .= $field."='".$value."', ";
		}
	}

	$q .= 'date=CURDATE(), ';
	$q .= 'updater_id='.$_SESSION['valid_user_id'].' ';
	$q .= 'where organisation_id='.$array['org_id'];

	$r = mysql_query($q);
	return mysql_affected_rows() != -1;
}

function get_aims() {
	db_connect();
	$q = 'select aim_id, aim from aims';
	$r = mysql_query($q);
	$num_aims = mysql_num_rows($r);
	$aims = array();

	for ($i=0; $i<$num_aims; ++$i) {
		$row = mysql_fetch_array($r);
		$aims[$row['aim_id']] = $row['aim'];
	}

	return $aims;
}

function add_contact($contact) {
	if (!isset($contact['contact_id'])) {
		$q = 'insert into contacts ('.join(', ', array_keys($contact)).") values (";
		$q .= join(", ", $contact).")";
	} else {
		$q = 'update contacts set ';

		$changes = array();
		
		foreach ($contact as $field => $value) {
			if ('contact_id' != $field) {
				$changes[] = $field.'='.$value;
			}
		}

		$q .= join(', ', $changes);
		$q .= ' where contact_id='.$contact['contact_id'];
	}
	
	db_connect();
	$r = mysql_query($q);
	return $r !== false;
}

function search_org($org) {
	db_connect();
	$q = "select organisation_id from organisations where name like '%$org%' order by name";
	$r = mysql_query($q);
	$found = array();

	if (!$r) {
		return false;
	}
	
	while ($row = mysql_fetch_array($r)) {
		$found[] = $row['organisation_id'];
	}

	return $found;
}

function get_org_info($org_id) {
	if (empty_org($org_id)) {
		return false;
	}

	$q = 'select street, city, phone, fax, www, profile, date, updater_id from organisations where organisation_id='.$org_id;
	db_connect();
	$r = mysql_query($q);
	
	if ($r === false) {
		return false;
	}
	
	return mysql_fetch_array($r, MYSQL_ASSOC);
}

function get_org_projects($org_id) {
	$q = 'select project_id, oc_responsible_id from projects_orgs where organisation_id='.$org_id;
	
	db_connect();
	$r = mysql_query($q);
	
	if ($r === false) {
		return false;
	}
	
	$projects = array();
	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$projects[$row['project_id']] = $row['oc_responsible_id'];
	}

	return $projects;
}

function get_contact_details($cid) {
	$select = 'select organisation_id, contact_person, contact_function, user_id, 
			  project_id, type, aim, date, comments, next_contact_type, next_contact_date ';
	$from = 'from contacts, aims ';
	$where = 'where contact_id='.$cid.' and contacts.aim_id=aims.aim_id';
	$q = $select.$from.$where;

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}
	
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	return $row;
}

function get_other_contact($cid, $dir) {
	$select =  'select c2.contact_id from contacts as c1, contacts as c2 where c1.contact_id='.$cid.
				' and c1.organisation_id=c2.organisation_id';
	$limit = ' limit 1';
	$asc_desc = ($dir == '>') ? 'asc' : 'desc';
	
	$cond1 = ' and c1.date=c2.date and c2.contact_id'.$dir.'c1.contact_id';
	$order1 = ' order by c2.contact_id '.$asc_desc;
	
	$q1 = $select.$cond1.$order1.$limit;
	
	db_connect();
	$r1 = mysql_query($q1);

	if ($r1 === false) {
		return false;
	}
	
	if (mysql_num_rows($r1) != 0) {
		$row = mysql_fetch_array($r1);

		return $row['contact_id'];
	}
	
	$cond2 = ' and c2.date'.$dir.'c1.date';
	$order2 = ' order by c2.date '.$asc_desc.', c2.contact_id '.$asc_desc;

	$q2 = $select.$cond2.$order2.$limit;

	$r2 = mysql_query($q2);

	if ($r2 === false) {
		return false;
	}
	
	$row = mysql_fetch_array($r2);
	return $row['contact_id'];
}

function get_project_oc($project_id) {
	$q = 'select oc_member_id from oc where project_id='.$project_id;
	
	db_connect();
	$r = mysql_query($q);

	if (!$r || 0 === ($num_oc = mysql_num_rows($r)) ) {
		return false;
	}

	$oc = array();

	for ($i=0; $i<$num_oc; ++$i) {
		$row = mysql_fetch_array($r);
		$oc[] = $row['oc_member_id'];
	}

	return $oc;
}

function get_contacts($field, $value) {
	$q = 'select contact_id from contacts where '.$field.'='.$value.' order by date desc, contact_id desc';

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$num_contacts = mysql_num_rows($r);
	$contacts = array();

	for ($i=0; $i<$num_contacts; ++$i) {
		$row = mysql_fetch_array($r);
		$contacts[] = $row['contact_id'];
	}

	return $contacts;
}

function get_all_users() {
	$q = 'select user_id, login from users order by login';

	db_connect();
	$r = mysql_query($q);

	if (!$r || 0 === ($num_users = mysql_num_rows($r)) ) {
		return false;
	}

	$users = array();
	
	for ($i=0; $i<$num_users; ++$i) {
		$row = mysql_fetch_array($r);
		$users[$row['user_id']] = $row['login'];
	}

	return $users;
}

function get_admins() {
	$q = 'select user_id from users where status=2 order by login';
	db_connect();
	$r = mysql_query($q);

	if (!$r || 0 === ($num_admins = mysql_num_rows($r)) ) {
		return false;
	}
	
	$admins = array();
	
	for ($i=0; $i<$num_admins; ++$i) {
		$row = mysql_fetch_array($r);
		$admins[] = $row['user_id'];
	}

	return $admins;
}

function get_normal_users() {
	$q = 'select user_id, login from users where status=1 order by login';
	db_connect();
	$r = mysql_query($q);

	if (!$r || 0 === ($num_users = mysql_num_rows($r)) ) {
		return false;
	}

	$users = array();
	
	for ($i=0; $i<$num_users; ++$i) {
		$row = mysql_fetch_array($r);
		$users[$row['user_id']] = $row['login'];
	}

	return $users;
}

function insert_users($logins) {
	$inserted_users = array();

	foreach ($logins as $login) {
		$inserted_users[] = "('$login', 1, old_password('" . DEFAULT_PASSWORD . "'))";
	}
	$q = "insert into users (login, status, password) values " . join(', ', $inserted_users);
	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

function add_admins($admins) {
	$q = 'update users set status=2 where user_id=';
	$q .= join(' or user_id=', $admins);
	db_connect();
	$r = mysql_query($q);

	return ($r === true);
}

function remove_admin($admin) {
	$num_admins = count(get_admins());
	
	if ($num_admins<2) {
		return false;
	} else {
		$q = 'update users set status=1 where user_id='.$admin;
		$r = mysql_query($q);

		return ($r === true);
	}
}


//function delete_org($org_id) {
	/*	This function is used to delete an organisation from the database.
		It removes it from tables: organisations, project_orgs and contacts.
		It also moves all records with higher organisation_id or contact_id
		from tables organisations and contacts to one lower and changes the
		auto_increment variables of those tables.

		This is the pseudo-code algorythm for this function:

		$org_id := organisation_id of the organisation to be deleted

		while ($contact_id = org_next_contact_id) {
			delete $contact_id

			foreach (contact_id > $contact_id) {
				change contact_id = contact_id-1 in 'contacts'
			}

			foreach (organisation_id > $org_id) {
				change organisation_id = organisation_id-1 in 'organisations', 'project_orgs' and 'contacts'
				
				
	*/
/*	
	function begin_operation() {
		db_connect();
		mysql_query('begin transaction');
	}

	function end_operation($success) {
		if ($success === false) {
			mysql_query('rollback');
			return false'
		} else {
			mysql_query('commit');
			return true;
	
	// cope with projects_orgs
	$org_projects = array_keys(get_org_projects($org_id));

	if ($org_projects === false) {
		mysql_query('unlock tables');
		return false;
	}

	foreach ($org_projects as $project_id) {
		if (delete_orgs_from_project(array($org_id), $project_id) === false) {
			mysql_query('unlock tables');
			return false;
		}
	}

	// cope with contacts

	$org_contacts = get_contacts('organisation_id', $org_id);

	if ($org_contacts === false) {
		mysql_query('unlock tables');
		return false;
	}

	foreach ($org_contacts as $cid) {
		if (delete_contact($cid) === false) {
			mysql_query('unlock tables');
			return false;
		}
	}

	// cope with organisations

	if (mysgl_query('delete from organisations where organisation_id='.$org_id) === false) {
		mysql_query('unlock tables');
		return false;
	}
	
	if (mysql_query('	update organisations
						set organisation_id=organisation_id-1
						where organisation_id>'.$org_id) === false) {
		mysql_query('unlock tables');
		return false;
	}

	if (mysql_query('alter table organisations auto_increment=

	// unlock tables
	mysql_query('unlock tables');
}
*/

function get_today_updated_orgs() {
	$q = "select organisation_id, updater_id from organisations where date=curdate() and updater_id is not null";

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	if (0 === ($num_orgs = mysql_num_rows($r)) ) {
		return 0;
	}

	$orgs = array();

	for ($i=0; $i<$num_orgs; ++$i) {
		$row = mysql_fetch_array($r, MYSQL_ASSOC);
		$orgs[] = $row;
	}
	
	return $orgs;
}

function get_projects() {
	$q = 'select project_id, name from projects order by name';

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$num_projects = mysql_num_rows($r);
	$projects = array();

	for ($i=0; $i<$num_projects; ++$i) {
		$row = mysql_fetch_array($r);
		$projects[$row['project_id']] = $row['name'];
	}

	return $projects;
}

function get_user_orgs($user_id) {
	$q = 'select organisation_id, project_id from projects_orgs where oc_responsible_id='.$user_id;

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$num_orgs = mysql_num_rows($r);
	$orgs = array();

	for ($i=0; $i<$num_orgs; ++$i) {
		$orgs[$i] = mysql_fetch_array($r, MYSQL_ASSOC);
	}

	return $orgs;
}

function get_contact_person($org_id) {
	$q = 'select contact_function, contact_person from contacts where organisation_id='.
		 $org_id.' order by date desc limit 1';

	db_connect();

	$r = mysql_query($q);

	if (($r === false) || mysql_num_rows($r) === 0) {
		return '-';
	}

	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	return $row['contact_function'].' '.$row['contact_person'];
}

function get_user_status($user_id) {
	$q = 'select status from users where user_id='.$user_id;
	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	return $row['status'];
}

function update_projects_orgs($project_id) {
	$q = 'update projects_orgs set oc_responsible_id=null where project_id='.$project_id.
		 ' and oc_responsible_id not in ( '.join(', ', get_oc($project_id)).' )';
	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

function change_project_name($project_id, $new_name) {
	$q = "update projects set name='$new_name' where project_id=$project_id";
	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	if (mysql_affected_rows() === 0) {
		return false;
	}

	return true;
}

function change_project_ocp($project_id, $new_ocp_id) {
	$old_ocp_id = get_project_ocp($project_id);
	$q = 'update projects set ocp_id='.$new_ocp_id.' where project_id='.$project_id;
	db_connect();
	$r = mysql_query($q);
	
	if ($r === false) {
		return false;
	}

	if (mysql_affected_rows() === 0) {
		return false;
	}

	$q = 'update projects_orgs set oc_responsible_id='.$new_ocp_id.
		 ' where oc_responsible_id='.$old_ocp_id.
		 ' and project_id='.$project_id;

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	if (is_oc_member($new_ocp_id, $project_id)) {
		if (mysql_query('delete from oc where oc_member_id='.$new_ocp_id.' and project_id='.$project_id) === false) {
			return false;
		}
	}

	return true;
}

function get_project_new_orgs($project_id, $orgs) {
	$project_new_orgs = array();

	db_connect();
	foreach ($orgs as $o) {
		$org_id = get_org_id($o);
		$q = 'select * from projects_orgs where project_id='.$project_id.' and organisation_id='.$org_id;
		$r = mysql_query($q);

		if ($r === false) {
			return false;
		}

		if (mysql_num_rows($r) === 0) {
			$project_new_orgs[] = $o;
		}
	}

	return $project_new_orgs;
}

function delete_orgs_from_project($orgs, $project_id) {
	$q = 'delete from projects_orgs where project_id='.$project_id.' and organisation_id in (';
	$q .= join(', ', $orgs).')';
	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

function project_has_null_resp($project_id) {
	$q = 'select * from projects_orgs where oc_responsible_id is null and project_id='.$project_id;
	db_connect();
	$r = mysql_query($q);

	if ($r === false || mysql_num_rows($r) === 0) {
		return false;
	}

	return true;
}

function org_exists($org_name) {
	$q = "select name from organisations where name='$org_name'";
	db_connect();
	$r = mysql_query($q);

	if ($r === false || mysql_num_rows($r)>0) {
		return true;
	}

	return false;
}

function get_top_5($field, $active_projects=false) {
	$q = 'select '.$field.', count(*) as COUNT from contacts ';
	
	if ($active_projects === true) {
		$active_projects = array_keys(get_active_projects());
		
		$q .= 'where project_id in ('.join(', ', $active_projects).') ';
	}
		
	$q .= 'group by '.$field.' order by COUNT desc limit 5';

	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$top_5 = array();

	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$top_5[$row[$field]] = $row['COUNT'];
	}

	return $top_5;
}

function org_is_open($org_id, $user_id, $project_id) {
	$q = 'select next_contact_type from contacts where organisation_id='.$org_id.
		 ' and user_id='.$user_id.' and project_id='.$project_id.' order by date desc, contact_id limit 1';
	
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	if (mysql_num_rows($r) === 0) {
		return true;
	}

	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	
	if ($row['next_contact_type'] == null) {
		return false;
	} else {
		return true;
	}
}

function org_is_contacted($org_id, $user_id, $project_id) {
	$q = "select contact_id from contacts where organisation_id=$org_id and user_id=$user_id and project_id=$project_id";
	db_connect();
	$r = mysql_query($q);

	if ($r === false || mysql_num_rows($r)===0) {
		return false;
	}

	return true;
}

function get_all_users_status() {
	$q = 'select user_id, status from users order by login';
	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$users = array();
	
	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$users[$row['user_id']] = $row['status'];
	}

	return $users;
}

function update_privs($privs) {
	db_connect();
	
	foreach ($privs as $user_id => $status) {
		$q = 'update users set status='.$status.' where user_id='.$user_id;
		$r = mysql_query($q);

		if ($r === false) {
			return false;
		}
	}

	return true;
}

function get_active_projects() {
	$q = 'select project_id, name from projects where status=1 order by name';

	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$active_projects = array();

	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$active_projects[$row['project_id']] = $row['name'];
	}

	return $active_projects;
}

function get_planned_contacts($user_id) {
	$active_projects = array_keys(get_active_projects());
	$q = 'select max(date) as M, organisation_id from contacts where user_id='.$user_id.
		 ' and project_id in ('.join(', ', $active_projects).
		 ') group by organisation_id order by M';

	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	} else if (mysql_num_rows($r) === 0) {
		return array();
	}

	$last_contact_dates = array();

	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$last_contact_dates[$row['organisation_id']] = $row['M'];
	}
	
	$last_contacts = array();
	
	foreach ($last_contact_dates as $org_id => $date) {
		$q = 'select contact_id from contacts where user_id='.$user_id.' and organisation_id='.
			 $org_id." and date='$date' and next_contact_date is not null order by contact_id desc limit 1";

		$r = mysql_query($q);

		if ($r === false) {
			return false;
		}

		$row = mysql_fetch_array($r, MYSQL_ASSOC);

		if (!empty($row)) {
			$last_contacts[$org_id] = $row['contact_id'];
		}
	}

	if (empty($last_contacts)) {
		return array();
	}

	$q = 'select contact_id from contacts where contact_id in ('.join(', ', $last_contacts).') order by next_contact_date, contact_id desc';

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$sorted_contacts = array();

	while ($row = mysql_fetch_array($r)) {
		$sorted_contacts[] = $row['contact_id'];
	}
	
	return $sorted_contacts;
}

function contact_exists($data) {
	$q = 'select contact_id from contacts where ';

	$conditions = array();
	foreach ($data as $field => $value) {
		$conditions[] = $field.'='.$value;
	}

	$q .= join(' and ', $conditions);
	db_connect();

	$r = mysql_query($q);

	if ($r === false || mysql_num_rows($r)>0) {
		return true;
	}

	return false;
}

function get_user_stats($user_id, $project_id) {
	$q = 'select organisation_id from projects_orgs where project_id='.$project_id.' and oc_responsible_id='.$user_id;
	db_connect();
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$orgs = array();

	for ($i=0, $num_orgs = mysql_num_rows($r); $i<$num_orgs; ++$i) {
		$row = mysql_fetch_array($r, MYSQL_ASSOC);
		$orgs[] = $row['organisation_id'];
	}

	$result = array();

	$result['all'] = $num_orgs;

	if ($num_orgs === 0) {
		$result['contacted'] = 0;
	} else {
		$q = 'select count(distinct organisation_id) as cd from contacts where project_id='.
			 $project_id.' and user_id='.$user_id.' and organisation_id in ('.join(',', $orgs).')';
			 
		$r = mysql_query($q);
		
		if ($r === false) {
			return false;
		}
		
		$row = mysql_fetch_array($r, MYSQL_ASSOC);
		$result['contacted'] = $row['cd'];
	}

	foreach (array('telefon', 'spotkanie') as $type) {
		$q = 'select count(*) as c from contacts where project_id='.$project_id.' and user_id='.$user_id." and type='$type'";
		$r = mysql_query($q);
		
		if ($r === false) {
			return false;
		}
	
		while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
			$result[$type] = $row['c'];
		}
	}
		
	return $result;
}

function get_overdue_contacts() {
	$q = 'select max(date) as M, organisation_id from contacts group by organisation_id order by M desc';

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$last_contact_dates = array();

	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$last_contact_dates[$row['organisation_id']] = $row['M'];
	}
	
	$last_contacts = array();

	foreach ($last_contact_dates as $org_id => $date) {
		$q = 'select contact_id from contacts where organisation_id='.$org_id.
			 " and date='".$date."' and next_contact_date<=curdate() order by contact_id desc limit 1";

		$r = mysql_query($q);

		if ($r === false) {
			return false;
		}

		$row = mysql_fetch_array($r, MYSQL_ASSOC);

		if (!empty($row)) {
			$last_contacts[$org_id] = $row['contact_id'];
		}
	}

	$q = 'select contact_id from contacts where contact_id in ('.
		 join(', ', $last_contacts).
		 ') order by next_contact_date, contact_id';

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$sorted_contacts = array();

	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$sorted_contacts[] = $row['contact_id'];
	}

	return $sorted_contacts;
}

function add_org_to_project($organisation_id, $project_id) {
	db_connect();
	$r = mysql_query('insert into projects_orgs (project_id, organisation_id) values ('.
					 $project_id.', '.$organisation_id.')');

	if ($r === false) {
		return false;
	} else {
		return true;
	}
}

function get_project_stats($projects, $start, $end) {
	db_connect();
	
	$r = mysql_query('select project_id, type, count(*) c from contacts where project_id in ('.
		 			 join(', ', $projects).') and date between '."'".$start."'".' and '."'".$end."'".' group by project_id, type');

	if ($r === false) {
		return false;
	}

	$stats = array();
	
	while (($row = mysql_fetch_array($r, MYSQL_ASSOC)) !== false) {
		$stats[$row['project_id']][$row['type']] = $row['c'];
	}

	return $stats;
}

/*********************************************************

				Help functions
				
	Used to retrieve, add and edit information from the
	'help_categories' and 'help_questions' MySQL tables.
	
*********************************************************/

function help_get_categories() {
	$q = 'select category_id, label from help_categories order by category_id';

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$cat_array = array();

	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$cat_array[$row['category_id']] = $row['label'];
	}

	return $cat_array;
}

function help_get_category_questions($category_id) {
	$q = 'select question_id, question from help_questions where category_id='.$category_id.' order by question_id';

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		echo mysql_error();
		return false;
	}

	$qsn_array = array();

	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$qsn_array[$row['question_id']] = $row['question'];
	}

	return $qsn_array;
}

function help_get_question_details($category_id, $question_id) {
	$q = 'select helper_id, date, question, answer from help_questions where category_id='.$category_id.
		 ' and question_id='.$question_id;

	db_connect();
	
	$r = mysql_query($q);

	return mysql_fetch_array($r, MYSQL_ASSOC);
}

function help_add_category($label) {
	$q = "insert into help_categories (label) values ('$label')";

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		echo mysql_error();
		return false;
	}
	
	return true;
}

function help_get_category_label($category_id) {
	return db_get('help_categories', 'label', 'category_id='.$category_id);
}

function help_edit_category($category_id, $label) {
	$q = "update help_categories set label='$label' where category_id=$category_id";

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

function help_add_question($data) {
	$q = 'insert into help_questions (category_id, question, answer, helper_id, date) values (';
	$q .= $data['category_id'].", '".$data['question']."', '".$data['answer']."', ".$data['helper_id'];
	$q .= ', CURDATE())';

	db_connect();
	
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

function help_edit_question($data) {
	$q = 'update help_questions set ';
	$q .= "question='".$data['question']."', ";
	$q .= "answer='".$data['answer']."', ";
	$q .= 'helper_id='.$data['helper_id'].', ';
	$q .= 'date=CURDATE() ';
	$q .= 'where category_id='.$data['category_id'].' and question_id='.$data['question_id'];

	db_connect();
	
	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	return true;
}

function help_search($condition) {
	$q = 'select category_id, question_id, question from help_questions where '.$condition.' order by category_id, question_id';

	db_connect();

	$r = mysql_query($q);

	if ($r === false) {
		return false;
	}

	$results = array();

	while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
		$results[] = $row;
	}

	return $results;
}

/*********************************************************

			End of help functions.

*********************************************************/

?>
