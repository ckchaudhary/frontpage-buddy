<?php
/**
 * The widget class.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) || exit;

/**
 *  The widget class.
 */
class Widget {

	use \RB\FrontPageBuddy\TraitGetSet;

	/**
	 * What type of widget is this?
	 *
	 * @var \RB\FrontPageBuddy\WidgetType
	 */
	protected $widget_type;

	/**
	 * An id generated at runtime. Useful for widgets which can be added more than once.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Whether this widget is added for a member or group.
	 *
	 * @var string
	 */
	protected $object_type = 'bp_members';

	/**
	 * ID of the user or group this widget is added to.
	 *
	 * @var string
	 */
	protected $object_id = false;

	/**
	 * Data for all data fields of the widget.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Get the id.
	 * If empty, set the id to a unique string.
	 *
	 * @return string
	 */
	public function get_id() {
		if ( empty( $this->id ) ) {
			$this->id = md5( microtime() );
		}

		return $this->id;
	}

	/**
	 * Get the id/slug of the widget type object associate with this object.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->widget_type->type;
	}

	/**
	 * Constructor.
	 *
	 * @param array                                 $args initial data.
	 * @param \RB\FrontPageBuddy\Widgets\WidgetType $type Widget type object.
	 */
	public function __construct( $args, $type ) {
		if ( ! empty( $args ) ) {
			$this->id          = isset( $args['id'] ) && ! empty( $args['id'] ) ? $args['id'] : false;
			$this->data        = isset( $args['data'] ) && ! empty( $args['data'] ) ? $args['data'] : false;
			$this->object_type = isset( $args['object_type'] ) && ! empty( $args['object_type'] ) ? $args['object_type'] : 'bp_members';
			$this->object_id   = isset( $args['object_id'] ) && ! empty( $args['object_id'] ) ? $args['object_id'] : false;
		}

		$this->widget_type = $type;
	}

	/**
	 * Get the saved value of one of the fields in the widget.
	 *
	 * @param string $field_name Self explanatory.
	 * @param string $context    Context in which the value is to be used.
	 *                           Expected values are 'view' and 'edit'.
	 *                           For now, $context is not used.
	 * @return mixed
	 */
	public function get_data( $field_name, $context = 'view' ) {
		$val = isset( $this->data[ $field_name ] ) ? $this->data[ $field_name ] : '';
		if ( ! empty( $val ) ) {
			$val = is_array( $val ) ? stripslashes_deep( $val ) : stripslashes( $val );
		}

		return apply_filters( 'frontpage_buddy_widget_get_data', $val, $field_name, $this, $context );
	}

	/**
	 * Get the saved value for all fields in the widget.
	 *
	 * @return array
	 */
	public function get_all_data() {
		return $this->data;
	}

	/**
	 * Update widget data.
	 *
	 * @param array $new_data New data for all fields.
	 *
	 * @return array {
	 *      @type boolean $status
	 *      @type string  $message
	 * }
	 */
	public function update( $new_data ) {
		$retval = array(
			'status'  => false,
			'message' => '',
		);

		$validation_errors = $this->widget_type->validate( $this, $new_data );

		if ( ! empty( $validation_errors ) ) {
			$retval['message'] = implode( '<br>', $validation_errors );
			return $retval;
		}

		$updated_data = array();

		$excluded_types = array( 'label' ); // and any other field type that we needn't save in db.
		$fields         = $this->widget_type->get_data_fields( $this );
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field_name => $field_attr ) {
				if ( in_array( $field_attr['type'], $excluded_types, true ) ) {
					continue;
				}

				$field_value                 = isset( $new_data[ $field_name ] ) ? $new_data[ $field_name ] : false;
				$updated_data[ $field_name ] = $this->widget_type->sanitize_field_value_for_db( $field_name, $field_value, $field_attr );
			}
		}

		$updated_data = apply_filters( 'frontpage_buddy_widget_data', $updated_data, $this );

		$this->data = $updated_data;

		return array(
			'status'  => true,
			'message' => __( 'Updated', 'frontpage-buddy' ),
		);
	}
}
