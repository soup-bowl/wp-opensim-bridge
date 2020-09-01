<?php
/**
 * Bridges the user management from WordPress into OpenSimulator.
 *
 * @package wp-os-bridge
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wposbridge;

use Exception;
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
		add_action( 'profile_update', array( &$this, 'update' ) );
		add_action( 'user_register', array( &$this, 'update' ) );
	}

	/**
	 * Updates the OpenSim user with profile changes, if necessary.
	 *
	 * @param integer $wp_user_id The WordPress user being processed.
	 * @return boolean|void
	 */
	public function update( $wp_user_id ) {
		$avatar_guid = get_user_meta( $wp_user_id, 'opensim_avatar_uuid', true );
		if ( ! current_user_can( 'edit_user', $wp_user_id ) ) {
			return false;
		}

		if ( isset( $_REQUEST['opensimNonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['opensimNonce'] ), 'wposb-user-verify' ) ) {
			// Deal with the OpenSim XMLRPC admin interface.
			if ( empty( $avatar_guid )
			&& ! empty( $_REQUEST['opensimFirstname'] ) && ! empty( $_REQUEST['opensimLastname'] ) && ! empty( $_REQUEST['email'] )
			&& ! empty( $_REQUEST['pass1'] ) && ! empty( $_REQUEST['pass2'] ) ) {
				// Create a new OpenSim user.
				$params = array(
					'user_firstname' => sanitize_text_field( wp_unslash( $_REQUEST['opensimFirstname'] ) ),
					'user_lastname'  => sanitize_text_field( wp_unslash( $_REQUEST['opensimLastname'] ) ),
					'user_email'     => sanitize_email( wp_unslash( $_REQUEST['email'] ) ),
					'user_password'  => sanitize_text_field( wp_unslash( $_REQUEST['pass1'] ) ),
					'start_region_x' => 1000,
					'start_region_y' => 1000,
				);

				try {
					$resp_new = ( new Xmlrpc() )->set_command( 'admin_create_user' )->set_parameters( $params )->send();
				} catch ( Exception $e ) {
					if ( current_user_can( 'administrator' ) ) {
						wp_die( 'An OpenSimulator Bridge communication error has occurred: ' . esc_html( $e->getMessage() ) );
					} else {
						wp_die( 'An OpenSimulator Bridge communication error has occurred. Please contact the system administrator. ' );
					}
				}

				if ( ! $resp_new->success ) {
					$error = '[OpenSim Bridge] A fault occurred when updating user information: ' . $resp_new->output['error']->scalarval();
					error_log( $error );
				} else {
					add_user_meta( $wp_user_id, 'opensimFirstname', sanitize_text_field( wp_unslash( $_REQUEST['opensimFirstname'] ) ) );
					add_user_meta( $wp_user_id, 'opensimLastname', sanitize_text_field( wp_unslash( $_REQUEST['opensimLastname'] ) ) );
					add_user_meta( $wp_user_id, 'opensim_avatar_uuid', $resp_new->output['avatar_uuid']->scalarval() );
				}
			} elseif ( ! empty( $avatar_guid ) && ! empty( $_REQUEST['pass1'] ) && ! empty( $_REQUEST['pass2'] ) ) {
				// Change OpenSim user password.
				$params = array(
					'user_firstname' => get_user_meta( $wp_user_id, 'opensimFirstname', true ),
					'user_lastname'  => get_user_meta( $wp_user_id, 'opensimLastname', true ),
					'user_password'  => sanitize_text_field( wp_unslash( $_REQUEST['pass1'] ) ),
				);

				try {
					$resp_udt = ( new Xmlrpc() )->set_command( 'admin_update_user' )->set_parameters( $params )->send();
				} catch ( Exception $e ) {
					if ( current_user_can( 'administrator' ) ) {
						wp_die( 'An OpenSimulator Bridge communication error has occurred: ' . esc_html( $e->getMessage() ) );
					} else {
						wp_die( 'An OpenSimulator Bridge communication error has occurred. Please contact the system administrator. ' );
					}
				}

				if ( ! $resp_udt->success ) {
					$error = '[OpenSim Bridge] A fault occurred when updating user information: ' . $resp_udt->output['error']->scalarval();
					error_log( $error );
				}
			}
		}
	}
}
