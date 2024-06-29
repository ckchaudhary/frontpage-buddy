<?php
/**
 * Base Component class.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Components;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Base Component class
 */
abstract class Component {
	/**
	 * Component type. E.g: 'bp_groups', 'bp_members'
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Name of the component. E.g: 'Groups', 'Member Profiles'
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Get the type of component.
	 *
	 * @return string
	 */
	public function get_component_type() {
		return $this->type;
	}

	/**
	 * Get the name of component.
	 *
	 * @return string
	 */
	public function get_component_name() {
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
	abstract public function has_custom_front_page( $object_id, $set = false );

	/**
	 * Is the current request a widget edit/manage screen.
	 *
	 * @param boolean $flag Passed when this is hooked to a filter.
	 * @return boolean
	 */
	abstract public function is_widgets_edit_screen( $flag = false );

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
	abstract public function script_data( $data );

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
	 * Can the current user manage given component( group or member )?
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
			$this->get_component_type(),
			$target_id
		);
	}

	/**
	 * Constructor
	 *
	 * @param string $type type of the component.
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
		add_filter( 'frontpage_buddy_script_data', array( $this, 'script_data' ) );
	}
}
