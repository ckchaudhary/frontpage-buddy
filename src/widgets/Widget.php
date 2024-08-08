<?php
/**
 * The main widget base class.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  The main widget base class
 */
abstract class Widget {

	use \RB\FrontPageBuddy\TraitGetSet;

	/**
	 * Widget type - A key to differentiate it from other widget types. E.g: contentblock, twitter_block etc.
	 * This must be unique across all widgets.
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * A descriptive, human-friendly name :)
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Description. What the widget does, etc.
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Description for admins, displayed on admin screen.
	 *
	 * @var string
	 */
	protected $description_admin = '';

	/**
	 * The html to be used for the icon of this widget.
	 * e.g: <i class="fa fa-add"></i>
	 * Used in manage-widget screen on front end.
	 *
	 * @var string
	 */
	protected $icon_image = '<i class="gg-details-more"></i>';

	/**
	 * Whether this widget can be added more than once on a page. Default true.
	 *
	 * @var boolean
	 */
	protected $is_multiple = true;

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
	 * Data for all fields/settings of the widget.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Get the description of widget intended to be displayed to admins.
	 * If admin description is not provided, it falls back to normal description.
	 *
	 * @return string
	 */
	public function get_description_admin() {
		return ! empty( $this->description_admin ) ? $this->description_admin : $this->description;
	}

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
	 * Get the value of the field for 'editing'.
	 *
	 * @param string $field_name self explanatory.
	 * @return mixed
	 */
	public function edit_field_value( $field_name ) {
		$val = isset( $this->options[ $field_name ] ) ? $this->options[ $field_name ] : '';
		if ( ! empty( $val ) ) {
			$val = is_array( $val ) ? stripslashes_deep( $val ) : stripslashes( $val );
		}

		return apply_filters( 'frontpage_buddy_edit_field_value', $val, $field_name, $this );
	}

	/**
	 * Get the value of the field for 'viewing'.
	 *
	 * @param string $field_name self explanatory.
	 * @return mixed
	 */
	public function view_field_val( $field_name ) {
		// For now, its same as edit_field_val.
		$val = isset( $this->options[ $field_name ] ) ? $this->options[ $field_name ] : '';
		if ( ! empty( $val ) ) {
			$val = is_array( $val ) ? stripslashes_deep( $val ) : stripslashes( $val );
		}
		return apply_filters( 'frontpage_buddy_view_field_value', $val, $field_name, $this );
	}

	/**
	 * Setup
	 *
	 * @param mixed $args initial data.
	 * @return void
	 */
	protected function setup( $args = '' ) {
		if ( empty( $args ) ) {
			return;
		}

		$this->id          = isset( $args['id'] ) && ! empty( $args['id'] ) ? $args['id'] : false;
		$this->options     = isset( $args['options'] ) && ! empty( $args['options'] ) ? $args['options'] : false;
		$this->object_type = isset( $args['object_type'] ) && ! empty( $args['object_type'] ) ? $args['object_type'] : 'bp_members';
		$this->object_id   = isset( $args['object_id'] ) && ! empty( $args['object_id'] ) ? $args['object_id'] : false;
	}

