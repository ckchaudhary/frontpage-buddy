<?php
/**
 * Front page for ultimate-member profiles.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Integrations\UltimateMember;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Front page for ultimate-member profiles.
 */
class Profiles extends \RecycleBin\FrontPageBuddy\Integration {

	/**
	 * Get/set If the current object has a custom front page.
	 *
	 * @param int    $object_id Id of member or group.
	 * @param string $set 'no' or 'yes'. Default false.
	 *
	 * @return boolean
	 */
	public function has_custom_front_page( $object_id, $set = false ) {
		$flag = false;
		$enabled_for = frontpage_buddy()->option( 'enabled_for' );
		if ( ! empty( $enabled_for ) && in_array( $this->get_integration_type(), $enabled_for ) ) {
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
		$flag = um_is_on_edit_profile();

		return $flag;
	}

	/**
	 * Is the current request a custom front page screen.
	 *
	 * @param boolean $flag Passed when this is hooked to a filter.
	 * @return boolean
	 */
	public function is_custom_front_page_screen( $flag = false ) {
		/**
		 * Possible checks 
		 * if ( um_queried_user() && um_is_core_page( 'user' ) ) {
		 */

		if ( um_is_core_page( 'user' ) && ! um_is_on_edit_profile() ) {
			$flag = true;
		}

		return $flag;
	}

	/**
	 * Add data for javascript.
	 *
	 * @param array $data the first argument of the filter this function is hooked to.
	 * @return array
	 */
	public function script_data( $data ) {
		if ( um_is_core_page( 'user' ) ) {
			$data['object_type'] = $this->get_integration_type();
			$data['object_id']   = (int) UM()->user()->target_id;
		}

		if ( $this->is_widgets_edit_screen() ) {
			$data['all_widgets'] = array();
			$all = frontpage_buddy()->widget_collection()->get_available_widgets( $this->get_integration_type(), $data['object_id'] );
			if ( ! empty( $all ) ) {
				foreach ( $all as $widget ) {
					$data['all_widgets'][] = array(
						'type'        => $widget->type,
						'name'        => $widget->name,
						'description' => $widget->description,
						'icon'        => $widget->icon_image_url,
					);
				}
			}

			$added_widgets = $this->get_added_widgets( $data['object_id'] );
			if ( ! empty( $added_widgets ) ) {
				$temp = array();
				foreach ( $added_widgets as $widget ) {
					unset( $widget['options'] );
					$temp[] = $widget;
				}
				$added_widgets = $temp;
			}
			$data['added_widgets'] = $added_widgets;

			$data['fp_layout'] = $this->get_frontpage_layout( $data['object_id'] );
		}

		return $data;
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
	 * Can the current user manage given user
	 *
	 * @param int $object_id group id or user id.
	 *
	 * @return boolean
	 */
	public function can_manage( $object_id ) {
		$can_manage = get_current_user_id() === $object_id;

		if ( ! $can_manage && UM()->roles()->um_current_user_can( 'edit', $object_id ) ) {
			$can_manage = true;
		}

		return apply_filters( 'frontpage_buddy_can_manage', $can_manage, $this->get_integration_type(), $object_id );
	}
}
