<?php
/**
 * Miscellaneous utility functions.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy;

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
 * @return string html
 */
function generate_form_fields( $fields, $args = '' ) {
	$output = '';
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

	$output .= $args['before_list'];

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

		$output .= str_replace( '{{FIELD_CLASS}}', $cssclass, $args['before_field'] );

		$output .= $args['before_label'];
		if ( isset( $field['label'] ) && ! empty( $field['label'] ) ) {
			$output .= '<label for="' . esc_attr( $field_id ) . '">' . esc_html( $field['label'] ) . '</label>';
		}
		$output .= $args['after_label'];

		$output .= $args['before_input'];

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

		$output .= $html;

		$output .= $args['after_input'];

		$output .= $args['after_field'];
	}

	return $output;
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
			$sanitization_func = '\RB\FrontPageBuddy\validate_switch';
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
			$sanitization_func = '\RB\FrontPageBuddy\sanitize_basic_html';
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
	$rich_text = frontpage_buddy()->get_widget_type( 'richcontent' );
	if ( empty( $rich_text ) ) {
		return array();
	}

	$editor_elements = $rich_text->get_option( 'editor_elements' );
	if ( empty( $editor_elements ) ) {
		return array();
	}

	$tags_allowed = array();
	$common_attrs = html_elements_common_safe_attrs();

	if ( ! empty( $editor_elements ) ) {
		if ( in_array( 'formatting', $editor_elements, true ) ) {
			$tags_allowed['h1']         = $common_attrs;
			$tags_allowed['h2']         = $common_attrs;
			$tags_allowed['h3']         = $common_attrs;
			$tags_allowed['h4']         = $common_attrs;
			$tags_allowed['h5']         = $common_attrs;
			$tags_allowed['h6']         = $common_attrs;
			$tags_allowed['div']        = $common_attrs;
			$tags_allowed['section']    = $common_attrs;
			$tags_allowed['p']          = $common_attrs;
			$tags_allowed['span']       = $common_attrs;
			$tags_allowed['blockquote'] = $common_attrs;
		}

		if ( in_array( 'strong', $editor_elements, true ) ) {
			$tags_allowed['strong'] = $common_attrs;
		}
		if ( in_array( 'em', $editor_elements, true ) ) {
			$tags_allowed['em'] = $common_attrs;
		}
		if ( in_array( 'del', $editor_elements, true ) ) {
			$tags_allowed['del'] = $common_attrs;
		}
		if ( in_array( 'a', $editor_elements, true ) ) {
			$tags_allowed['a'] = array_merge(
				$common_attrs,
				array(
					'href' => true,
				)
			);
		}
		$add_list_item = false;
		if ( in_array( 'ul', $editor_elements, true ) ) {
			$tags_allowed['ul'] = $common_attrs;
			$add_list_item      = true;
		}
		if ( in_array( 'ol', $editor_elements, true ) ) {
			$tags_allowed['ol'] = $common_attrs;
			$add_list_item      = true;
		}
		if ( $add_list_item ) {
			$tags_allowed['li'] = $common_attrs;
		}

		if ( in_array( 'hr', $editor_elements, true ) ) {
			$tags_allowed['hr'] = $common_attrs;
		}
	}

	return apply_filters(
		'frontpage_buddy_visual_editor_allowed_html_tags',
		$tags_allowed
	);
}

/**
 * Get the list of basic & safe html tags( and their attributes ) allowed.
 *
 * @since 1.0.0
 * @return array
 */
function basic_html_allowed_tags() {
	$common_attrs = html_elements_common_safe_attrs();

	$basic_tags = array(
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'div',
		'section',
		'p',
		'blockquote',
		'span',
		'i',
		'em',
		'strong',
		'ins',
		'del',
		'sup',
		'sub',
		'br',
		'hr',
		'table',
		'thead',
		'tbody',
		'tfoot',
		'tr',
		'th',
		'td',
		'ul',
		'ol',
		'li',
	);

	$compiled = array();
	foreach ( $basic_tags as $tag ) {
		$compiled[ $tag ] = $common_attrs;
	}

	$compiled['a'] = array_merge(
		$common_attrs,
		array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
		)
	);

	/*
	$compiled['img'] = array_merge(
		$common_attrs,
		array(
			'src' => true,
			'srcset' => true,
		)
	);*/

	return apply_filters( 'frontpage_buddy_basic_allowed_html_tags', $compiled );
}

/**
 * Get the list of html tags( and their attributes ) allowed for form elements.
 * This is used to sanitize the contents of integration and widget setting fields.
 *
 * @since 1.0.0
 * @return array
 */
function form_elements_allowed_tags() {
	$common_attrs = html_elements_common_safe_attrs();
	$form_tags    = array(
		'label'    => array_merge(
			$common_attrs,
			array(
				'for' => true,
			)
		),
		'input'    => array_merge(
			$common_attrs,
			array(
				'type'        => true,
				'checked'     => true,
				'value'       => true,
				'placeholder' => true,
				'min'         => true,
				'max'         => true,
			)
		),
		'textarea' => array_merge(
			$common_attrs,
			array(
				'rows'        => true,
				'cols'        => true,
				'placeholder' => true,
			)
		),
		'button'   => array_merge(
			$common_attrs,
			array(
				'type'        => true,
				'value'       => true,
				'placeholder' => true,
			)
		),
		'select'   => array_merge(
			$common_attrs,
			array(
				'multiple'    => true,
				'value'       => true,
				'placeholder' => true,
			)
		),
		'options'  => array(
			'id'    => true,
			'class' => true,
			'value' => true,
		),
	);

	return apply_filters( 'frontpage_buddy_form_allowed_html_tags', $form_tags );
}

/**
 * List of common attributes of html elements.
 *
 * @return array
 */
function html_elements_common_safe_attrs() {
	return array(
		'disabled'         => true,
		'readonly'         => true,
		'aria-controls'    => true,
		'aria-current'     => true,
		'aria-describedby' => true,
		'aria-details'     => true,
		'aria-expanded'    => true,
		'aria-hidden'      => true,
		'aria-label'       => true,
		'aria-labelledby'  => true,
		'aria-live'        => true,
		'data-*'           => true,
		'dir'              => true,
		'hidden'           => true,
		'lang'             => true,
		'style'            => true,
		'title'            => true,
		'role'             => true,
		'xml:lang'         => true,
		'class'            => true,
		'id'               => true,
		'name'             => true,
		'colspan'          => true,
	);
}