	/**
	 * Update widget settings
	 *
	 * @return array {
	 *      @type boolean $status
	 *      @type string  $message
	 * }
	 */
	public function update() {
		$retval = array(
			'status'  => false,
			'message' => '',
		);

		$validation_errors = $this->validate();

		if ( ! empty( $validation_errors ) ) {
			$retval['message'] = implode( '<br>', $validation_errors );
			return $retval;
		}

		$updated_data = array();

		$excluded_types = array( 'label' ); // and any other field type that we needn't save in db.
		$fields         = $this->get_fields();
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field_name => $field_attr ) {
				if ( in_array( $field_attr['type'], $excluded_types, true ) ) {
					continue;
				}

				$updated_data[ $field_name ] = $this->sanitize_field_value_for_db( $field_name, $field_attr );
			}
		}

		$updated_data = apply_filters( 'frontpage_buddy_widget_data', $updated_data, $this );

		$this->options = $updated_data;

		return array(
			'status'  => true,
			'message' => __( 'Updated', 'frontpage-buddy' ),
		);
	}

	/**
	 * Performs basic validation on form fields before updating.
	 *
	 * @return array of errors, if any.
	 */
	public function validate() {
		$errors = array();

		// Required fields.
		$fields = $this->get_fields();
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field_name => $field_attr ) {
				if ( isset( $field_attr['is_required'] ) && $field_attr['is_required'] ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
					if ( empty( $_POST[ $field_name ] ) ) {
						// translators: 'field name' can not be empty.
						$errors[] = sprintf( __( '%s can not be empty.', 'frontpage-buddy' ), $field_attr['label'] );
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Get the sanitized value for given field to be saved in database.
	 *
	 * @since 1.0.0
	 * @param string $field_name self explanator.
	 * @param array  $field_attr field propterties like field type etc.
	 * @return mixed
	 */
	public function sanitize_field_value_for_db( $field_name, $field_attr ) {
		$sanitized_value = '';
		switch ( $field_attr['type'] ) {
			case 'wp_editor':
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
				if ( isset( $_POST[ $field_name ] ) && ! empty( $_POST[ $field_name ] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
					$sanitized_value = wp_kses( wp_unslash( $_POST[ $field_name ] ), \RB\FrontPageBuddy\visual_editor_allowed_html_tags() );
				}

				break;

			case 'checkbox':
			case 'radio':
				// phpcs:disable
				if ( isset( $_POST[ $field_name ] ) && ! empty( $_POST[ $field_name ] ) ) {
					$sanitized_value = array();
					if ( is_array( $_POST[ $field_name ] ) ) {
						foreach ( $_POST[ $field_name ] as $raw_value ) {
							$sanitized_value[] = sanitize_text_field( wp_unslash( $raw_value ) );
						}
					} else {
						$sanitized_value[] = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );
					}
				}
				// phpcs:enable
				break;

			default:
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
				if ( isset( $_POST[ $field_name ] ) && ! empty( $_POST[ $field_name ] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
					$sanitized_value = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );
				}
				break;
		}

		return $sanitized_value;
	}

	/**
	 * Prints the html for settings/options of the widget.
	 *
	 * @return void
	 */
	public function settings_screen() {
		?>
		<form method="POST" action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
			<?php wp_nonce_field( 'frontpage_buddy_widget_opts_update', '_wpnonce', false ); ?>
			<input type="hidden" name="widget_id" value="<?php echo esc_attr( $this->get_id() ); ?>" >
			<input type="hidden" name="widget_type" value="<?php echo esc_attr( $this->type ); ?>" >
			<input type="hidden" name="action" value="frontpage_buddy_widget_opts_update" >
			<input type="hidden" name="object_type" value="<?php echo esc_attr( $this->object_type ); ?>">
			<input type="hidden" name="object_id" value="<?php echo esc_attr( $this->object_id ); ?>">

			<div class="widget_fields">
				<?php
				$fields = $this->get_fields();
				if ( ! empty( $fields ) ) {
					\RB\FrontPageBuddy\generate_form_fields( $fields );
				}
				?>

				<div class='fpwidget-submit'>
					<button type="submit"><?php esc_html_e( 'Update', 'frontpage-buddy' ); ?></button>
					<a href="#" class="close-widget-settings js-toggle-widget-state"><?php esc_html_e( 'Close', 'frontpage-buddy' ); ?></a>
				</div>
			</div>

		</form>
		<?php
	}

	/**
	 * Get default fields for all widgets.
	 *
	 * @return array
	 */
	protected function get_default_fields() {
		$fields = array(
			'heading'     => array(
				'type'  => 'text',
				'label' => __( 'Heading', 'frontpage-buddy' ),
				'value' => ! empty( $this->edit_field_value( 'heading' ) ) ? $this->edit_field_value( 'heading' ) : '',
			),
		);

		return apply_filters( 'frontpage_buddy_widgets_default_fields', $fields, $this );
	}

	/**
	 * Get all the 'fields' for the settings/options screen for this widget.
	 *
	 * @return array
	 */
	abstract public function get_fields();

	/**
	 * Get the output for this widget.
	 *
	 * @return void
	 */
	abstract public function get_output();

	/**
	 * Get the html to be appended before the actual output of a widget.
	 *
	 * @return string
	 */
	public function output_start() {
		$html = sprintf( '<div class="fp-widget fp-widget-%s">', esc_attr( $this->type ) );

		// Include heading for all.
		$heading = apply_filters( 'frontpage_buddy_widget_heading', $this->view_field_val( 'heading' ), $this );
		if ( ! empty( $heading ) ) {
			$html .= sprintf( '<div class="fp-widget-title"><h2>%s</h2></div>', esc_html( $heading ) );
		}

		$html .= '<div class="fp-widget-details">';

		return apply_filters( 'frontpage_buddy_widget_output_start', $html, $this );
	}

	/**
	 * Get the html to be appended after the actual output of a widget.
	 *
	 * @return string
	 */
	public function output_end() {
		return apply_filters( 'frontpage_buddy_widget_output_end', '</div><!-- .fp-widget-details --></div><!-- .fp-widget-->', $this );
	}
}
