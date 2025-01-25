<?php
/**
 * Add settings screen in member profiles.
 * Show the custom front page if enabled.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace FrontPageBuddy\Integrations\BuddyBoss;

defined( 'ABSPATH' ) || exit;

/**
 * Add settings screen in member profiles.
 * Show the custom front page if enabled.
 */
class MemberProfilesHelper {
	use \FrontPageBuddy\TraitSingleton;
	use \FrontPageBuddy\TraitGetSet;

	/**
	 * Was the content displayed at least once?
	 *
	 * @var boolean
	 */
	protected $content_displayed = false;

	/**
	 * Slug for the front page component.
	 *
	 * @var string
	 */
	protected $fp_component_slug = 'front';

	/**
	 * Constructor
	 */
	protected function init() {
		// Add manage front page screen.
		add_action( 'bp_setup_nav', array( $this, 'bp_setup_nav' ) );

		// Change 'Dashboard' to 'Home'.
		add_filter( 'bp_core_create_nav_link', array( $this, 'change_fp_nav' ) );

		// If enabled, make front page the default component.
		add_filter( 'bp_member_default_component', array( $this, 'set_fp_as_default_component' ) );

		/**
		 * If active theme( or child theme ) has a buddypress/members/single/front.php file,
		 * buddyboss assumes that front pages are enabled for all members.
		 * So by design, that file doesn't exist.
		 *
		 * If however, you add that file in your theme, frontpage gets enabled for all members by default.
		 */
		if ( function_exists( '\bp_register_template_stack' ) ) {
			/**
			 * Front page is not enabled by default.
			 *  1. frontpage-buddy/templates/buddypress/members/single folder has a front.php file.
			 *     FrontPage Buddy adds the folder frontpage-buddy/templates to the template stack, which turns on the front page for all members.
			 *  2. Later on, it is checked if the displayed user has opted to enable a custom front page.
			 *     If not, the folder frontpage-buddy/templates is removed from buddypress' template stack,
			 *     which disables the front page for displayed user.
			 *
			 * The priority is 100, so that frontpage's templates are at the bottom of stack.
			 * Theme and other plugins get priority.
			 */
			\bp_register_template_stack( array( $this, 'register_template_stack' ), 100 );
			add_filter( 'bp_get_template_stack', array( $this, 'maybe_remove_template_stack' ) );
		}

		// Ouput the frontpage content.
		add_action( 'bp_after_member_front_template', array( $this, 'on_template_content' ) );
		add_action( 'bp_template_content', array( $this, 'on_template_content' ) );
	}

	/**
	 * Add this plugin's templates folder in buddyboss' template stack.
	 *
	 * @return string
	 */
	public function register_template_stack() {
		return FRONTPAGE_BUDDY_PLUGIN_DIR . 'templates/bb-buddypress';
	}

	/**
	 * Conditionally, remove this plugin's templates folder from buddyboss' template stack.
	 *
	 * @param array $stack List of folder paths.
	 * @return array
	 */
	public function maybe_remove_template_stack( $stack ) {
		$need_template_stack = false;
		$enabled_for         = frontpage_buddy()->option( 'enabled_for' );

		if ( bp_is_user() && ! empty( $enabled_for ) && in_array( 'buddyboss_members', $enabled_for, true ) ) {
			// Does the current user want to have a custom front page template?
			$integration = frontpage_buddy()->get_integration( 'buddyboss_members' );

			if ( $integration && $integration->has_custom_front_page( bp_displayed_user_id() ) ) {
				$need_template_stack = true;
			}
		}

		if ( ! $need_template_stack ) {
			// Remove this plugin's template stack.
			$new_stack = array();
			foreach ( $stack as $filepath ) {
				if ( strpos( $filepath, FRONTPAGE_BUDDY_PLUGIN_DIR ) === false ) {
					$new_stack[] = $filepath;
				}
			}

			return $new_stack;
		}

		return $stack;
	}

