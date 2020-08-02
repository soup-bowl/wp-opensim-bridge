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

		add_action( 'personal_options_update', array( &$this, 'store_fields' ) );
		add_action( 'edit_user_profile_update', array( &$this, 'store_fields' ) );
	}

	/**
	 * Renders the OpenSim input form, with the user's original values if available.
	 *
	 * @param WP_User $user The requesting user object.
	 * @return void Prints to page.
	 */
	public function display_fields( $user ) { ?>
		<h3><?php esc_html_e( 'OpenSimulator Details', 'wposbridge' ); ?></h3>
		<input type="hidden" name="opensimNonce" value="<?php echo esc_html( wp_create_nonce( 'wposb-user-verify' ) ); ?>" />
		<table class="form-table">
			<tr>
				<th><label for="opensimFirstname"><?php esc_html_e( 'Avatar First Name', 'wposbridge' ); ?></label></th>
				<td>
					<input type="text" name="opensimFirstname" id="opensimFirstname" value="<?php echo esc_attr( get_the_author_meta( 'opensimFirstname', $user->ID ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><label for="opensimLastname"><?php esc_html_e( 'Avatar Last Name', 'wposbridge' ); ?></label></th>
				<td>
					<input type="text" name="opensimLastname" id="opensimLastname" value="<?php echo esc_attr( get_the_author_meta( 'opensimLastname', $user->ID ) ); ?>" class="regular-text" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Stores the provided data changes to the relevant OpenSim fields.
	 *
	 * @param integer $user_id The user ID being updated.
	 * @return boolean|void False on permission check fail, void if successful.
	 */
	public function store_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( isset( $_POST['opensimNonce'], $_POST['opensimFirstname'], $_POST['opensimLastname'] )
		&& wp_verify_nonce( sanitize_key( $_POST['opensimNonce'] ), 'wposb-user-verify' ) ) {
			update_user_meta( $user_id, 'opensimFirstname', sanitize_text_field( wp_unslash( $_POST['opensimFirstname'] ) ) );
			update_user_meta( $user_id, 'opensimLastname', sanitize_text_field( wp_unslash( $_POST['opensimLastname'] ) ) );
		}
	}
}
