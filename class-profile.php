<?php
/**
 * Bridges the user management from WordPress into OpenSimulator.
 *
 * @package wp-os-bridge
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wposbridge;

use WP_User;

/**
 * Profile controls.
 */
class Profile {
	/**
	 * Hook the plugin functionality into WordPress.
	 */
	public function hooks() {
		add_action( 'show_user_profile', array( &$this, 'display_fields' ) );
		add_action( 'edit_user_profile', array( &$this, 'display_fields' ) );
	}

	/**
	 * Renders the OpenSim input form, with the user's original values if available.
	 *
	 * @param WP_User $user The requesting user object.
	 * @return void Prints to page.
	 */
	public function display_fields( $user ) {
		$os_firstname  = get_the_author_meta( 'opensimFirstname', $user->ID );
		$os_lastname   = get_the_author_meta( 'opensimLastname', $user->ID );
		$field_disable = ( ! empty( $os_firstname ) && ! empty( $os_lastname ) ) ? 'disabled' : '';
		?>
		<h3><?php esc_html_e( 'OpenSimulator Details', 'wposbridge' ); ?></h3>
		<input type="hidden" name="opensimNonce" value="<?php echo esc_html( wp_create_nonce( 'wposb-user-verify' ) ); ?>" />
		<table class="form-table">
			<tr>
				<th><label for="opensimFirstname"><?php esc_html_e( 'Avatar First Name', 'wposbridge' ); ?></label></th>
				<td>
					<input type="text" name="opensimFirstname" id="opensimFirstname" value="<?php echo esc_attr( $os_firstname ); ?>" class="regular-text" <?php echo esc_attr( $field_disable ); ?> />
				</td>
			</tr>
			<tr>
				<th><label for="opensimLastname"><?php esc_html_e( 'Avatar Last Name', 'wposbridge' ); ?></label></th>
				<td>
					<input type="text" name="opensimLastname" id="opensimLastname" value="<?php echo esc_attr( $os_lastname ); ?>" class="regular-text" <?php echo esc_attr( $field_disable ); ?> />
					<p class='description'><?php esc_html_e( 'Once set, your OpenSim name cannot be changed.', 'wposbridge' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}
}
