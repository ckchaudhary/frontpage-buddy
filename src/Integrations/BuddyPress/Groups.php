<?php
/**
 * Front page for buddypress groups.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace FrontPageBuddy\Integrations\BuddyPress;

defined( 'ABSPATH' ) || exit;

/**
 *  Front page for buddypress groups.
 */
class Groups extends \FrontPageBuddy\Integration {

	/**
	 * Get details about this integration, to be displayed in admin settings screen.
	 *
	 * @return string
	 */
	public function get_admin_description() {
		$cf_enabled       = false;
		$cf_boxes_enabled = false;
		if ( function_exists( '\bp_nouveau_get_appearance_settings' ) ) {
			if ( \bp_nouveau_get_appearance_settings( 'group_front_page' ) ) {
				$cf_enabled = true;
			}

			if ( \bp_nouveau_get_appearance_settings( 'group_front_boxes' ) ) {
				$cf_boxes_enabled = true;
			}
		}

		$html = '<p>' . __( 'This enables administrators of all BuddyPress groups to customize a group\'s front page.', 'frontpage-buddy' ) . '</p>';

		$html .= '<p>' . __( 'Frontpage Buddy has no effect if the following options are not enabled:', 'frontpage-buddy' ) . '</p>';
		$html .= '<ul>';

		$html .= '<li>' . __( 'Appearance', 'frontpage-buddy' ) . ' &gt; ' . __( 'Customize', 'frontpage-buddy' ) . ' &gt; BuddyPress Nouveau &gt; ' . __( 'Group front page', 'frontpage-buddy' ) . ' &gt; ' . __( 'Enable custom front pages for groups.', 'frontpage-buddy' );
		$html .= $cf_enabled ? '<span class="notice notice-success inline">' . esc_html__( 'Currently enabled', 'frontpage-buddy' ) . '</span>' : '<span class="notice notice-error inline">' . esc_html__( 'Currently disabled', 'frontpage-buddy' ) . '</span>';
		$html .= '</li>';

		$html .= '<li>' . __( 'Appearance', 'frontpage-buddy' ) . ' &gt; ' . __( 'Customize', 'frontpage-buddy' ) . ' &gt; BuddyPress Nouveau &gt; ' . __( 'Group front page', 'frontpage-buddy' ) . ' &gt; ' . __( 'Enable custom boxes for group homepages...', 'frontpage-buddy' );
		$html .= $cf_boxes_enabled ? '<span class="notice notice-success inline">' . esc_html__( 'Currently enabled', 'frontpage-buddy' ) . '</span>' : '<span class="notice notice-error inline">' . esc_html__( 'Currently disabled', 'frontpage-buddy' ) . '</span>';
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
				__( 'Customize this group\'s front page by going %s.', 'frontpage-buddy' ),
				$editor_link
			);
		}

		$group_navs = array(
			'none' => 'Do not redirect',
		);
		if ( bp_is_active( 'activity' ) ) {
			$group_navs[ bp_get_activity_slug() ] = esc_html__( 'Activity', 'frontpage-buddy' );
		}
		$group_navs[ bp_get_members_slug() ] = esc_html__( 'Members', 'frontpage-buddy' );

		$redirect_to = $this->get_option( 'redirect_when_empty' );
		$redirect_to = empty( $redirect_to ) || ! isset( $group_navs[ $redirect_to ] ) ? 'none' : $redirect_to;

		return array(
			'show_encourage_prompt' => array(
				'type'        => 'switch',
				'label'       => __( 'Show prompt to group admins?', 'frontpage-buddy' ),
				'label_off'   => __( 'No', 'frontpage-buddy' ),
				'label_on'    => __( 'Yes', 'frontpage-buddy' ),
				'attributes'  => $attrs_show_prompt,
				'description' => __( 'If enabled, when a group administrator visits the group\'s front page, they see a small prompt at the top. This can be used to encourage group admins to provide necessary information on front page. This can also be used to add a link to the page where the group admin can customize the front page.', 'frontpage-buddy' ),
			),
			'encourage_prompt_text' => array(
				'type'          => 'tinymce_tiny',
				'label'         => __( 'Prompt text', 'frontpage-buddy' ),
				'value'         => $prompt_text,
				'description'   => __( 'The text to be displayed inside the aforementioned prompt. You can use the placeholder {{EDITOR_URL}} which will automatically be replaced with the url where the front page can be customized.', 'frontpage-buddy' ),
				'attributes'    => array(
					'rows' => 3,
					'cols' => 50,
				),
				'sanitization'  => 'basic_html',
				'wrapper_class' => $prompt_field_wrapper_class,
			),
			'redirect_when_empty'   => array(
				'type'        => 'select',
				'label'       => __( 'Redirect empty front page', 'frontpage-buddy' ),
				'value'       => $redirect_to,
				'options'     => $group_navs,
				'description' => __( 'If a group\'s admins have not added any content for the front page, the front page will be blank. In such cases, it is better to simply redirect to another page/tab(e.g: \'Activity\' ) of the group. Group admins will not be redirected though, they\'ll still see an empty front page with the prompt( mentioned in previous setting ).', 'frontpage-buddy' ),
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
		return function_exists( '\bp_nouveau_get_appearance_settings' ) && \bp_nouveau_get_appearance_settings( 'group_front_page' );
	}

	/**
	 * Is the current request a widget edit/manage screen.
	 *
	 * @param boolean $flag Passed when this is hooked to a filter.
	 * @return boolean
	 */
	public function is_widgets_edit_screen( $flag = false ) {
		if ( bp_is_active( 'groups' ) && bp_is_group() && 'admin' === bp_current_action() && bp_action_variable( 0 ) === 'front-page' ) {
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
		if ( bp_is_active( 'groups' ) && bp_is_group() && 'home' === bp_current_action() ) {
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
}
