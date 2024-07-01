<?php
/**
 * Add settings screen in buddypress groups.
 * Show the custom front page if enabled.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Integrations\BuddyPress;

defined( 'ABSPATH' ) ? '' : exit();

/**
 * Add settings screen in buddypress groups.
 * Show the custom front page if enabled.
 */
class GroupExtension extends \BP_Group_Extension {

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

		$args = array(
			'enable_nav_item' => true,
			'screens'         => array(
				'edit'   => array(
					'enabled'              => function_exists( '\bp_nouveau_get_appearance_settings' ) && bp_nouveau_get_appearance_settings( 'group_front_page' ),
					'slug'                 => 'front-page',
					'name'                 => __( 'Front Page', 'bp-landing-pages' ),
					'position'             => 55,
					'screen_callback'      => array( $this, 'settings_screen' ),
					'screen_save_callback' => array( $this, 'settings_screen_save' ),
				),
				'create' => array( 'enabled' => false ),
				'admin'  => array( 'enabled' => false ),
			),
		);
		parent::init( $args );

		add_action( 'groups_custom_group_boxes', array( $this, 'custom_group_boxes' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @param int $group_id group id.
	 * @return void
	 */
	public function settings_screen( $group_id = null ) {
		\RecycleBin\FrontPageBuddy\load_template( 'buddypress/groups/manage' );
	}

	/**
	 * Undocumented function
	 *
	 * @param int $group_id group id.
	 * @return void
	 */
	public function settings_screen_save( $group_id = null ) {
		// Nothing here.
	}

	public function custom_group_boxes() {
		frontpage_buddy()->get_integration( 'bp_groups' )->output_frontpage_content( bp_get_current_group_id() );
	}
}
