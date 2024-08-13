<?php
/**
 * Add settings screen in buddypress groups.
 * Show the custom front page if enabled.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Integrations\BuddyPress;

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
					'name'                 => __( 'Front Page', 'frontpage-buddy' ),
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
	 * Generate the output for settings screen.
	 *
	 * @param int $group_id group id.
	 * @return void
	 */
	public function settings_screen( $group_id = null ) {
		\RB\FrontPageBuddy\load_template( 'buddypress/groups/manage' );
	}

	/**
	 * Save settings screen.
	 *
	 * @param int $group_id group id.
	 * @return void
	 */
	public function settings_screen_save( $group_id = null ) {
		// Nothing here.
	}

	/**
	 * Print the output for custom front page widgets.
	 *
	 * @return void
	 */
	public function custom_group_boxes() {
		$integration = frontpage_buddy()->get_integration( 'bp_groups' );
		if ( $integration ) {
			if ( $integration->can_manage( \bp_get_current_group_id() ) ) {
				// Show prompt?
				if ( 'yes' === $integration->get_option( 'show_encourage_prompt' ) ) {
					$prompt_text = $integration->get_option( 'encourage_prompt_text' );
					if ( $prompt_text ) {
						$manage_link = sprintf(
							'<a href="%s">%s</a>',
							bp_get_group_manage_url( \bp_get_current_group_id() ) . $this->subnav_slug . '/',
							__( 'here', 'frontpage-buddy' )
						);
						$prompt_text = str_replace( '{{LINK}}', $manage_link, $prompt_text );
						echo '<div class="frontpage-buddy-prompt prompt-info"><div class="frontpage-buddy-prompt-content">';
						echo wp_kses( $prompt_text, \RB\FrontPageBuddy\basic_html_allowed_tags() );
						echo '</div></div>';
					}
				}
			}

			// Show widgets output.
			$integration->output_frontpage_content( bp_get_current_group_id() );
		}
	}
}
