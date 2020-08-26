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

	public function hooks() {
		add_action( 'personal_options_update', array( &$this, 'update' ) );
		add_action( 'edit_user_profile_update', array( &$this, 'update' ) );
	}

	public function update( $wp_user_id ) {
		$params[] = new Value(
			array(
				'password'       => new Value( $this->secret ),
				'user_firstname' => new Value( 'Governor' ),
				'user_lastname'  => new Value( 'Linden' ),
			),
			'struct'
		);

		$request = new Request( 'admin_exists_user', $params );
		$client  = new Client( $this->address );
		$client->setDebug(2);
		$resp    = $client->send( $request );
		echo "<pre>";
		var_dump($resp);
	}
}
