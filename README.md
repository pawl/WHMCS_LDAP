changepassword.php: Search for a WHMCS user's account in LDAP (with a matching email address) and change that password when the password is changed in WHMCS.

ldap.php: Creates users in LDAP when they are created in WHMCS.

You will need to modify this script to match your own schema. I used phpLDAPadmin, exported an user, then copied the fields into the script exactly. Also, you will need to change the DN settings to match your own login information.

For ldap.php, you will also need to create a custom field for the user's LDAP username. Part of the code does a mysql query to find the user's LDAP username which corresponds to the userID which WHMCS returns.

Add this script to your whmcs/includes/hooks directory.

Debugging:
You can use the WHMCS activity log. The script will create an entry if it fails. However, you may want to set static variables for each of the items in the function and try to add an user.
