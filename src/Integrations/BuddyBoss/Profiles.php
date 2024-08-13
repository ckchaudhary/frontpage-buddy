<?php
/**
 * Front page for buddyboss member profiles.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Integrations\BuddyBoss;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Front page for buddypress member profiles.
 */
class Profiles extends \RB\FrontPageBuddy\Integration {

	/**
	 * Is the front page enabled for all members by default?
	 *
	 * @var bool
	 */
	protected $is_fp_enabled_for_all = null;

	/**
	 * Path of the file that will be loaded fro front page.
	 *
	 * @var string
	 */
	protected $front_template_file_path = '';

	/**
	 * Is the front page enabled for all members
	 *
	 * @return bool
	 */
	public function is_fp_enabled_for_all() {
		if ( null === $this->is_fp_enabled_for_all ) {
			/*
			* If a file buddypress/members/single/front.php exists anywhere in buddypress template stack,
			* front page is enabled for all members, automatically.
			*/

			$helper = \RB\FrontPageBuddy\Integrations\BuddyBoss\MemberProfilesHelper::get_instance();
			bp_deregister_template_stack( array( $helper, 'register_template_stack' ) );

			$located = bp_locate_template( array( 'members/single/front.php' ), false, false );
			if ( $located ) {
				$this->is_fp_enabled_for_all    = true;
				$this->front_template_file_path = $located;
			}

			bp_register_template_stack( array( $helper, 'register_template_stack' ) );
		}

		return apply_filters( 'frontpage_buddy_buddyboss_members_is_enabled_for_all', $this->is_fp_enabled_for_all );
	}

	/**
	 * Get the front page template file path.
	 *
	 * @return string
	 */
	public function get_front_template_file_path() {
		return $this->front_template_file_path;
	}

	/**
	 * Get details about this integration, to be displayed in admin settings screen.
	 *
	 * @return string
	 */
	public function get_admin_description() {
		$html = '<p>' . esc_html__( 'This enables all members of your buddyboss site to customize their front page.', 'frontpage-buddy' ) . '</p>';

		return $html;
	}

	/**
	 * Get the fields for specific settings for this integration, if any.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		$attrs_show_prompt = array();
		if ( 'yes' === $this->get_option( 'show_encourage_prompt' ) ) {
			$attrs_show_prompt['checked'] = 'checked';
		}

		return array(
			'frontpage_nav_title'   => array(
				'type'        => 'text',
				'label'       => __( 'Front page menu name', 'frontpage-buddy' ),
				'value'       => $this->get_option( 'frontpage_nav_title' ),
				'description' => __( 'Name of the member profile menu item which displays the front page.', 'frontpage-buddy' ),
			),
			'settings_nav_title'    => array(
				'type'        => 'text',
				'label'       => __( 'Settings menu name', 'frontpage-buddy' ),
				'value'       => $this->get_option( 'settings_nav_title' ),
				'description' => __( 'Name of the sub menu item which is added under \'Account\' main menu. This is the screen from where members can customize thier front page.', 'frontpage-buddy' ),
			),
			'settings_nav_slug'     => array(
				'type'         => 'text',
				'label'        => __( 'Settings menu slug', 'frontpage-buddy' ),
				'value'        => $this->get_option( 'settings_nav_slug' ),
				'description'  => __( 'Slug of the sub menu item which is added under \'Account\' main menu. This is the screen from where members can customize thier front page.', 'frontpage-buddy' ),
				'sanitization' => 'slug',
			),
			'show_encourage_prompt' => array(
				'type'        => 'switch',
				'label'       => __( 'Show prompt when viewing one\'s own profile?', 'frontpage-buddy' ),
				'label_off'   => __( 'No', 'frontpage-buddy' ),
				'label_on'    => __( 'Yes', 'frontpage-buddy' ),
				'attributes'  => $attrs_show_prompt,
				'description' => __( 'If enabled, when a member visits their profile, they see a small prompt at the top. This can be used to encourage members to add content to their front page. This can also be used to add a link to the page where the member can customize their front page.', 'frontpage-buddy' ),
			),
			'encourage_prompt_text' => array(
				'type'         => 'textarea',
				'label'        => __( 'Prompt text', 'frontpage-buddy' ),
				'value'        => $this->get_option( 'encourage_prompt_text' ),
				'description'  => __( 'The text to be displayed inside the aforementioned prompt. You can use the placeholder {{LINK}} which will automatically be replaced with a link to the page where the member can customize their front page.', 'frontpage-buddy' ),
				'attributes'   => array(
					'rows' => 3,
					'cols' => 50,
				),
				'sanitization' => 'basic_html',
			),
		);
	}

	/**
	 * Get an option's/setting's default value.
	 * This function is to be overloaded by integrations.
	 *
	 * @param mixed                          $option_value value of the option.
	 * @param string                         $option_name  name of the option.
	 * @param \RB\FrontPageBuddy\Integration $integration  integration object.
	 *
	 * @return mixed null if no default value is to be provided.
	 */
	public function filter_option_value( $option_value, $option_name, $integration ) {
		if ( $integration->type !== $this->type ) {
			return $option_value;
		}

		switch ( $option_name ) {
			case 'frontpage_nav_title':
				$option_value = null !== $option_value ? trim( $option_value ) : '';
				if ( empty( $option_value ) ) {
					$option_value = __( 'Welcome', 'frontpage-buddy' );
				}
				break;

			case 'settings_nav_title':
				$option_value = null !== $option_value ? trim( $option_value ) : '';
				if ( empty( $option_value ) ) {
					$option_value = __( 'Front Page', 'frontpage-buddy' );
				}
				break;

			case 'settings_nav_slug':
				$option_value = null !== $option_value ? trim( $option_value ) : '';
				if ( empty( $option_value ) ) {
					$option_value = 'front-page';
				}
				break;

			case 'show_encourage_prompt':
				if ( 'yes' !== $option_value ) {
					$option_value = 'no';
				}
				break;

			case 'encourage_prompt_text':
				$option_value = null !== $option_value ? trim( $option_value ) : '';
				if ( empty( $option_value ) ) {
					$option_value = sprintf(
						/* translators: 1: {{LINK}} . In front end, this gets replaced by <a href='..'>here</a> */
						__( 'Customize your profile\'s front page by going %s.', 'frontpage-buddy' ),
						'{{LINK}}'
					);
				}
				break;
		}

		return $option_value;
	}

