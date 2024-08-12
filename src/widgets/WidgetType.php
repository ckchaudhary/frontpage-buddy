<?php
/**
 * The widget type class.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  The widget type class.
 */
abstract class WidgetType {

	use \RB\FrontPageBuddy\TraitGetSet;

	/**
	 * Widget type - A key to differentiate it from other widget types. E.g: contentblock, twitter_block etc.
	 * This must be unique across all widget types.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * A descriptive, human-friendly name :)
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Description. What the widget does, etc.
	 * Maybe displayed in front end - to end users.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Description for admins, displayed on admin screen.
	 *
	 * @var string
	 */
	protected $description_admin;

	/**
	 * The html to be used for the icon of this widget.
	 * e.g: <i class="fa fa-add"></i>
	 * Used in manage-widget screen on front end.
	 *
	 * @var string
	 */
	protected $icon_image = '<i class="gg-details-more"></i>';

	/**
	 * Get the description of widget intended to be displayed to admins.
	 * If admin description is not provided, it falls back to normal description.
	 *
	 * @return string
	 */
	public function get_admin_description() {
		return ! empty( $this->description_admin ) ? $this->description_admin : $this->description;
	}

	/**
	 * Get the fields for specific settings of this widget type, if any.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		$all_integrations = frontpage_buddy()->get_all_integrations();
		if ( empty( $all_integrations ) ) {
			return array();
		}

		$integration_options = array();
		foreach ( $all_integrations as $integration_type => $integration_obj ) {
			$integration_options[ $integration_type ] = $integration_obj->get_integration_name();
		}
		return array(
			'enabled_for' => array(
				'type'    => 'checkbox',
				'label'   => __( 'Enable for', 'frontpage-buddy' ),
				'value'   => $this->get_option( 'enabled_for' ),
				'options' => $integration_options,
			),
		);
	}

	/**
	 * Get an option's/setting's value.
	 *
	 * @param string $option_name name of the option.
	 * @return mixed
	 */
	public function get_option( $option_name ) {
		$all_widgets = frontpage_buddy()->option( 'widgets' );
		$all_options = ! empty( $all_widgets ) && isset( $all_widgets[ $this->type ] ) && ! empty( $all_widgets[ $this->type ] ) ? $all_widgets[ $this->type ] : array();
		$opt_value   = isset( $all_options[ $option_name ] ) ? $all_options[ $option_name ] : null;

		return apply_filters( 'frontpage_buddy_get_widget_option', $opt_value, $option_name, $this );
	}

	/**
	 * Get an option's/setting's default value.
	 * This function is to be overloaded by widgets.
	 *
	 * @param mixed                         $option_value value of the option.
	 * @param string                        $option_name  name of the option.
	 * @param \RB\FrontPageBuddy\WidgetType $widget_type  Widget type object.
	 *
	 * @return mixed null if no default value is to be provided.
	 */
	public function filter_option_value( $option_value, $option_name, $widget_type ) {
		if ( $widget_type->type !== $this->type ) {
			return $option_value;
		}

		// @todo: Furnish default value if required.

		return $option_value;
	}

	/**
	 * Add data for scripts on manage front page screen.
	 * Should only be called if the widgettype is enabled for current integration.
	 *
	 * @param array $data Existing data.
	 * @return array
	 */
	public function add_manage_screen_script_data( $data ) {
		// Child classes should overwrite this method if required.
		return $data;
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		\add_filter( 'frontpage_buddy_get_widget_option', array( $this, 'filter_option_value' ), 9, 3 );
	}

	/**
	 * Is this widget type enabled for given integration.
	 *
	 * @param string $integration_type Id/Slug of the integeration.
	 * @param mixed  $object_id Id of the current object in question.
	 *                          E.g: group_id or member_id. Optional.
	 * @return boolean
	 */
	public function is_enabled_for( $integration_type, $object_id = false ) {
		$enabled_for = $this->get_option( 'enabled_for' );
		$enabled     = ! empty( $enabled_for ) && in_array( $integration_type, $enabled_for, true );
		return apply_filters( 'frontpage_buddy_widget_type_enabled_for', $enabled, $integration_type, $object_id );
	}

