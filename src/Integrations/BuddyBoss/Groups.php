<?php
/**
 * Front page for buddypress groups.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Integrations\BuddyBoss;

defined( 'ABSPATH' ) || exit;

/**
 *  Front page for buddypress groups.
 */
class Groups extends \RB\FrontPageBuddy\Integration {
	/**
	 * Constructor
	 *
	 * @param string $type type of the integration.
	 * @param string $name Name. Optional.
	 *
	 * @return void
	 */
	public function __construct( $type, $name = '' ) {
		parent::__construct( $type, $name );

		/**
		 * This must be hooked in pretty early.
		 * And so we can't add this in GroupExtension's constructor.
		 */
		add_filter( 'bp_groups_default_extension', array( $this, 'set_fp_as_default_extension' ) );
	}
	/**
	 * Get details about this integration, to be displayed in admin settings screen.
	 *
	 * @return string
	 */
	public function get_admin_description() {
		$html = '<p>' . __( 'This enables administrators of all BuddyPress groups to customize their group\'s front page.', 'frontpage-buddy' ) . '</p>';

		if ( ! \bp_is_active( 'groups' ) ) {
			$html .= '<p>';
			$html .= '<span class="notice notice-error inline">' . esc_html__( 'Groups are not enabled.', 'frontpage-buddy' ) . '</span> ';
			$html .= esc_html__( 'Enabling this integration will have no effect.', 'frontpage-buddy' );
			$html .= '</p>';
		}

		return $html;
	}

	/**
	 * Get the fields for specific settings for this integration, if any.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		// There is no point in giving any settings options if groups aren't enabled yet.
		if ( ! \bp_is_active( 'groups' ) ) {
			return array();
		}

		$attrs_show_prompt = array();
		if ( 'yes' === $this->get_option( 'show_encourage_prompt' ) ) {
			$attrs_show_prompt['checked'] = 'checked';
		}

		return array(
			'frontpage_nav_name'    => array(
				'type'        => 'text',
				'label'       => __( 'Front page menu name', 'frontpage-buddy' ),
				'value'       => $this->get_option( 'frontpage_nav_name' ),
				'description' => __( 'Name of the group\'s menu item which displays the front page.', 'frontpage-buddy' ),
			),
			'frontpage_nav_slug'    => array(
				'type'         => 'text',
				'label'        => __( 'Front page menu slug', 'frontpage-buddy' ),
				'value'        => $this->get_option( 'frontpage_nav_slug' ),
				'description'  => __( 'Slug of the group\'s menu item which displays the front page.', 'frontpage-buddy' ),
				'sanitization' => 'slug',
			),
			'settings_nav_name'     => array(
				'type'        => 'text',
				'label'       => __( 'Settings menu name', 'frontpage-buddy' ),
				'value'       => $this->get_option( 'settings_nav_name' ),
				'description' => __( 'Name of the sub menu item which is added under \'Manage\' main menu. This is the screen from where admins can customize group\'s front page.', 'frontpage-buddy' ),
			),

			/*
			This is used to create css classes for body element.
			That css class is used to hide the default group settings form.
			So making it editable will break that small functionality.
			Untill we find a good solution for that, this setting is disabled.

			'settings_nav_slug'     => array(
				'type'         => 'text',
				'label'        => __( 'Settings menu slug', 'frontpage-buddy' ),
				'value'        => $this->get_option( 'settings_nav_slug' ),
				'description'  => __( 'Slug of the sub menu item which is added under \'Manage\' main menu. This is the screen from where admins can customize group\'s front page.', 'frontpage-buddy' ),
				'sanitization' => 'slug',
			),
			*/

