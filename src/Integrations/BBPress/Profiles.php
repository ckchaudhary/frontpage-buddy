<?php
/**
 * Front page for bbpress member profiles.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace FrontPageBuddy\Integrations\BBPress;

defined( 'ABSPATH' ) || exit;

/**
 *  Front page for bbpress member profiles.
 */
class Profiles extends \FrontPageBuddy\Integration {

	/**
	 * Get details about this integration, to be displayed in admin settings screen.
	 *
	 * @return string
	 */
	public function get_admin_description() {
		$html = __( 'This enables all members of your forums to customize their profile page.', 'frontpage-buddy' );
		return $html;
	}

	/**
	 * Get the fields for specific settings for this integration, if any.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		$attrs_show_prompt          = array();
		$prompt_field_wrapper_class = 'is-hidden';
		if ( 'yes' === $this->get_option( 'show_encourage_prompt' ) ) {
			$attrs_show_prompt['checked'] = 'checked';
			$prompt_field_wrapper_class   = '';
		}

		$prompt_text = $this->get_option( 'encourage_prompt_text' );
		if ( $prompt_text ) {
			$prompt_text = trim( $prompt_text );
		}
		if ( ! $prompt_text ) {
			$editor_link = '<a href="{{EDITOR_URL}}">' . esc_html__( 'here', 'frontpage-buddy' ) . '</a>';
			$prompt_text = sprintf(
				/* translators: 1: Link to edit-front-page url. */
				__( 'Customize your profile\'s front page by going %s.', 'frontpage-buddy' ),
				$editor_link
			);
		}

		return array(
			'show_encourage_prompt' => array(
				'type'        => 'switch',
				'label'       => __( 'Show prompt when viewing one\'s own profile?', 'frontpage-buddy' ),
				'label_off'   => __( 'No', 'frontpage-buddy' ),
				'label_on'    => __( 'Yes', 'frontpage-buddy' ),
				'attributes'  => $attrs_show_prompt,
				'description' => __( 'If enabled, when a member visits their profile, they see a small prompt. This can be used to encourage members to add content to their front page. This can also be used to add a link to the page where the member can customize their front page.', 'frontpage-buddy' ),
			),
			'encourage_prompt_text' => array(
				'type'          => 'tinymce_tiny',
				'label'         => __( 'Prompt text', 'frontpage-buddy' ),
				'value'         => $prompt_text,
				'description'   => __( 'The text to be displayed inside the aforementioned prompt. You can use the placeholder {{EDITOR_URL}} which will automatically be replaced with the url where the member can customize their front page.', 'frontpage-buddy' ),
				'attributes'    => array(
					'rows' => 3,
					'cols' => 50,
				),
				'sanitization'  => 'basic_html',
				'wrapper_class' => $prompt_field_wrapper_class,
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
		$flag        = false;
		$enabled_for = frontpage_buddy()->option( 'enabled_for' );
		if ( ! empty( $enabled_for ) && in_array( $this->get_integration_type(), $enabled_for, true ) ) {
			$flag = true;
		}

		return apply_filters( 'frontpage_buddy_has_custom_front_page', $flag, $this->get_integration_type(), $object_id );
	}

	/**
	 * Is the current request a widget edit/manage screen.
	 *
	 * @param boolean $flag Passed when this is hooked to a filter.
	 * @return boolean
	 */
	public function is_widgets_edit_screen( $flag = false ) {
		if ( bbp_is_single_user_edit() ) {
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
		if ( bbp_is_single_user_profile() ) {
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
		return bbp_get_displayed_user_id();
	}

	/**
	 * Get the layout for front page.
	 *
	 * @param int $object_id Id of the member.
	 * @return string
	 */
	public function get_frontpage_layout( $object_id ) {
		return get_user_meta( $object_id, '_frontpage_buddy_page_layout', true );
	}

	/**
	 * Update the layout for front page.
	 *
	 * @param int    $object_id Id of the member.
	 * @param string $data html.
	 * @return void
	 */
	public function update_frontpage_layout( $object_id, $data = '' ) {
		update_user_meta( $object_id, '_frontpage_buddy_page_layout', $data );
	}

	/**
	 * Get the details of all individual widgets added for given object.
	 *
	 * @param int $object_id Id of the object(member/group).
	 * @return array
	 */
	public function get_added_widgets( $object_id ) {
		$all = get_user_meta( $object_id, '_frontpage_buddy_added_widgets', true );
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
		update_user_meta( $object_id, '_frontpage_buddy_added_widgets', $data );
	}

	/**
	 * Can the current user manage given user
	 *
	 * @param int $object_id group id or user id.
	 *
	 * @return boolean
	 */
	public function can_manage( $object_id ) {
		$can_manage = get_current_user_id() === $object_id;

		if ( ! $can_manage && current_user_can( 'edit_user', $object_id ) ) {
			$can_manage = true;
		}

		return apply_filters( 'frontpage_buddy_can_manage', $can_manage, $this->get_integration_type(), $object_id );
	}
}
