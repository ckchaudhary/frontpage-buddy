<?php
/**
 * Add settings screen in member profiles.
 * Show the custom front page if enabled.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace FrontPageBuddy\Integrations\BuddyPress;

defined( 'ABSPATH' ) || exit;

/**
 * Add settings screen in member profiles.
 * Show the custom front page if enabled.
 */
class MemberProfilesHelper {
	use \FrontPageBuddy\TraitSingleton;

	/**
	 * Name of the subnav item
	 *
	 * @var string
	 */
	protected $subnav_name = '';

	/**
	 * Slug of subnav item
	 *
	 * @var string
	 */
	protected $subnav_slug = 'front-page';

	/**
	 * Constructor
	 */
	protected function init() {
		$this->subnav_name = __( 'Front Page', 'frontpage-buddy' );

		add_action( 'bp_setup_nav', array( $this, 'bp_setup_nav' ) );

		add_filter( 'is_active_sidebar', array( $this, 'is_buddypress_members_sidebar_active' ), 20, 2 );
		add_action( 'dynamic_sidebar_after', array( $this, 'after_buddypress_members_sidebar' ), 20 );

		add_action( 'template_redirect', array( $this, 'maybe_redirect_empty_frontpage' ) );
	}

	/**
	 * Add navigation links.
	 * One is added under members>xyz>settings.
	 *
	 * @return void
	 */
	public function bp_setup_nav() {
		$add_nav     = false;
		$enabled_for = frontpage_buddy()->option( 'enabled_for' );
		if ( ! empty( $enabled_for ) && in_array( 'bp_members', $enabled_for, true ) ) {
			$add_nav = true;
		}

		if ( ! $add_nav ) {
			return;
		}

		// Get the settings slug.
		$settings_slug = bp_get_settings_slug();

		bp_core_new_subnav_item(
			array(
				'name'            => $this->subnav_name,
				'slug'            => $this->subnav_slug,
				'parent_url'      => trailingslashit( bp_displayed_user_domain() . $settings_slug ),
				'parent_slug'     => $settings_slug,
				'screen_function' => array( $this, 'screen_edit_widgets' ),
				'position'        => 29,
				'user_has_access' => bp_core_can_edit_settings(),
			),
			'members'
		);

		return false;
	}

	/**
	 * Function to handle the output for the new subnav item added under settings.
	 *
	 * @return mixed
	 */
	public function screen_edit_widgets() {
		if ( ! bp_is_user() ) {
			return false;
		}

		add_action( 'bp_template_title', array( $this, 'edit_widgets_title' ) );
		add_action( 'bp_template_content', array( $this, 'edit_widgets_contents' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Title for the new subnav item added under settings.
	 *
	 * @return void
	 */
	public function edit_widgets_title() {
		echo esc_html( apply_filters( 'frontpage_buddy_member_edit_widgets_title', __( 'Customize your front page', 'frontpage-buddy' ) ) );
	}

	/**
	 * Content for the new subnav item added under settings.
	 *
	 * @return void
	 */
	public function edit_widgets_contents() {
		\FrontPageBuddy\load_template( 'buddypress/profiles/manage' );
	}

	/**
	 * Filters whether a dynamic sidebar is considered "active".
	 *
	 * @param bool       $is_active Whether or not the sidebar should be considered "active".
	 *                                      In other words, whether the sidebar contains any widgets.
	 * @param int|string $index     Index, name, or ID of the dynamic sidebar.
	 * @return boolean
	 */
	public function is_buddypress_members_sidebar_active( $is_active, $index ) {
		if ( $is_active ) {
			return $is_active;// No need to check anything.
		}

		if ( 'sidebar-buddypress-members' !== $index ) {
			return $is_active;// No need to check anything.
		}

		/**
		 * Output of the custom front page is printed inside this sidebar.
		 * So if there are no widgets added to this sidebar, the sidebar is never printed and thus the custom front page content is also not displayed.
		 * So we hijack it and return true even if there are no widgets added to this sidebar.
		 */
		if ( \bp_is_user() ) {
			$integration = frontpage_buddy()->get_integration( 'bp_members' );
			if ( $integration->has_custom_front_page( \bp_displayed_user_id() ) ) {
				$is_active = true;
			}
		}

		return $is_active;
	}

	/**
	 * Show outuput at the end of buddypress members sidebar.
	 *
	 * @param int|string $index Index, name, or ID of the dynamic sidebar.
	 * @return void
	 */
	public function after_buddypress_members_sidebar( $index ) {
		if ( 'sidebar-buddypress-members' === $index ) {
			$integration = frontpage_buddy()->get_integration( 'bp_members' );
			if ( $integration->has_custom_front_page( \bp_displayed_user_id() ) ) {
				$this->print_output( \bp_displayed_user_id() );
			}
		}
	}

	/**
	 * Print the output for custom front page widgets.
	 *
	 * @param int $user_id id of the user. Default bp_displayed_user_id().
	 * @return bool
	 */
	public function print_output( $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = \bp_displayed_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$integration = frontpage_buddy()->get_integration( 'bp_members' );
		if ( $integration ) {
			if ( $integration->can_manage( $user_id ) ) {
				// Show prompt?
				if ( 'yes' === $integration->get_option( 'show_encourage_prompt' ) ) {
					$prompt_text = $integration->get_option( 'encourage_prompt_text' );
					if ( $prompt_text ) {
						$editor_url  = \bp_members_get_user_url( \bp_displayed_user_id() ) . 'settings/' . $this->subnav_slug . '/';
						$prompt_text = str_replace( '{{EDITOR_URL}}', esc_url( $editor_url ), $prompt_text );
						echo '<div class="frontpage-buddy-prompt prompt-info"><div class="frontpage-buddy-prompt-content">';
						echo wp_kses( $prompt_text, \FrontPageBuddy\basic_html_allowed_tags() );
						echo '</div></div>';
					}
				}
			}

			// Show widgets output.
			$integration->output_frontpage_content( $user_id );
		}
	}

	/**
	 * If a member hasn't added any content to their front page yet, redirect visitors to another tab of member's profile.
	 * Someone visiting their own profile isn't redirected.
	 * Admins, or anyone else who can edit others profiles, are also not redirected.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function maybe_redirect_empty_frontpage() {
		$integration = frontpage_buddy()->get_integration( 'bp_members' );

		if ( ! $integration->is_custom_front_page_screen() ) {
			return;
		}

		if ( $integration->can_manage( bp_displayed_user_id() ) ) {
			return;
		}
		if ( ! empty( $integration->get_added_widgets( bp_displayed_user_id() ) ) ) {
			return;
		}

		$redirect_to = $integration->get_option( 'redirect_when_empty' );
		if ( empty( $redirect_to ) || 'none' === $redirect_to ) {
			return;
		}

		wp_safe_redirect( bp_displayed_user_url( bp_members_get_path_chunks( array( $redirect_to ) ) ) );
		exit;
	}
}
