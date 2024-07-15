<?php
/**
 * Miscellaneous utility functions.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy;

defined( 'ABSPATH' ) ? '' : exit();

/**
 * Loads( includes ) the given template file.
 * Checks first in child theme, then in parent theme and finally in plugin's templates folder.
 *
 * @param string $template template file name without the leading '.php'.
 * @return void
 */
function load_template( $template ) {
	$template .= '.php';
	if ( file_exists( get_stylesheet_directory() . '/frontpage-buddy/' . $template ) ) {
		include get_stylesheet_directory() . '/frontpage-buddy/' . $template;
	} elseif ( file_exists( get_template_directory() . '/frontpage-buddy/' . $template ) ) {
		include get_template_directory() . '/frontpage-buddy/' . $template;
	} else {
		include FPBUDDY_PLUGIN_DIR . 'templates/frontpage-buddy/' . $template;
	}
}

/**
 * Load the given template file in buffer.
 *
 * @param string $template template file name without the leading '.php'.
 * @return string contents of the template file.
 */
function buffer_template_part( $template ) {
	ob_start();
	load_template( $template );
	$output = ob_get_clean();

	return $output;
}

/**
 * Function to generate the html for given form fields.
 *
 * @param array $fields list of fields.
 * @param array $args Options.
 * @return void
 */
