You will need to modify this script to match your own schema. I used phpLDAPadmin, exported an user, then copied the fields exactly. Also, you will need to change the DN settings to match your own login information.


Debugging:
You can use the WHMCS activity log. The script will create an entry if it fails. However, you may want to set static variables for each of the items in the function and try to add an user.