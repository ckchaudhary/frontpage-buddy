<?php
/**
 * UltimateMembers user profile integtaion.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Integrations\BBPress;

/**
 * The main plugin class.
 */
class ProfilesHelper {
	use \RecycleBin\FrontPageBuddy\TraitSingleton;

	/**
	 * Initiazlie the singleton object.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'bbp_template_after_user_profile', array( $this, 'show_output' ) );
		add_action( 'bbp_user_edit_after', array( $this, 'show_manage_screen' ) );
	}

	public function show_output() {
		frontpage_buddy()->get_integration( 'bbp_profiles' )->output_frontpage_content( \bbp_get_displayed_user_id() );
	}

	public function show_manage_screen() {
		\RecycleBin\FrontPageBuddy\load_template( 'bbpress/profiles/manage' );
	}
}
