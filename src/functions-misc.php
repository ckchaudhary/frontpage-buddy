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
	if ( file_exists( get_stylesheet_directory() . '/frontpage_buddy/' . $template ) ) {
		include get_stylesheet_directory() . '/frontpage_buddy/' . $template;
	} elseif ( file_exists( get_template_directory() . '/frontpage_buddy/' . $template ) ) {
		include get_template_directory() . '/frontpage_buddy/' . $template;
	} else {
		include FPBUDDY_PLUGIN_DIR . 'templates/frontpage_buddy/' . $template;
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
		'before_field' => '',
		'after_field'  => '',
		'form_id'      => '',
	);

	$args = array_merge( $defaults, $args );

	// phpcs:ignore WordPress.Security.EscapeOutput
	echo $args['before_list'];

	foreach ( $fields as $field_name => $field ) {
		$field_defaults = array(
			'id'            => '',
			'label'         => '',
			'before'        => '',
			'before_inside' => '',
			'after_inside'  => '',
			'after'         => '',
			'wrapper_class' => '',
			'type'          => 'text',
		);
		$field = wp_parse_args( $field, $field_defaults );

		$field_id = $field_name . '_' . \uniqid();

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_field'];

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $field['before'];

		$cssclass = 'field field-' . $field_name . ' field-' . $field['type'];
		if ( $field['wrapper_class'] ) {
			$cssclass .= ' ' . $field['wrapper_class'];
		}

		echo "<div class='" . esc_attr( $cssclass ) . "'>";
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $field['before_inside'];

		switch ( $field['type'] ) {
			case 'checkbox':
			case 'radio':
				// Label.
				$html = '<label>' . esc_html( $field['label'] ) . '</label>';
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

					// Attributes.
					if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
						foreach ( $field['attributes'] as $att_name => $att_val ) {
							$html .= sprintf( ' %s="%s" ', esc_html( $att_name ), esc_attr( $att_val ) );
						}
					}

					$html .= ' />' . esc_html( $option_label ) . '</label>';
				}

				// Description.
				if ( isset( $field['description'] ) && $field['description'] ) {
					$html .= "<span class='field_description'>" . $field['description'] . '</span>';
				}

				// phpcs:ignore WordPress.Security.EscapeOutput
				echo $html;
				break;

			case 'switch':
				$html = '';
				if ( isset( $field['label'] ) && ! empty( $field['label'] ) ) {
					$html .= '<label>' . esc_html( $field['label'] ) . '</label>';
				}

				// Attributes.
				$attributes = isset( $field['value'] ) && 'yes' === $field['value'] ? 'checked' : '';
				if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
					foreach ( $field['attributes'] as $att_name => $att_val ) {
						$attributes .= sprintf( ' %s="%s" ', esc_html( $att_name ), esc_attr( $att_val ) );
					}
				}

				$html .= sprintf(
					'<label class="fpbuddy-switch">	
						<input type="checkbox" name="%1$s" value="yes" %2$s>
						<span class="switch-mask"></span>
						<span class="switch-labels">
							<span class="label-on">%3$s</span>
							<span class="label-off">%4$s</span>
						</span>
					</label>',
					esc_attr( $field_name ),
					$attributes,
					esc_html( $field['label_on'] ),
					esc_html( $field['label_off'] )
				);

				// Description.
				if ( isset( $field['description'] ) && $field['description'] ) {
					$html .= "<span class='field_description'>" . $field['description'] . '</span>';
				}

				// phpcs:ignore WordPress.Security.EscapeOutput
				echo $html;
				break;

			case 'select':
				// Label.
				$html = sprintf(
					'<label for="%1$s">%2$s</label><select id="%1$s" name="%3$s"',
					esc_attr( $field_id ),
					esc_html( $field['label'] ),
					esc_attr( $field_name )
				);

				// Attributes.
				if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
					foreach ( $field['attributes'] as $att_name => $att_val ) {
						$html .= sprintf( ' %s="%s" ', esc_html( $att_name ), esc_attr( $att_val ) );
					}
				}

				$html .= '>';

				foreach ( $field['options'] as $option_val => $option_label ) {
					$html .= "<option value='" . esc_attr( $option_val ) . "' ";

					// checked ?
					if ( isset( $field['value'] ) && ! empty( $field['value'] ) ) {
						if ( is_array( $field['value'] ) ) {
							if ( in_array( $option_val, $field['value'] ) ) {
								$html .= " selected='selected'";
							}
						} elseif ( $option_val == $field['value'] ) {
								$html .= " selected='selected'";
						}
					}

					$html .= '>' . esc_html( $option_label ) . '</option>';
				}

				$html .= '</select>';

				// Description.
				if ( isset( $field['description'] ) && $field['description'] ) {
					$html .= "<span class='field_description'>" . esc_html( $field['description'] ) . '</span>';
				}

				// phpcs:ignore WordPress.Security.EscapeOutput
				echo $html;
				break;
			case 'textarea':
			case 'wp_editor':
				// Label.
				$html = sprintf(
					'<label for="%1$s">%2$s</label><textarea id="%1$s" name="%3$s"',
					esc_attr( $field_id ),
					esc_html( $field['label'] ),
					esc_attr( $field_name )
				);

				// Attributes.
				if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
					foreach ( $field['attributes'] as $att_name => $att_val ) {
						$html .= sprintf( ' %s="%s" ', esc_html( $att_name ), esc_attr( $att_val ) );
					}
				}
				$html .= ' >';

				$field['value'] = esc_textarea( $field['value'] );
				if ( isset( $field['value'] ) && $field['value'] ) {
					$html .= $field['value'];
				}

				$html .= '</textarea>';

				// Description.
				if ( isset( $field['description'] ) && $field['description'] ) {
					$html .= "<span class='field_description'>" . esc_html( $field['description'] ) . '</span>';
				}

				// phpcs:ignore WordPress.Security.EscapeOutput
				echo $html;
				break;

			case 'button':
			case 'submit':
				$html = '<label for="' . esc_attr( $field_id ) . '">' . esc_html( $field['label'] ) . '</label>';

				$field_type = 'submit';
				if ( isset( $field['type'] ) ) {
					$field_type = $field['type'];
				}

				if ( 'button' === $field_type ) {
					$html .= '<button id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '" ';
				} else {
					$html .= '<input type="' . esc_attr( $field_type ) . '" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_name ) . '" ';
				}

				// Attributes.
				if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
					foreach ( $field['attributes'] as $att_name => $att_val ) {
						$html .= sprintf( ' %s="%s" ', esc_html( $att_name ), esc_attr( $att_val ) );
					}
				}

				if ( 'button' === $field_type ) {
					$html .= '>';
					if ( isset( $field['value'] ) && $field['value'] ) {
						$html .= $field['value'];
					}
					$html .= '</button>';
				} else {
					if ( isset( $field['value'] ) && $field['value'] ) {
						$html .= ' value="' . esc_attr( $field['value'] ) . '" ';
					}
					$html .= ' />';
				}

				// Description.
				if ( isset( $field['description'] ) && $field['description'] ) {
					$html .= '<span class="field_description">' . esc_html( $field['description'] ) . '</span>';
				}

				// phpcs:ignore WordPress.Security.EscapeOutput
				echo $html;
				break;

			case 'label':
				echo '<label for="' . esc_attr( $field_id ) . '" >' . esc_html( $field['label'] ) . '</label>';
				break;

			default:
				// Label.
				$html = sprintf(
					'<label for="%1$s">%2$s</label><input id="%1$s" name="%3$s" type="%4$s"',
					esc_attr( $field_id ),
					esc_html( $field['label'] ),
					esc_attr( $field_name ),
					esc_attr( $field[ 'type' ] )
				);

				// Attributes.
				if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
					foreach ( $field['attributes'] as $att_name => $att_val ) {
						$html .= sprintf( ' %s="%s" ', esc_html( $att_name ), esc_attr( $att_val ) );
					}
				}

				// Value.
				if ( isset( $field['value'] ) ) {
					$html .= ' value="' . esc_attr( $field['value'] ) . '" ';
				}

				$html .= ' />';

				// Description.
				if ( isset( $field['description'] ) && $field['description'] ) {
					$html .= '<span class="field_description">' . esc_html( $field['description'] ) . '</span>';
				}

				// phpcs:ignore WordPress.Security.EscapeOutput
				echo $html;
				break;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $field['after_inside'];
		echo '</div><!-- .field -->';

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $field['after'];
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['after_field'];
	}

	// phpcs:ignore WordPress.Security.EscapeOutput
	echo $args['after_list'];
}