function generate_form_fields( $fields, $args = '' ) {

	if ( ! $fields || empty( $fields ) ) {
		return;
	}
	if ( ! $args || empty( $args ) ) {
		$args = array();
	}

	$defaults = array(
		'before_list'  => '',
		'after_list'   => '',

		'before_field' => '<div class="{{FIELD_CLASS}}">',
		'after_field'  => '</div><!-- .field -->',

		'before_label' => '',
		'after_label'  => '',

		'before_input' => '',
		'after_input'  => '',
	);

	$args = array_merge( $defaults, $args );

	// phpcs:ignore WordPress.Security.EscapeOutput
	echo $args['before_list'];

	foreach ( $fields as $field_name => $field ) {
		$field_defaults = array(
			'type'          => 'text',
			'id'            => '',
			'label'         => '',
			'before'        => '',
			'after'         => '',
			'wrapper_class' => '',
		);
		$field          = wp_parse_args( $field, $field_defaults );

		$field_id = $field['id'];
		if ( empty( $field_id ) ) {
			$field_id = $field_name . '_' . \uniqid();
		}

		$cssclass = 'field field-' . $field_name . ' field-' . $field['type'];
		if ( $field['wrapper_class'] ) {
			$cssclass .= ' ' . $field['wrapper_class'];
		}

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo str_replace( '{{FIELD_CLASS}}', $cssclass, $args['before_field'] );

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_label'];
		if ( isset( $field['label'] ) && ! empty( $field['label'] ) ) {
			echo '<label>' . esc_html( $field['label'] ) . '</label>';
		}
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['after_label'];

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_input'];

		$html = $field['before'];

		$input_attributes = '';
		if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
			foreach ( $field['attributes'] as $att_name => $att_val ) {
				$input_attributes .= sprintf( ' %s="%s" ', esc_html( $att_name ), esc_attr( $att_val ) );
			}
		}
		switch ( $field['type'] ) {
			case 'checkbox':
			case 'radio':
				// Label.
				foreach ( $field['options'] as $option_val => $option_label ) {
					$html .= sprintf(
						'<label class="label_option label_option_%1$s"><input type="%1$s" name="%2$s[]" value="%3$s"',
						esc_attr( $field['type'] ),
						esc_attr( $field_name ),
						esc_attr( $option_val )
					);

					// Checked ?
					if ( isset( $field['value'] ) && ! empty( $field['value'] ) ) {
						if ( is_array( $field['value'] ) ) {
							if ( in_array( $option_val, $field['value'], true ) ) {
								$html .= " checked='checked'";
							}
						} elseif ( $option_val === $field['value'] ) {
							$html .= '';
						}
					}

					$html .= $input_attributes . ' />' . esc_html( $option_label ) . '</label>';
				}

				break;

			case 'switch':
				$field_val = isset( $field['value'] ) ? $field['value'] : 'yes';
				$html     .= sprintf(
					'<label class="fpbuddy-switch">	
						<input type="checkbox" name="%1$s" value="%2$s" %3$s>
						<span class="switch-mask"></span>
						<span class="switch-labels">
							<span class="label-on">%4$s</span>
							<span class="label-off">%5$s</span>
						</span>
					</label>',
					esc_attr( $field_name ),
					esc_attr( $field_val ),
					$input_attributes,
					esc_html( $field['label_on'] ),
					esc_html( $field['label_off'] )
				);
				break;

			case 'select':
				// Label.
				$html .= sprintf(
					'<select id="%1$s" name="%2$s"',
					esc_attr( $field_id ),
					esc_attr( $field_name )
				);

				$html .= $input_attributes . ' >';

				foreach ( $field['options'] as $option_val => $option_label ) {
					$html .= "<option value='" . esc_attr( $option_val ) . "' ";

					// checked ?
					if ( isset( $field['value'] ) && ! empty( $field['value'] ) ) {
						if ( is_array( $field['value'] ) ) {
							if ( in_array( $option_val, $field['value'], true ) ) {
								$html .= " selected='selected'";
							}
						} elseif ( $option_val === $field['value'] ) {
								$html .= " selected='selected'";
						}
					}

					$html .= '>' . esc_html( $option_label ) . '</option>';
				}

				$html .= '</select>';

				break;
			case 'textarea':
			case 'wp_editor':
				// Label.
				$html = sprintf(
					'<textarea id="%1$s" name="%2$s"',
					esc_attr( $field_id ),
					esc_attr( $field_name )
				);

				$html .= $input_attributes . ' >';

				$field['value'] = esc_textarea( $field['value'] );
				if ( isset( $field['value'] ) && $field['value'] ) {
					$html .= $field['value'];
				}

				$html .= '</textarea>';
				break;

			case 'button':
			case 'submit':
				$field_type = 'submit';
				if ( isset( $field['type'] ) ) {
					$field_type = $field['type'];
				}

				if ( 'button' === $field_type ) {
					$html .= '<button id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '" ';
				} else {
					$html .= '<input type="' . esc_attr( $field_type ) . '" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '" ';
				}

				$html .= $input_attributes;

				if ( 'button' === $field_type ) {
					$html .= '>';
					if ( isset( $field['value'] ) && $field['value'] ) {
						$html .= esc_html( $field['value'] );
					}
					$html .= '</button>';
				} else {
					if ( isset( $field['value'] ) && $field['value'] ) {
						$html .= ' value="' . esc_attr( $field['value'] ) . '" ';
					}
					$html .= ' />';
				}
				break;

			default:
				// Label.
				$html = sprintf(
					'<input id="%1$s" name="%2$s" type="%3$s"',
					esc_attr( $field_id ),
					esc_attr( $field_name ),
					esc_attr( $field['type'] )
				);

				$html .= $input_attributes;

				// Value.
				if ( isset( $field['value'] ) ) {
					$html .= ' value="' . esc_attr( $field['value'] ) . '" ';
				}

				$html .= ' />';
				break;
		}

		// Description.
		if ( isset( $field['description'] ) && $field['description'] ) {
			$html .= "<span class='field_description'>" . $field['description'] . '</span>';
		}

		$html .= $field['after'];

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $html;

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['after_input'];

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['after_field'];
	}

	// phpcs:ignore WordPress.Security.EscapeOutput
	echo $args['after_list'];
}

/**
 * Sanitize the input of a field.
 *
 * @param mixed $field_value can be a single value or an array of values.
 * @param array $field_attrs Details about the field.
 * @return mixed
 */
