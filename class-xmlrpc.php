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
use PhpXmlRpc\Request;
use PhpXmlRpc\Client;
use PhpXmlRpc\Value;


/**
 * Modifies and communicates with an XMLRPC endpoint.
 */
class Xmlrpc {
	/**
	 * XMLRPC command to be executed.
	 *
	 * @var string
	 */
	private $command;

	/**
	 * Parameters send to the XMLRPC endpoint.
	 *
	 * @var array
	 */
	private $parameters;

	/**
	 * Sets the XMLRPC command to be executed.
	 *
	 * @param string $command The XMLRPC control string.
	 * @return self;
	 */
	public function set_command( $command ) {
		$this->command = $command;

		return $this;
	}

	/**
	 * Generate XMLRPC-formatted parameters (includes secret key automatically).
	 *
	 * @param array $array Array of key and values to be sent to the XMLRPC.
	 * @return self
	 */
	public function set_parameters( $array ) {
		$array['password'] = get_option( 'wposbridge_secret' );

		$new_arr = array();
		foreach ( $array as $key => $val ) {
			$new_arr[ $key ] = new Value( $val );
		}

		$this->parameters = array( new Value( $new_arr, 'struct' ) );

		return $this;
	}

	/**
	 * Sends the request to the XMLRPC endpoint.
	 *
	 * @throws Exception If command and/or parameters aren't set, or the XMLRPC endpoint cannot be reached.
	 * @return stdClass 'success' state, and 'output'.
	 */
	public function send() {
		$response    = array();
		$client_addr = get_option( 'wposbridge_address' );
		if ( ! isset( $client_addr, $this->command, $this->parameters ) ) {
			throw new Exception( 'Required pre-setup not completed' );
		}

		$request  = new Request( $this->command, $this->parameters );
		$client   = new Client( $client_addr );
		$xml_resp = $client->send( $request );

		if ( $xml_resp->faultCode() ) {
			throw new Exception( 'A communication error with the XMLRPC endpoint occurred' );
		}

		return (object) array(
			'success' => $xml_resp->value()['success']->scalarval(),
			'output'  => $xml_resp->value(),
		);
	}
}