	/**
	 * Add navigation links.
	 * One is added under members>xyz>settings.
	 *
	 * @return void
	 */
	public function bp_setup_nav() {
		$integration = frontpage_buddy()->get_integration( 'buddyboss_members' );

		// Get the settings slug.
		$settings_slug = bp_get_settings_slug();

		bp_core_new_subnav_item(
			array(
				'name'            => $integration->get_option( 'settings_nav_title' ),
				'slug'            => $integration->get_option( 'settings_nav_slug' ),
				'parent_url'      => trailingslashit( bp_displayed_user_domain() . $settings_slug ),
				'parent_slug'     => $settings_slug,
				'screen_function' => array( $this, 'screen_manage_fp' ),
				'position'        => 29,
				'user_has_access' => bp_core_can_edit_settings(),
			),
			'members'
		);
	}

	/**
	 * Function to handle the output for the new subnav item added under settings.
	 *
	 * @return mixed
	 */
	public function screen_manage_fp() {
		if ( ! bp_is_user() ) {
			return false;
		}

		add_action( 'bp_template_title', array( $this, 'screen_manage_fp_title' ) );
		add_action( 'bp_template_content', array( $this, 'screen_manage_fp_contents' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Title for the new subnav item added under settings.
	 *
	 * @return void
	 */
	public function screen_manage_fp_title() {
		echo esc_html( apply_filters( 'frontpage_buddy_member_edit_widgets_title', __( 'Customize your front page', 'frontpage-buddy' ) ) );
	}

	/**
	 * Content for the new subnav item added under settings.
	 *
	 * @return void
	 */
	public function screen_manage_fp_contents() {
		\FrontPageBuddy\load_template( 'buddyboss/profiles/manage' );
	}

	/**
	 * Conditionally, print output.
	 *
	 * @return boolean
	 */
	public function on_template_content() {
		if ( $this->content_displayed ) {
			return false;
		}

		$user_id = \bp_displayed_user_id();
		if ( ! $user_id ) {
			return false;
		}

		$is_fp = false;
		if ( bp_current_component() === $this->fp_component_slug ) {
			$current_action = bp_current_action();
			if ( ! $current_action || 'public' === $current_action ) {
				$is_fp = true;
			}
		}

		if ( ! $is_fp ) {
			return false;
		}

		$integration = frontpage_buddy()->get_integration( 'buddyboss_members' );
		if ( $integration->has_custom_front_page( $user_id ) ) {
			$this->print_output( $user_id );
			$this->content_displayed = true;
		}

		return true;
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

		$integration = frontpage_buddy()->get_integration( 'buddyboss_members' );
		if ( $integration ) {
			if ( $integration->can_manage( $user_id ) ) {
				// Show prompt?
				if ( 'yes' === $integration->get_option( 'show_encourage_prompt' ) ) {
					$prompt_text = $integration->get_option( 'encourage_prompt_text' );
					if ( $prompt_text ) {
						$manage_link = sprintf(
							'<a href="%s">%s</a>',
							\bp_displayed_user_domain() . bp_get_settings_slug() . '/' . $integration->get_option( 'settings_nav_slug' ) . '/',
							__( 'here', 'frontpage-buddy' )
						);
						$prompt_text = str_replace( '{{LINK}}', $manage_link, $prompt_text );
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
	 * Change the name of front page nav.
	 *
	 * @param array $nav Details of the nav item.
	 * @return array
	 */
	public function change_fp_nav( $nav ) {
		if ( $this->fp_component_slug !== $nav['slug'] ) {
			return $nav;
		}

		$integration = frontpage_buddy()->get_integration( 'buddyboss_members' );
		$nav['name'] = $integration->get_option( 'frontpage_nav_title' );
		return $nav;
	}

	/**
	 * If the member has enabled, set the frontpage as the default component.
	 *
	 * @param string $component Existing default component.
	 * @return string
	 */
	public function set_fp_as_default_component( $component ) {
		$user_id = \bp_displayed_user_id();
		if ( ! $user_id ) {
			return $component;
		}

		$integration = frontpage_buddy()->get_integration( 'buddyboss_members' );
		if ( $integration->has_custom_front_page( $user_id ) ) {
			$component = $this->fp_component_slug;
		}

		return $component;
	}
}