			'show_encourage_prompt' => array(
				'type'        => 'switch',
				'label'       => __( 'Show prompt to group admins when viewing group\'s front page?', 'frontpage-buddy' ),
				'label_off'   => __( 'No', 'frontpage-buddy' ),
				'label_on'    => __( 'Yes', 'frontpage-buddy' ),
				'attributes'  => $attrs_show_prompt,
				'description' => __( 'If enabled, when a group admin visits the front page of the group, they see a small prompt at the top. This can be used to encourage group admins to add content to the front page. This can also be used to add a link to the page where the front page can be customized.', 'frontpage-buddy' ),
			),
			'encourage_prompt_text' => array(
				'type'         => 'textarea',
				'label'        => __( 'Prompt text', 'frontpage-buddy' ),
				'value'        => $this->get_option( 'encourage_prompt_text' ),
				'description'  => __( 'The text to be displayed inside the aforementioned prompt. You can use the placeholder {{LINK}} which will automatically be replaced with a link to the page where the front page can be customized.', 'frontpage-buddy' ),
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
			case 'frontpage_nav_name':
				$option_value = null !== $option_value ? trim( $option_value ) : '';
				if ( empty( $option_value ) ) {
					$option_value = __( 'Welcome', 'frontpage-buddy' );
				}
				break;

			case 'frontpage_nav_slug':
				$option_value = null !== $option_value ? trim( $option_value ) : '';
				if ( empty( $option_value ) ) {
					$option_value = 'welcome';
				}
				break;

			case 'settings_nav_name':
				$option_value = null !== $option_value ? trim( $option_value ) : '';
				if ( empty( $option_value ) ) {
					$option_value = __( 'Welcome Page', 'frontpage-buddy' );
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
						__( 'Edit the welcome page by going %s.', 'frontpage-buddy' ),
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
		if ( ! \bp_is_active( 'groups' ) ) {
			return false;
		}

		$integration_enabled = false;
		$enabled_for         = frontpage_buddy()->option( 'enabled_for' );
		if ( ! empty( $enabled_for ) && in_array( $this->get_integration_type(), $enabled_for, true ) ) {
			$integration_enabled = true;
		}

		if ( ! $integration_enabled ) {
			return false;
		}

		if ( false !== $set ) {
			\groups_update_groupmeta( $object_id, '_has_custom_frontpage', $set );
		}

		$flag = 'yes' === \groups_get_groupmeta( $object_id, '_has_custom_frontpage', true );
		return apply_filters( 'frontpage_buddy_has_custom_front_page', $flag, $this->get_integration_type(), $object_id );
	}

	/**
	 * Is the current request a widget edit/manage screen.
	 *
	 * @param boolean $flag Passed when this is hooked to a filter.
	 * @return boolean
	 */
	public function is_widgets_edit_screen( $flag = false ) {
		$slug = $this->get_option( 'settings_nav_slug' );
		if ( bp_is_active( 'groups' ) && bp_is_group() && 'admin' === bp_current_action() && bp_action_variable() === $slug ) {
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
		$slug = $this->get_option( 'frontpage_nav_slug' );
		if ( bp_is_active( 'groups' ) && bp_is_group() && bp_current_action() === $slug ) {
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
		return bp_get_current_group_id();
	}

	/**
	 * Get the layout for front page.
	 *
	 * @param int $object_id Id of the member.
	 * @return string
	 */
	public function get_frontpage_layout( $object_id ) {
		return groups_get_groupmeta( $object_id, '_frontpage_buddy_page_layout', true );
	}

	/**
	 * Update the layout for front page.
	 *
	 * @param int    $object_id Id of the member.
	 * @param string $data html.
	 * @return void
	 */
	public function update_frontpage_layout( $object_id, $data = '' ) {
		groups_update_groupmeta( $object_id, '_frontpage_buddy_page_layout', $data );
	}

	/**
	 * Get the details of all individual widgets added for given object.
	 *
	 * @param int $object_id Id of the object(member/group).
	 * @return array
	 */
	public function get_added_widgets( $object_id ) {
		$all = groups_get_groupmeta( $object_id, '_frontpage_buddy_added_widgets', true );
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
		groups_update_groupmeta( $object_id, '_frontpage_buddy_added_widgets', $data );
	}

	/**
	 * Can the current user manage given group
	 *
	 * @param int $object_id group id or user id.
	 *
	 * @return boolean
	 */
	public function can_manage( $object_id ) {
		$can_manage = false;

		if ( bp_current_user_can( 'bp_moderate' ) ) {
			$can_manage = true;
		} elseif ( groups_is_user_admin( get_current_user_id(), $object_id ) ) {
			$can_manage = true;
		}

		return apply_filters( 'frontpage_buddy_can_manage', $can_manage, $this->get_integration_type(), $object_id );
	}

	/**
	 * Conditionally, set the front page as the default extension.
	 *
	 * @param string $existing_default Since this is hooked to a filter.
	 * @return string
	 */
	public function set_fp_as_default_extension( $existing_default ) {
		$group_id = bp_get_current_group_id();
		if ( ! $group_id ) {
			return $existing_default;
		}

		if ( $this->has_custom_front_page( $group_id ) ) {
			return $this->get_option( 'frontpage_nav_slug' );
		}

		return $existing_default;
	}
}
