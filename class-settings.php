<?php
/**
 * Bridges the user management from WordPress into OpenSimulator.
 *
 * @package wp-os-bridge
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wposbridge;

/**
 * Controls system-wide settings.
 */
class Settings {
	/**
	 * Hook the plugin functionality into WordPress.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( &$this, 'page' ) );
		add_action( 'admin_init', array( &$this, 'settings' ) );
	}

	/**
	 * Registers the settings page with WordPress.
	 */
	public function page() {
		add_options_page(
			__( 'OpenSim Bridge Settings', 'wposbridge' ),
			__( 'OpenSim Bridge Settings', 'wposbridge' ),
			'manage_options',
			'wpos_settings',
			array( &$this, 'page_contents' )
		);
	}

	/**
	 * Setup the surrounding settings page contents.
	 */
	public function page_contents() {
		?>
		<form action='options.php' method='post'>
			<h2><?php _e( 'OpenSimulator Bridge Settings', 'wposbridge' ); ?></h2>
			<?php
			settings_fields( 'wpos' );
			do_settings_sections( 'wpos' );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Renders the settings fields within our settings segment.
	 */
	public function settings() {
		register_setting( 'wpos', 'wpos' );

		add_settings_section(
			'wpos_xmlrpc',
			__( 'XMLRPC Server', 'wposbridge' ),
			function () {
				esc_html_e( 'Configure WordPress to talk to your XMLRPC server.', 'wpsimplesmtp' );
			},
			'wpos'
		);

		add_settings_field(
			'wpos_xmlrpc_server',
			__( 'XMLRPC Address', 'wposbridge' ),
			function() {
				$opt = get_option( 'wpos' );
				$val = ( ! empty( $opt['address'] ) ) ? $opt['address'] : '';
				?>
				<input type='text' class='regular-text ltr' name='wpos[address]' value='<?php echo esc_attr( $val ); ?>'>
				<?php
			},
			'wpos',
			'wpos_xmlrpc'
		);

		add_settings_field(
			'wpos_xmlrpc_secret',
			__( 'Endpoint secret', 'wposbridge' ),
			function() {
				$opt = get_option( 'wpos' );
				$val = ( ! empty( $opt['secret'] ) ) ? $opt['secret'] : '';
				?>
				<input type='password' class='regular-text ltr' name='wpos[secret]' value='<?php echo esc_attr( $val ); ?>'>
				<?php
			},
			'wpos',
			'wpos_xmlrpc'
		);
	}
}
