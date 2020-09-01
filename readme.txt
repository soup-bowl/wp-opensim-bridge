=== WordPress OpenSim Bridge ===
Contributors: soupbowl
Tags: opensim
Requires at least: 5.0
Tested up to: 5.5
Requires PHP: 7.0
Stable tag: trunk
License: MIT

Manage OpenSim grid users via the WordPress user management system.

== Description ==
Manage an individual grid through your WordPress system by making use of the
XMLRPC interface baked into OpenSim. Once hooked up, this plugin will link up
your WordPress users with their OpenSim counterparts, providing a simple user
management interface.

This has been tested with OpenSimulator configured to Standalone mode. This users
and requires the following admin commands:

* admin_create_user
* admin_update_user

A new user will only be created when the password is regenerated, to allow for
the API to set the user password.

== Changelog ==
= 0.1 =
* Create new OpenSimulator accounts via WordPress admin.
* Update password of OpenSimulator account with the generated password.
* Set a restricted last name selection.