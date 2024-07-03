<?php
/**
 * Front page for buddypress member profiles.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Integrations\BuddyPress;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Front page for buddypress member profiles.
 */
class Profiles extends \RecycleBin\FrontPageBuddy\Integration {

	/**
	 * Get details about this integration, to be displayed in admin settings screen.
	 *
	 * @return string
	 */
	public function get_admin_description() {
		$notice_class = 'notice-info';
		if ( ! function_exists( '\bp_nouveau_get_appearance_settings' ) || ! \bp_nouveau_get_appearance_settings( 'user_front_page' ) ) {
			$notice_class = 'notice-warning';
		}

		$cf_enabled = false;
		if ( function_exists( '\bp_nouveau_get_appearance_settings' ) ) {
			if ( \bp_nouveau_get_appearance_settings( 'user_front_page' ) ) {
				$cf_enabled = true;
			}
		}
		$html  = '<p>' . __( 'This enables all members of your buddypress site to customize their front page.', 'frontpage-buddy' ) . '</p>';
		$html .= '<p>' . __( 'Frontpage Buddy has no effect if the following option is not enabled:', 'frontpage-buddy' ) . '</p>';

		$html .= '<ul>';
		$html .= '<li>' . __( 'Appearance' ) . ' &gt; ' . __( 'Customize' ) . ' &gt; BuddyPress Nouveau &gt; ' . __( 'Member front page', 'buddypress' ) . ' &gt; ' . __( 'Enable default front page for member profiles.', 'buddypress' );
		$html .= $cf_enabled ? '<span class="notice notice-success inline">' . esc_html__( 'Currently enabled', 'frontpage-buddy' ) . '</span>' : '<span class="notice notice-error inline">' . esc_html__( 'Currently disabled', 'frontpage-buddy' ) . '</span>';
		$html .= '</li>';
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Get the fields for specific settings for this integration, if any.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		$show_prompt = $this->get_option( 'show_encourage_prompt' );
		$show_prompt = 'yes' === $show_prompt ? 'yes' : '';

		$prompt_text = $this->get_option( 'show_encourage_prompt' );
		if ( $prompt_text ) {
			$prompt_text = trim( $prompt_text );
		}
		if ( ! $prompt_text ) {
			$prompt_text = sprintf(
				/* translators: 1: {{LINK}} . In front end, this gets replaced by <a href='..'>here</a> */
				__( 'Customize your profile\'s front page by going %s.', 'frontpage-buddy' ),
				'{{LINK}}'
			);
		}

		return array(
			'show_encourage_prompt' => array(
				'type'        => 'switch',
				'label'       => __( 'Show prompt when viewing one\'s own profile?', 'frontpage-buddy' ),
				'value'       => $show_prompt,
				'label_off'   => __( 'No', 'frontpage-buddy' ),
				'label_on'    => __( 'Yes', 'frontpage-buddy' ),
				'description' => __( 'If enabled, when a member visits their profile, they see a small prompt at the top. This can be used to encourage members to add content to their front page. This can also be used to add a link to the page where the member can customize their front page.', 'frontpage-buddy' ),
			),
			'encourage_prompt_text' => array(
				'type'        => 'textarea',
				'label'       => __( 'Prompt text', 'frontpage-buddy' ),
				'value'       => $prompt_text,
				'description' => __( 'The text to be displayed inside the aforementioned prompt. You can use the placeholder {{LINK}} which will automatically be replaced with a link to the page where the member can customize their front page.', 'frontpage-buddy' ),
				'attributes'  => array(
					'rows' => 3,
					'cols' => 50,
				),
			),
		);
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
		return function_exists( '\bp_nouveau_get_appearance_settings' ) && \bp_nouveau_get_appearance_settings( 'user_front_page' );
	}

	/**
	 * Is the current request a widget edit/manage screen.
	 *
	 * @param boolean $flag Passed when this is hooked to a filter.
	 * @return boolean
	 */
	public function is_widgets_edit_screen( $flag = false ) {
		if ( bp_is_user() && 'settings' === bp_current_component() && 'front-page' === bp_current_action() ) {
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
		return get_user_meta( $object_id, '_fpbuddy_page_layout', true );
	}

	/**
	 * Update the layout for front page.
	 *
	 * @param int    $object_id Id of the member.
	 * @param string $data html.
	 * @return void
	 */
	public function update_frontpage_layout( $object_id, $data = '' ) {
		update_user_meta( $object_id, '_fpbuddy_page_layout', $data );
	}

	/**
	 * Get the details of all individual widgets added for given object.
	 *
	 * @param int $object_id Id of the object(member/group).
	 * @return array
	 */
	public function get_added_widgets( $object_id ) {
		$all = get_user_meta( $object_id, '_fpbuddy_added_widgets', true );
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
		update_user_meta( $object_id, '_fpbuddy_added_widgets', $data );
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
