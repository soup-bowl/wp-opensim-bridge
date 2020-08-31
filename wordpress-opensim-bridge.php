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

/**
 * Grab dependencies.
 */
require_once __DIR__ . '/vendor/autoload.php';

use wposbridge\Profile;
use wposbridge\Opensim;
use wposbridge\Settings;

( new Profile() )->hooks();
( new Opensim() )->hooks();
( new Settings() )->hooks();
