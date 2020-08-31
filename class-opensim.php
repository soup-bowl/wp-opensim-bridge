<?php
/**
 * Bridges the user management from WordPress into OpenSimulator.
 *
 * @package wp-os-bridge
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wposbridge;

use wposbridge\Xmlrpc;

/**
 * Handles the communication between WordPress and OpenSimulator.
 */
class Opensim {
	/**
	 * Web URL location of the XMLRPC communication.
	 *
	 * @var string
	 */
	protected $address;

	/**
	 * Secret key to pass XMLRPC authentication.
	 *
	 * @var string
	 */
	protected $secret;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->address = get_option( 'wposbridge_address' );
		$this->secret  = get_option( 'wposbridge_secret' );
	}

	/**
	 * Hooks actions and filters into the WordPress system.
	 *
	 * @return void Runs hooks.
	 */
	public function hooks() {
		add_action( 'personal_options_update', array( &$this, 'update' ) );
		add_action( 'edit_user_profile_update', array( &$this, 'update' ) );
	}

	/**
	 * Updates the OpenSim user with profile changes, if necessary.
	 *
	 * @param integer $wp_user_id The WordPress user being processed.
	 * @return void
	 */
	public function update( $wp_user_id ) {
		$avatar_guid = get_user_meta( $wp_user_id, 'opensim_avatar_uuid', true );
		if ( isset( $_REQUEST['opensimFirstname'], $_REQUEST['opensimLastname'] ) ) {
			$params = array(
				'user_firstname' => sanitize_text_field( wp_unslash( $_REQUEST['opensimFirstname'] ) ),
				'user_lastname'  => sanitize_text_field( wp_unslash( $_REQUEST['opensimLastname'] ) ),
			);

			$resp = ( new Xmlrpc() )->set_command( 'admin_exists_user' )->set_parameters( $params )->send();
			$err  = '[OpenSim Bridge] ';

			if ( $resp->success ) {
				// The linked OpenSim user exists.
				if ( isset( $_REQUEST['pass1'], $_REQUEST['pass2'] ) ) {
					$params['user_password'] = sanitize_text_field( wp_unslash( $_REQUEST['pass1'] ) );

					$resp_udt = ( new Xmlrpc() )->set_command( 'admin_update_user' )->set_parameters( $params )->send();

					if ( ! $resp_udt->success ) {
						$error = "{$err} A fault occurred when updating user information: " . $resp_udt->output['error']->scalarval();
						error_log( $error );
					}
				}
			} elseif ( empty( $avatar_guid ) && ! $resp->success ) {
				// No OpenSim user was found.
				if ( isset( $_REQUEST['email'], $_REQUEST['pass1'], $_REQUEST['pass2'] ) ) { // OpenSim fields already checked.
					$params['user_email']     = sanitize_email( wp_unslash( $_REQUEST['email'] ) );
					$params['user_password']  = sanitize_text_field( wp_unslash( $_REQUEST['pass1'] ) );
					$params['start_region_x'] = 1000;
					$params['start_region_y'] = 1000;

					$resp_new = ( new Xmlrpc() )->set_command( 'admin_create_user' )->set_parameters( $params )->send();

					if ( ! $resp_new->success ) {
						$error = "{$err} A fault occurred when updating user information: " . $resp_new->output['error']->scalarval();
						error_log( $error );
					} else {
						add_user_meta( $wp_user_id, 'opensim_avatar_uuid', $resp_new->output['avatar_uuid']->scalarval() );
					}
				}
			} else {
				error_log( "{$err} An unexpected response was recieved from the XMLRPC source." );
			}
		}
	}
}
