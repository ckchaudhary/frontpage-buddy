<?php
/**
 * Add settings screen in member profiles.
 * Show the custom front page if enabled.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy;

defined( 'ABSPATH' ) ? '' : exit();

/**
 * Add settings screen in member profiles.
 * Show the custom front page if enabled.
 */
class MemberProfiles {
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
	public function __construct() {
		$this->subnav_name = __( 'Front Page', 'frontpage-buddy' );

		add_action( 'bp_setup_nav', array( $this, 'bp_setup_nav' ) );

		add_action( 'members_custom_group_boxes', array( $this, 'custom_group_boxes' ) );
	}

	public function bp_setup_nav() {
		$add_nav     = false;
		$enabled_for = frontpage_buddy()->option( 'enabled_for' );
		if ( ! empty( $enabled_for ) && in_array( 'bp_members', $enabled_for ) ) {
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

	public function screen_edit_widgets() {
		if ( ! bp_is_user() ) {
			return false;
		}

		add_action( 'bp_template_title', array( $this, 'edit_widgets_title' ) );
		add_action( 'bp_template_content', array( $this, 'edit_widgets_contents' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	public function edit_widgets_title() {
		echo esc_html( apply_filters( 'frontpage_buddy_member_edit_widgets_title', __( 'Customize your front page', 'frontpage-buddy' ) ) );
	}

	public function edit_widgets_contents() {
		\RecycleBin\FrontPageBuddy\load_template( frontpage_buddy()->bp_member_profiles()->get_component_type() . '/manage' );
	}

	public function custom_group_boxes() {
		frontpage_buddy()->bp_member_profiles()->output_frontpage_content( bp_displayed_user_id() );
	}
}
