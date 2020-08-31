<?php
/**
 * Bridges the user management from WordPress into OpenSimulator.
 *
 * @package wp-os-bridge
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wposbridge;

use PhpXmlRpc\Request;
use PhpXmlRpc\Client;
use PhpXmlRpc\Value;


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

			$request = new Request( 'admin_exists_user', $this->xmlrpc_param( $params ) );
			$client  = new Client( $this->address );
			$resp1   = $client->send( $request );
			$err     = '[OpenSim Bridge] ';

			if ( ! $resp1->faultCode() && $resp1->value()['success']->scalarval() ) {
				// The linked OpenSim user exists.
				if ( isset( $_REQUEST['pass1'], $_REQUEST['pass2'] ) ) {
					$params['user_password'] = sanitize_text_field( wp_unslash( $_REQUEST['pass1'] ) );

					$request = new Request( 'admin_update_user', $this->xmlrpc_param( $params ) );
					$resp2   = $client->send( $request );

					if ( ! $resp2->faultCode() && false === $resp2->value()['success']->scalarval() ) {
						$error = "{$err} A fault occurred when updating user information: " . $resp2->value()['error']->scalarval();
						error_log( $error );
					}
				}
			} elseif ( empty( $avatar_guid ) && ! $resp1->faultCode() && false === $resp1->value()['success']->scalarval() ) {
				// No OpenSim user was found.
				if ( isset( $_REQUEST['email'], $_REQUEST['pass1'], $_REQUEST['pass2'] ) ) { // OpenSim fields already checked.
					$params['user_email']     = sanitize_email( wp_unslash( $_REQUEST['email'] ) );
					$params['user_password']  = sanitize_text_field( wp_unslash( $_REQUEST['pass1'] ) );
					$params['start_region_x'] = 1000;
					$params['start_region_y'] = 1000;

					$request = new Request( 'admin_create_user', $this->xmlrpc_param( $params ) );
					$resp2   = $client->send( $request );

					if ( ! $resp2->faultCode() && false === $resp2->value()['success']->scalarval() ) {
						$error = "{$err} A fault occurred when updating user information: " . $resp2->value()['error']->scalarval();
						error_log( $error );
					} else {
						add_user_meta( $wp_user_id, 'opensim_avatar_uuid', $resp2->value()['avatar_uuid']->scalarval() );
					}
				}
			} else {
				error_log( "{$err} An unexpected response was recieved from the XMLRPC source." );
			}
		}
	}

	/**
	 * Generate XMLRPC-formatted parameters (includes secret key automatically).
	 *
	 * @param array $array Array of key and values to be sent to the XMLRPC.
	 * @return Value[] XMLRPC Value struct collective.
	 */
	private function xmlrpc_param( $array ) {
		$array['password'] = $this->secret;

		$new_arr = array();
		foreach ( $array as $key => $val ) {
			$new_arr[ $key ] = new Value( $val );
		}

		return array( new Value( $new_arr, 'struct' ) );
	}
}
