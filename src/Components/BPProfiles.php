<?php
/**
 * Front page for buddypress member profiles.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Components;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Front page for buddypress member profiles.
 */
class BPProfiles extends Component {

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
	 * Add data for javascript.
	 *
	 * @param array $data the first argument of the filter this function is hooked to.
	 * @return array
	 */
	public function script_data( $data ) {
		if ( bp_is_user() ) {
			$data['object_type'] = $this->get_component_type();
			$data['object_id']   = bp_displayed_user_id();
		}

		if ( $this->is_widgets_edit_screen() ) {
			$data['all_widgets'] = array();
			$all = frontpage_buddy()->widget_collection()->get_available_widgets( $this->get_component_type(), bp_displayed_user_id() );
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

			$added_widgets = $this->get_added_widgets( bp_displayed_user_id() );
			if ( ! empty( $added_widgets ) ) {
				$temp = array();
				foreach ( $added_widgets as $widget ) {
					unset( $widget[ 'options' ] );
					$temp[] = $widget;
				}
				$added_widgets = $temp;
			}
			$data['added_widgets'] = $added_widgets;

			$data['fp_layout'] = $this->get_frontpage_layout( bp_displayed_user_id() );
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
	 * Can the current user manage given component( group or member )?
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

		return apply_filters( 'frontpage_buddy_can_manage', $can_manage, $this->get_component_type(), $object_id );
	}
}