	/**
	 * Get a new widget object.
	 *
	 * @param array $props Widget properties like id, data etc.
	 * @return \RB\FrontPageBuddy\Widgets\Widget
	 */
	public function get_widget( $props = array() ) {
		return new \RB\FrontPageBuddy\Widgets\Widget( $props, $this );
	}

	/**
	 * Get data fields for all widgets.
	 *
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 *
	 * @return array
	 */
	protected function get_default_data_fields( $widget ) {
		$fields = array(
			'heading' => array(
				'type'  => 'text',
				'label' => __( 'Heading', 'frontpage-buddy' ),
				'value' => ! empty( $widget->get_data( 'heading', 'edit' ) ) ? $widget->get_data( 'heading', 'edit' ) : '',
			),
		);

		return apply_filters( 'frontpage_buddy_widgets_default_data_fields', $fields, $this, $widget );
	}

	/**
	 * Get all the data 'fields' for the settings/options screen for this widget.
	 *
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 *
	 * @return array
	 */
	abstract public function get_data_fields( $widget );

	/**
	 * Get the output for this widget.
	 *
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 *
	 * @return string
	 */
	abstract public function get_output( $widget );

	/**
	 * Get the html to be appended before the actual output of a widget.
	 *
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 * @return string
	 */
	public function output_start( $widget ) {
		$html = sprintf( '<div class="fp-widget fp-widget-%s">', esc_attr( $widget->get_type() ) );

		// Include heading for all.
		$heading = apply_filters( 'frontpage_buddy_widget_heading', $widget->get_data( 'heading', 'view' ), $this );
		if ( ! empty( $heading ) ) {
			$html .= sprintf( '<div class="fp-widget-title"><h2>%s</h2></div>', esc_html( $heading ) );
		}

		$html .= '<div class="fp-widget-details">';

		return apply_filters( 'frontpage_buddy_widget_output_start', $html, $this, $widget );
	}

	/**
	 * Get the html to be appended after the actual output of a widget.
	 *
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 * @return string
	 */
	public function output_end( $widget ) {
		return apply_filters( 'frontpage_buddy_widget_output_end', '</div><!-- .fp-widget-details --></div><!-- .fp-widget-->', $this, $widget );
	}

	/**
	 * Performs basic validation on data fields before updating.
	 *
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 * @return array of errors, if any.
	 */
	public function validate( $widget ) {
		$errors = array();

		// Required fields.
		$fields = $this->get_data_fields( $widget );
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
	 * Get the sanitized value for given data field to be saved in database.
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
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
				if ( isset( $_POST[ $field_name ] ) && ! empty( $_POST[ $field_name ] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
					if ( is_array( $_POST[ $field_name ] ) ) {
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
						$sanitized_value = map_deep( wp_unslash( $_POST[ $field_name ] ), 'sanitize_text_field' );
					} else {
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
						$sanitized_value[] = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );
					}
				}
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
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 * @return void
	 */
	public function widget_input_ui( $widget ) {
		?>
		<form method="POST" action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
			<?php wp_nonce_field( 'frontpage_buddy_widget_opts_update', '_wpnonce', false ); ?>
			<input type="hidden" name="widget_id" value="<?php echo esc_attr( $widget->get_id() ); ?>" >
			<input type="hidden" name="widget_type" value="<?php echo esc_attr( $widget->get_type() ); ?>" >
			<input type="hidden" name="action" value="frontpage_buddy_widget_opts_update" >
			<input type="hidden" name="object_type" value="<?php echo esc_attr( $widget->object_type ); ?>">
			<input type="hidden" name="object_id" value="<?php echo esc_attr( $widget->object_id ); ?>">

			<div class="widget_fields">
				<?php
				$fields = $this->get_data_fields( $widget );
				if ( ! empty( $fields ) ) {
					$fields_html  = \RB\FrontPageBuddy\generate_form_fields( $fields );
					$allowed_tags = wp_parse_args( \RB\FrontPageBuddy\basic_html_allowed_tags(), \RB\FrontPageBuddy\form_elements_allowed_tags() );
					echo wp_kses( $fields_html, $allowed_tags );
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
}
