<?php
/**
 * UltimateMembers user profile integtaion.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Integrations\UltimateMember;

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
		add_action( 'um_profile_content_main', array( $this, 'um_profile_content_main' ) );
	}

	/**
	 * Load the manage-widgets screen or show the widgets ouptut.
	 *
	 * @return void
	 */
	public function um_profile_content_main() {
		if ( um_is_on_edit_profile() ) {
			\RecycleBin\FrontPageBuddy\load_template( 'ultimate-member/profiles/manage' );
		} else {
			frontpage_buddy()->get_integration( 'um_member_profiles' )->output_frontpage_content( (int) UM()->user()->target_id );
		}
	}
}
