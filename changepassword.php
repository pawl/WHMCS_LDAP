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

        // query database for username
        $table = "tblcustomfieldsvalues";
        $fields = "fieldid,relid,value";
        $where = array("fieldid"=>"2", "relid"=>$userid);
        $result = select_query($table,$fields,$where);
        $data = mysql_fetch_array($result);
        $username = $data['value'];
        if (!$username) {
                logActivity('Could not find username for User ID: ' . $userid);
        }

        $group_name = "uid=" . $username . ",ou=people,dc=secret,dc=com";

        $group_info['userpassword'][0] = "{SHA}" . base64_encode(sha1($password, TRUE));

        // change user password
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