function sanitize_field( $field_value, $field_attrs ) {
	$sanitization_func = '\sanitize_text_field';
	$sanitization_type = isset( $field_attrs['sanitization'] ) ? $field_attrs['sanitization'] : '';
	if ( empty( $sanitization_type ) ) {
		$sanitization_type = isset( $field_attrs['type'] ) ? $field_attrs['type'] : 'text';
	}

	if ( 'none' === $sanitization_type ) {
		return $field_value;
	}

	switch ( $sanitization_type ) {
		case 'switch':
			$sanitization_func = '\RecycleBin\FrontPageBuddy\validate_switch';
			break;

		case 'email':
			$sanitization_func = '\sanitize_email';
			break;

		case 'key':
			$sanitization_func = '\sanitize_key';
			break;

		case 'slug':
			$sanitization_func = '\sanitize_title';
			break;

		case 'hexcolor':
			$sanitization_func = '\sanitize_hex_color';
			break;

		case 'textarea':
			$sanitization_func = '\sanitize_textarea';
			break;

		case 'basic_html':
			$sanitization_func = '\RecycleBin\FrontPageBuddy\sanitize_basic_html';
			break;

		default:
			$sanitization_func = '\sanitize_text_field';
			break;
	}

	if ( is_scalar( $field_value ) ) {
		$field_value = call_user_func( $sanitization_func, $field_value );
	} elseif ( is_array( $field_value ) ) {
		$count_val = count( $field_value );
		for ( $i = 0; $i < $count_val; $i++ ) {
			$field_value[ $i ] = call_user_func( $sanitization_func, $field_value[ $i ] );
		}
	}

	return $field_value;
}

/**
 * Validate the value of a 'switch' field.
 *
 * Returns 'yes' if the value is already 'yes'.
 * Returns 'no' otherwise.
 *
 * @param string $value Current value.
 * @return string
 */
function validate_switch( $value ) {
	return 'yes' === strtolower( $value ) ? 'yes' : 'no';
}

/**
 * Filter the value to include only allowed html tags and their attributes.
 * Strip all other html.
 *
 * @param string $value raw html.
 * @return string
 */
function sanitize_basic_html( $value ) {
	return wp_kses( $value, basic_html_allowed_tags() );
}

/**
 * Get the list of html tags( and their attributes ) allowed.
 * This is used to sanitize the contents of richcontent widget.
 *
 * @since 1.0.0
 * @return array
 */
function visual_editor_allowed_html_tags() {
	return apply_filters(
		'fronpage_buddy_visual_editor_allowed_html_tags',
		array(
			'h2'     => array(),
			'h3'     => array(),
			'h4'     => array(),
			'p'      => array(),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'del'    => array(),
			'a'      => array(
				'href'  => array(),
				'title' => array(),
			),
			'img'    => array(
				'src' => array(),
				'alt' => array(),
			),
			'ul'     => array(),
			'ol'     => array(),
			'li'     => array(),
			'hr'     => array(),
		)
	);
}

/**
 * Get the list of html tags( and their attributes ) allowed.
 * This is used to sanitize the contents of integration and widget descriptions, before showing those in admin settings screen( and elsewhere, if applicable ).
 *
 * @since 1.0.0
 * @return array
 */
function basic_html_allowed_tags() {
	return apply_filters(
		'fronpage_buddy_admin_descriptions_allowed_html_tags',
		array(
			'h2'     => array(),
			'h3'     => array(),
			'h4'     => array(),
			'div'    => array(
				'class' => array(),
			),
			'p'      => array(
				'class' => array(),
			),
			'span'   => array(
				'class' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'del'    => array(),
			'a'      => array(
				'href'  => array(),
				'title' => array(),
				'class' => array(),
			),
			'img'    => array(
				'src'   => array(),
				'alt'   => array(),
				'class' => array(),
			),
			'ul'     => array(),
			'ol'     => array(),
			'li'     => array(),
			'hr'     => array(),
		)
	);
}

\add_action( 'frontpage_buddy_manage_frontpage__after', '\RecycleBin\FrontPageBuddy\manage_screen_theme_setup' );

/**
 * Output the colors etc as set in plugin options.
 * These are output as css variables and are used to style the individual elements.
 *
 * @return void
 */
function manage_screen_theme_setup() {
	?>
	<style type="text/css">
	.fpbuddy_manage_widgets {
		--fpbuddy-editor-color-bg: <?php echo esc_attr( frontpage_buddy()->option( 'editor_color_bg' ) ); ?>;
		--fpbuddy-editor-color-text: <?php echo esc_attr( frontpage_buddy()->option( 'editor_color_text' ) ); ?>;
		--fpbuddy-editor-color-main: <?php echo esc_attr( frontpage_buddy()->option( 'editor_color_primary' ) ); ?>;
		--fpbuddy-editor-color-main-contrast: <?php echo esc_attr( frontpage_buddy()->option( 'editor_color_primary_contrast' ) ); ?>;
	}
	</style>
	<?php
}
