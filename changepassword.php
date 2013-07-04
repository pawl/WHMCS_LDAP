<?php

function change_ldap_pass($vars) {

$ds = ldap_connect("localhost");  // assuming the LDAP server is on this host

if ($ds) {
        // otherwise PHP defaults to ldap v2 and you will get a Syntax Error!
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	
	// bind with appropriate dn to give update access
	$r = ldap_bind($ds, "cn=admin,dc=yourdomain,dc=com", "secret");

	// variables from WHMCS
	$userid = $vars['userid'];
	$password = $vars['password'];

	// query database for email
	$table2 = "tblclients";
	$fields2 = "email";
	$where2 = array("id"=>$userid);
	$result2 = select_query($table2,$fields2,$where2);
	$data2 = mysql_fetch_array($result2);
	$email = $data2['email'];
	if (!$email) {
		logActivity('Could not find email for User ID: ' . $userid);
	} else {
		// logActivity('Email from WHMCS: ' . $email);
	}

	// search LDAP for matching email
	$sr=ldap_search($ds, "ou=people,dc=yourdomain,dc=com", "mail=$email");
	if ($sr) {
		if (ldap_count_entries($ds, $sr) > 1) {
			// bad - multiple entries with that email found
			logActivity("Multiple users with that e-mail");
			$info = ldap_get_entries($ds, $sr);
			$username = $info[0]["uid"][0];
		} elseif (ldap_count_entries($ds, $sr) == 1) {
			// correct - only 1 entry with that email found
			$info = ldap_get_entries($ds, $sr);
			$username = $info[0]["uid"][0];
		} else {
			// bad - user not found
			logActivity("No matching email in LDAP");
		}
	} else {
		logActivity("LDAP Search Failure");
	}

	// Generate SSHA hash
	mt_srand((double)microtime()*1000000);
	$salt = pack("CCCC", mt_rand(), mt_rand(), mt_rand(), mt_rand());
	$group_info['userpassword'][0] = "{SSHA}" . base64_encode(pack("H*", sha1($password . $salt)) . $salt);
	$group_name = "uid=" . $username . ",ou=people,dc=yourdomain,dc=com";

	logActivity($username);
	// change user password, but not if their WHMCS email doesn't match their LDAP email
	$s = ldap_modify($ds,$group_name,$group_info);

	if (!$s) {
			// logs error if ldap_mod_add fails
			logActivity($userid . " password change failure");
			logActivity(ldap_error($ds));
	} else {
			logActivity($username . " changed password successfully");
	}

	ldap_close($ds);
} else {
	logActivity("Unable to connect to LDAP server");
}

}

add_hook("ClientChangePassword",1,"change_ldap_pass");

?>
