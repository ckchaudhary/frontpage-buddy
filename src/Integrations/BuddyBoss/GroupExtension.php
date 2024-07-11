<?php
/**
 * Add settings screen in buddypress groups.
 * Show the custom front page if enabled.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Integrations\BuddyBoss;

defined( 'ABSPATH' ) ? '' : exit();

/**
 * Add settings screen in buddypress groups.
 * Show the custom front page if enabled.
 */
class GroupExtension extends \BP_Group_Extension {
	/**
	 * Constructor
	 */
	public function __construct() {
		$integration = frontpage_buddy()->get_integration( 'buddyboss_groups' );

		$enable_nav_item = false;
		$group_id = bp_get_current_group_id();
		if ( $group_id ) {
			$enable_nav_item = $integration->has_custom_front_page( $group_id );
		}

		$args = array(
			'enable_nav_item'   => $enable_nav_item,
			'nav_item_position' => 7,

			'slug' => $integration->get_option( 'frontpage_nav_slug' ),
			'name' => $integration->get_option( 'frontpage_nav_name' ),

			'screens' => array(
				'edit'   => array(
					'enabled'              => true,
					'slug'                 => $integration->get_option( 'settings_nav_slug' ),
					'name'                 => $integration->get_option( 'settings_nav_name' ),
					'position'             => 55,
					'screen_callback'      => array( $this, 'settings_screen' ),
					'screen_save_callback' => array( $this, 'settings_screen_save' ),
				),
				'create' => array( 'enabled' => false ),
				'admin'  => array( 'enabled' => false ),
			),
		);
		parent::init( $args );
	}

	/**
	 * Display the contents of the main tab.
	 *
	 * @param int $group_id id of the group.
	 * @return bool
	 */
	public function display( $group_id = null ) {
		$group_id = \bp_get_current_group_id();
		if ( ! $group_id ) {
			return false;
		}

		$integration = frontpage_buddy()->get_integration( 'buddyboss_groups' );
		if ( $integration ) {
			if ( $integration->can_manage( $group_id ) ) {
				// Show prompt?
				if ( 'yes' === $integration->get_option( 'show_encourage_prompt' ) ) {
					$prompt_text = $integration->get_option( 'encourage_prompt_text' );
					if ( $prompt_text ) {

						$manage_url = '';
						if ( function_exists( '\bp_get_group_manage_url' ) ) {
							$manage_url = bp_get_group_manage_url( $group_id );
						} else {
							$manage_url = bp_get_group_permalink( groups_get_current_group() ) . 'admin';
						}
						$manage_url = trailingslashit( $manage_url ) . $integration->get_option( 'settings_nav_slug' ) . '/';

						$manage_link = sprintf(
							'<a href="%s">%s</a>',
							esc_attr( $manage_url ),
							__( 'here', 'frontpage-buddy' )
						);
						$prompt_text = str_replace( '{{LINK}}', $manage_link, $prompt_text );
						echo '<div class="frontpage-buddy-prompt prompt-info"><div class="frontpage-buddy-prompt-content">';
						// Allow html as it is provided by admins.
						// phpcs:ignore WordPress.Security.EscapeOutput
						echo $prompt_text;
						echo '</div></div>';
					}
				}
			}

			// Show widgets output.
			$integration->output_frontpage_content( $group_id );
		}
	}

	/**
	 * Display the settings sceen.
	 *
	 * @param int $group_id group id.
	 * @return void
	 */
	public function settings_screen( $group_id = null ) {
		\RecycleBin\FrontPageBuddy\load_template( 'buddyboss/groups/manage' );
	}

	/**
	 * Save the information on settings screen.
	 *
	 * @param int $group_id group id.
	 * @return void
	 */
	public function settings_screen_save( $group_id = null ) {
		// Nothing here, as the settings are saved via ajax.
	}
}
