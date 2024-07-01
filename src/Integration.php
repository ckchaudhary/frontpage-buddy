<?php
/**
 * Base Integration class.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Base Integration class
 */
abstract class Integration {
	/**
	 * Integration type. E.g: 'bp_groups', 'bp_members'
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Name of the integration. E.g: 'Groups', 'Member Profiles'
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Get the type of integration.
	 *
	 * @return string
	 */
	public function get_integration_type() {
		return $this->type;
	}

	/**
	 * Get the name of integration.
	 *
	 * @return string
	 */
	public function get_integration_name() {
		return $this->name;
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
	abstract public function is_widgets_edit_screen( $flag = false );

	/**
	 * When on manage widget screen, get the id of the object being edited.
	 * E.g: current user id, group id etc.
	 *
	 * @return mixed
	 */
	abstract public function get_editable_object_id();

	/**
	 * Is the current request a custom front page screen.
	 *
	 * @param boolean $flag Passed when this is hooked to a filter.
	 * @return boolean
	 */
	abstract public function is_custom_front_page_screen( $flag = false );

	/**
	 * Add data for javascript.
	 *
	 * @param array $data the first argument of the filter this function is hooked to.
	 * @return array
	 */
	public function manage_screen_script_data( $data ) {
		if ( ! $this->is_widgets_edit_screen() ) {
			return $data;
		}

		$data['object_type'] = $this->get_integration_type();
		$data['object_id']   = $this->get_editable_object_id();
		if ( $data['object_id'] ) {
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
					$title = apply_filters( 'frontpage_buddy_widget_title_for_manage_screen', '', $widget );
					$widget['title'] = $title;
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
	abstract public function get_frontpage_layout( $object_id );

	/**
	 * Update the layout for front page.
	 *
	 * @param int    $object_id Id of the member.
	 * @param string $data html.
	 * @return void
	 */
	abstract public function update_frontpage_layout( $object_id, $data = '' );

	/**
	 * Get the details of all individual widgets added for given object.
	 *
	 * @param int $object_id Id of the object(member/group).
	 * @return array
	 */
	abstract public function get_added_widgets( $object_id );

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
	abstract public function update_added_widgets( $object_id, $data = array() );

	/**
	 * Can the current user manage given integration( group or member )?
	 *
	 * @param int $object_id group id or user id.
	 *
	 * @return boolean
	 */
	abstract public function can_manage( $object_id );

	/**
	 * Output the contents for front page.
	 *
	 * @param int $target_id id of the member.
	 *
	 * @return void
	 */
	public function output_frontpage_content( $target_id ) {
		\RecycleBin\FrontPageBuddy\show_output(
			$this->get_frontpage_layout( $target_id ),
			$this->get_added_widgets( $target_id ),
			$this->get_integration_type(),
			$target_id
		);
	}

	/**
	 * Constructor
	 *
	 * @param string $type type of the integration.
	 * @param string $name Name. Optional.
	 *
	 * @return void
	 */
	public function __construct( $type, $name = '' ) {
		$this->type = $type;
		$this->name = $name;
		if ( empty( $this->name ) ) {
			$this->name = ucfirst( $type );
		}

		add_filter( 'frontpage_buddy_is_widgets_edit_screen', array( $this, 'is_widgets_edit_screen' ) );
		add_filter( 'frontpage_buddy_is_custom_front_page_screen', array( $this, 'is_custom_front_page_screen' ) );
		add_filter( 'frontpage_buddy_script_data', array( $this, 'manage_screen_script_data' ) );
	}
}
