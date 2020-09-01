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
		add_action( 'user_new_form', array( &$this, 'display_fields' ) );
	}

	/**
	 * Renders the OpenSim input form, with the user's original values if available.
	 *
	 * @param WP_User $user The requesting user object.
	 * @return void Prints to page.
	 */
	public function display_fields( $user = null ) {
		if ( is_string( $user ) && 'add-new-user' === $user ) {
			$user = null;
		}

		$os_firstname   = ( isset( $user ) ) ? get_the_author_meta( 'opensim_firstname', $user->ID ) : null;
		$os_lastname    = ( isset( $user ) ) ? get_the_author_meta( 'opensim_lastname', $user->ID ) : null;
		$lastnames      = get_option( 'wpos' )['allowedlastnames'];
		$allowed_lnames = ( ! empty( $lastnames ) ) ? explode( ',', $lastnames ) : null;
		$field_disable  = ( ! empty( $os_firstname ) && ! empty( $os_lastname ) ) ? 'disabled' : '';
		?>
		<h3><?php esc_html_e( 'OpenSimulator Details', 'wposbridge' ); ?></h3>
		<p><?php esc_html_e( 'To create or change your OpenSimulator account, please generate a new password. This can be set to your current password.', 'wposbridge' ); ?></p>
		<input type="hidden" name="opensim_nonce" value="<?php echo esc_html( wp_create_nonce( 'wposb-user-verify' ) ); ?>" />
		<table class="form-table">
			<tr>
				<th><label for="opensim_firstname"><?php esc_html_e( 'Avatar First Name', 'wposbridge' ); ?></label></th>
				<td>
					<input type="text" name="opensim_firstname" value="<?php echo esc_attr( $os_firstname ); ?>" class="regular-text" <?php echo esc_attr( $field_disable ); ?> />
				</td>
			</tr>
			<tr>
				<th><label for="opensim_lastname"><?php esc_html_e( 'Avatar Last Name', 'wposbridge' ); ?></label></th>
				<td>
					<?php if ( ! empty( $allowed_lnames ) && empty( $os_lastname ) ) : ?>
					<select name="opensim_lastname" style="width:15em">
						<?php foreach ( $allowed_lnames as $last_name ) : ?>
							<option><?php echo esc_attr( trim( $last_name ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php else : ?>
					<input type="text" name="opensim_lastname" value="<?php echo esc_attr( $os_lastname ); ?>" class="regular-text" <?php echo esc_attr( $field_disable ); ?> />
					<?php endif; ?>
					<p class='description'><?php esc_html_e( 'Once set, your OpenSimulator name cannot be changed.', 'wposbridge' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}
}