	/**
	 * Get/set If the current object has a custom front page.
	 *
	 * @param int    $object_id Id of member or group.
	 * @param string $set 'no' or 'yes'. Default false.
	 *
	 * @return boolean
	 */
	public function has_custom_front_page( $object_id, $set = false ) {
		if ( false !== $set ) {
			\bp_update_user_meta( $object_id, '_has_custom_frontpage', $set );
		}

		return 'yes' === \bp_get_user_meta( $object_id, '_has_custom_frontpage', true );
	}

	/**
	 * Is the current request a widget edit/manage screen.
	 *
	 * @param boolean $flag Passed when this is hooked to a filter.
	 * @return boolean
	 */
	public function is_widgets_edit_screen( $flag = false ) {
		$slug = $this->get_option( 'settings_nav_slug' );
		if ( bp_is_user() && 'settings' === bp_current_component() && bp_current_action() === $slug ) {
			$flag = true;
		}

		return $flag;
	}

	/**
	 * Is the current request a custom front page screen.
	 *
	 * @param boolean $flag Passed when this is hooked to a filter.
	 * @return boolean
	 */
	public function is_custom_front_page_screen( $flag = false ) {
		if ( bp_is_user() && 'front' === bp_current_component() ) {
			$flag = true;
		}

		return $flag;
	}

	/**
	 * When on manage widget screen, get the id of the object being edited.
	 * E.g: current user id, group id etc.
	 *
	 * @return mixed
	 */
	public function get_editable_object_id() {
		return bp_displayed_user_id();
	}

	/**
	 * Get the layout for front page.
	 *
	 * @param int $object_id Id of the member.
	 * @return string
	 */
	public function get_frontpage_layout( $object_id ) {
		return bp_get_user_meta( $object_id, '_frontpage_buddy_page_layout', true );
	}

	/**
	 * Update the layout for front page.
	 *
	 * @param int    $object_id Id of the member.
	 * @param string $data html.
	 * @return void
	 */
	public function update_frontpage_layout( $object_id, $data = '' ) {
		bp_update_user_meta( $object_id, '_frontpage_buddy_page_layout', $data );
	}

	/**
	 * Get the details of all individual widgets added for given object.
	 *
	 * @param int $object_id Id of the object(member/group).
	 * @return array
	 */
	public function get_added_widgets( $object_id ) {
		$all = bp_get_user_meta( $object_id, '_frontpage_buddy_added_widgets', true );
		return ! empty( $all ) ? $all : array();
	}

	/**
	 * Update the details of all individual widgets added for given object.
	 *
	 * @param int   $object_id Id of the object(member/group).
	 * @param array $data {
	 *    List of widgets.
	 *    @type string $id id of the widget.
	 *    @type string $type type of widget.
	 *    @type array  $options key value pair of options.
	 * }
	 * @return void
	 */
	public function update_added_widgets( $object_id, $data = array() ) {
		bp_update_user_meta( $object_id, '_frontpage_buddy_added_widgets', $data );
	}

	/**
	 * Can the current user manage given member
	 *
	 * @param int $object_id group id or user id.
	 *
	 * @return boolean
	 */
	public function can_manage( $object_id ) {
		$can_manage = get_current_user_id() === $object_id;

		if ( ! $can_manage && bp_current_user_can( 'bp_moderate' ) ) {
			$can_manage = true;
		}

		return apply_filters( 'frontpage_buddy_can_manage', $can_manage, $this->get_integration_type(), $object_id );
	}
}
