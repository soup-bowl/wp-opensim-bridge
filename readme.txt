=== OpenSimulator Bridge ===
Contributors: soupbowl
Tags: opensim
Requires at least: 5.0
Tested up to: 5.5
Requires PHP: 7.0
License: MIT

Manage OpenSimulator grid users via the WordPress user management system.

== Description ==
**This plugin is in early days, so you may experience bugs. Please bear with us and report any bugs you come across**

Manage an individual grid through your WordPress system by making use of the XMLRPC interface baked into OpenSimulator. Once hooked up, this plugin will link up your WordPress users with their OpenSimulator counterparts, providing a simple user management interface.

This has been tested with OpenSimulator configured to Standalone mode. This uses and requires the following admin commands:

* admin_create_user
* admin_update_user

A new user will only be created when the password is regenerated, to allow for the API to set the user password.

== Frequently Asked Questions ==
= What OpenSimulator configuration is needed for this plugin? =
The Remote Administration interface needs to be enabled, which is an XMLRPC feature that permits commands to be sent to the OpenSimulator grid. This is how we make changes to the grid.

[RemoteAdmin - OpenSimulator Wiki](http://opensimulator.org/wiki/RemoteAdmin)

We strongly recommend setting a secret key, to avoid unwanted administration commands being sent to the grid. If you wish to restrict the enabled methods, the plugin description lists the required admin commands to be permitted.

= How do I manually hook an account to a user? (advanced) =
You can add the following to wp_usermeta table (or more preferably, via wp-cli's wp user meta control).

* 'opensim_avatar_uuid' (PrincipalID in UserAccounts table).
* 'opensim_firstname' (FirstName in UserAccounts table).
* 'opensim_lastname' (LastName in UserAccounts table).

The OpenSimulator xmlrpc operates predominantly on first and last name, so it's important that this matches exactly what the configuration is in OpenSimulator.

= Source code =
The project is currently developed on GitHub. Head on over to our repo to report faults, contribute code or even fork your own version of the plugin.

[github.com/soup-bowl/wordpress-opensim-bridge](https://github.com/soup-bowl/wordpress-opensim-bridge)

== Changelog ==
= 0.1.1 =
* Plugin name change to adhere with WordPress directory guidelines.

= 0.1 =
* Create new OpenSimulator accounts via WordPress admin.
* Update password of OpenSimulator account with the generated password.
* Set a restricted last name selection.