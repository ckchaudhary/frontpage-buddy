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
			'h2' => array(),
			'h3' => array(),
			'h4' => array(),
			'p' => array(),
			'br' => array(),
			'em' => array(),
			'strong' => array(),
			'del' => array(),
			'a' => array(
				'href'  => array(),
				'title' => array(),
			),
			'img' => array(
				'src' => array(),
				'alt' => array(),
			),
			'ul' => array(),
			'ol' => array(),
			'hr' => array(),
		)
	);
}

/**
 * Show/Print the output for custom front page.
 *
 * @param array  $layout Rows and columns.
 * @param array  $widgets All added widgets.
 * @param string $integration_type E.g: bp_groups.
 * @param mixed  $target_id E.g: group id.
 * @return void
 */
function show_output( $layout, $widgets, $integration_type, $target_id ) {
	// phpcs:ignore WordPress.Security.EscapeOutput
	echo get_output( $layout, $widgets, $integration_type, $target_id );
}

/**
 * Get the output for custom front page.
 *
 * @param array  $layout Rows and columns.
 * @param array  $widgets All added widgets.
 * @param string $integration_type E.g: bp_groups.
 * @param mixed  $target_id E.g: group id.
 * @return string html
 */
function get_output( $layout, $widgets, $integration_type, $target_id ) {
	$html = '';

	$registered_widgets = frontpage_buddy()->widget_collection()->get_registered_widgets();

	if ( ! empty( $layout ) ) {
		foreach ( $layout as $layout_row ) {
			$row = array();

			foreach ( $layout_row as $widget_id ) {
				$found = false;
				$widget_id = trim( $widget_id );
				if ( ! empty( $widgets ) ) {
					foreach ( $widgets as $widget ) {
						if ( $widget['id'] === $widget_id ) {
							$found = $widget;
							break;
						}
					}
				}

				if ( $found && ! frontpage_buddy()->widget_collection()->is_widget_enabled_for( $widget['type'], $integration_type, $target_id ) ) {
					$found = false;
				}

				if ( $found ) {
					$widget_obj = false;
					$widget_class = isset( $registered_widgets[ $widget['type'] ] ) && ! empty( $registered_widgets[ $widget['type'] ] ) ? $registered_widgets[ $widget['type'] ] : false;
					if ( $widget_class && class_exists( $widget_class ) ) {
						$widget_obj = new $widget_class(
							array(
								'id'          => $widget['id'],
								'object_type' => $integration_type,
								'object_id'   => $target_id,
								'options'     => $widget['options'],
							)
						);

						$widget_output = $widget_obj->get_output();
						if ( ! empty( $widget_output ) ) {
							$row[] = $widget_output;
						} else {
							$html .= 'one';
						}
					}
				}
			}

			if ( ! empty( $row ) ) {
				$col_count = count( $row );
				$html .= sprintf( "<div class='fpbuddy-widget-row has-%d-fpcols'>", $col_count );

				for ( $i = 0; $i < $col_count; $i++ ) {
					$this_col_num = $i + 1;
					$html .= sprintf( "<div class='fp-col fp-col-%d-of-%d'><div class='fp-col-contents'>%s</div></div>", $this_col_num, $col_count, stripslashes( $row[ $i ] ) );
				}

				$html .= '</div>';
			}
		}
	}

	return $html;
}

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

add_filter( 'frontpage_buddy_widget_title_for_manage_screen', '\RecycleBin\FrontPageBuddy\widget_title_for_manage_screen', 10, 2 );
/**
 * Filters the title for a widget when displayed on manage widgets screens.
 *
 * @param  string $title Existing value, if any.
 * @param  array  $widget Widget details like 'type', 'options' etc.
 * @return string
 */
function widget_title_for_manage_screen( $title, $widget ) {
	$widget_type = isset( $widget['type'] ) ? $widget['type'] : '';
	switch ( $widget_type ) {
		case 'richcontent':
			$content = isset( $widget['options'] ) && ! empty( $widget['options'] ) && isset( $widget['options']['content'] ) && ! empty( $widget['options']['content'] ) ? wp_strip_all_tags( $widget['options']['content'] ) : '';
			$title   = substr( $content, 0, 100 );
			break;

		case 'instagramprofileembed':
			$content = isset( $widget['options'] ) && ! empty( $widget['options'] ) && isset( $widget['options']['insta_id'] ) && ! empty( $widget['options']['insta_id'] ) ? wp_strip_all_tags( $widget['options']['insta_id'] ) : '';
			if ( $content ) {
				$content = trim( $content, ' @' );
				$title   = '@' . $content . ' - instagram';
			}
	}
	return $title;
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
					'<label for="%1$s">%$2s</label><textarea id="%1$s" name="%3$s"',
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
