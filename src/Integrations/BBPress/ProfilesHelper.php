<?php
/**
 * UltimateMembers user profile integtaion.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Integrations\BBPress;

defined( 'ABSPATH' ) || exit;

/**
 * The main plugin class.
 */
class ProfilesHelper {
	use \RB\FrontPageBuddy\TraitSingleton;

	/**
	 * Initiazlie the singleton object.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'bbp_template_after_user_profile', array( $this, 'show_output' ) );
		add_action( 'bbp_user_edit_after', array( $this, 'show_manage_screen' ) );
	}

	/**
	 * Print the output for custom front page widgets.
	 *
	 * @return void
	 */
	public function show_output() {
		$user_id     = \bbp_get_displayed_user_id();
		$integration = frontpage_buddy()->get_integration( 'bbp_profiles' );
		if ( $integration->can_manage( $user_id ) ) {
			// Show prompt?
			if ( 'yes' === $integration->get_option( 'show_encourage_prompt' ) ) {
				$prompt_text = $integration->get_option( 'encourage_prompt_text' );
				if ( $prompt_text ) {
					$manage_link = sprintf(
						'<a href="%s">%s</a>',
						\bbp_get_user_profile_edit_url( $user_id ),
						__( 'here', 'frontpage-buddy' )
					);
					$prompt_text = str_replace( '{{LINK}}', $manage_link, $prompt_text );
					echo '<div class="frontpage-buddy-prompt prompt-info"><div class="frontpage-buddy-prompt-content">';
					echo wp_kses( $prompt_text, \RB\FrontPageBuddy\basic_html_allowed_tags() );
					echo '</div></div>';
				}
			}
		}

		frontpage_buddy()->get_integration( 'bbp_profiles' )->output_frontpage_content( $user_id );
	}

	/**
	 * Load the manage-widgets screen.
	 *
	 * @return void
	 */
	public function show_manage_screen() {
		\RB\FrontPageBuddy\load_template( 'bbpress/profiles/manage' );
	}
}
