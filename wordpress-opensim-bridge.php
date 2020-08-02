<?php
/**
 * Bridges the user management from WordPress into OpenSimulator.
 *
 * @package wp-os-bridge
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress OpenSimulator Bridge
 * Description:       Bridges the user management from WordPress into OpenSimulator.
 * Plugin URI:        https://github.com/soup-bowl/wordpress-opensim-bridge
 * Version:           0.1-dev
 * Author:            soup-bowl
 * Author URI:        https://www.soupbowl.io
 * License:           MIT
 * Text Domain:       wposbridge
 */

add_action(
	'admin_init',
	function() {
		$opts = [ 'wposbridge_address', 'wposbridge_port', 'wposbridge_secret' ];

		foreach ( $opts as $opt ) {
			register_setting( 'options', $opt );

			if ( false === get_option( $opt ) ) {
				add_option( $opt, '' );
			}
		}
	}
);

/**
 * Grab dependencies.
 */
require_once __DIR__ . '/vendor/autoload.php';

use wposbridge\Profile;

( new Profile() )->hooks();
