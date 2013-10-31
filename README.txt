moodle-local_loginas
====================
A local plugin that adds a 'Login as' quick menu to the administration block.
By configuration settings the menu can display:
- admin restricted login-as links to particular users specified by id or username.
- link to ajaxed course users login-as links.

Version
-------
1.3.0 (2013103100)

Requires:
--------
Moodle 2.0 or higher

Installation:
------------
Download zip from: https://github.com/itamart/moodle-local_loginas/zipball/master
    or http://moodle.org/plugins/pluginversions.php?plugin=local_loginas
Unzip into the 'local' subfolder of your Moodle install.
Rename the new folder to loginas.
Visit http://yoursite.com/admin to finish the installation. 

Documentation:
-------------
Please feel free to contribute documentation in the relevant area of
the MoodleDocs wiki.

Changelog:
----------
V 1.3.0
- Added: Group filtered course level users list. 
 If course group mode is visible or separate, users who in the course level
 can login as (moodle/user:loginas) but not access all groups (moodle/site:accessallgroups)
 will be able to see in the Course users list only members in their groups. 
V 1.2.0
- Added: config settings for admin list of loginas users by usernames
- Added: config list for showing/hiding the coruse level loginas users list.
- Fixed: config settings were not deleted on uninstall

