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

function show_output( $layout, $widgets, $integration_type, $target_id ) {
	echo get_output( $layout, $widgets, $integration_type, $target_id );
}

/**
 * Undocumented function
 *
 * @param [type] $layout
 * @param [type] $widgets
 * @param [type] $integration_type
 * @param [type] $target_id
 * @return void
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

if ( ! function_exists( 'emi_generate_fields' ) ) :
	/*
	 * function to generate the html for given form fields
	 *
	 * @param array $fields 
		an example input
		$fields = array(
		'full_name'     => array(
		'type'          => 'textbox',
		'label'         => 'Full Name',
		'sqlcolumn'     => 'user_name',
		'attributes'    => array(
		'class'=>'inputtype1',
		'placeholder'=>'Full Name'
		),
		'value'         => 'Mr. XYZ',
		'description'   => 'Enter your full name including your surname.'
		),

		'date_of_birth' => array(
		'type'          => 'textbox',
		'label'         => 'Date of Birth',
		'sqlcolumn'     => 'user_dob',
		'attributes'    => array(
		'class'=>'jqueryui-date'
		)
		),

		'gender'        => array(
		'type'          => 'radio',
		'label'         => 'Gender',
		'sqlcolumn'     => 'user_gender'
		'options'       => array(
		'male'      => 'Male',
		'female'    => 'Female'
		),
		'value'         => 'female'
		),

		'hobbies'        => array(
		'type'          => 'select',
		'label'         => 'Hobbies',
		'sqlcolumn'     => 'user_hobbies',
		'attributes'    => array(
		'multiple'=>''
		)
		'options'       => array(
		'11'     => 'Listening to music',
		'16'     => 'playing games',
		'5'     => 'Reading',
		),
		'value'         => array( '5', '11' )
		),
		);

	 *
	 * @param array $args options
	 * @return void
	 */
	function emi_generate_fields( $fields, $args = '' ) {

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
			'form_id'      => 'form1',
		);

		$args = array_merge( $defaults, $args );

		echo $args['before_list'];

		foreach ( $fields as $field_id => $field ) {
			$field_defaults = array(
				'label'         => '',
				'before'        => '',
				'before_inside' => '',
				'after_inside'  => '',
				'after'         => '',
				'wrapper_class' => '',
				'type'          => 'text',
			);

			$field = wp_parse_args( $field, $field_defaults );

			echo $args['before_field'];

			echo $field['before'];

			$cssclass = 'field field-' . $field_id . ' field-' . $field['type'];
			if ( $field['wrapper_class'] ) {
				$cssclass .= ' ' . $field['wrapper_class'];
			}

			echo "<div class='$cssclass' id='field-" . $field_id . "'>";
			echo $field['before_inside'];

			switch ( $field['type'] ) {
				case 'checkbox':
				case 'radio':
					// label
					$html = '<label>' . $field['label'] . '</label>';
					foreach ( $field['options'] as $option_val => $option_label ) {
						$html .= "<label class='label_option label_option_" . $field['type'] . "'><input type='" . $field['type'] . "' name='" . $field_id . "[]' value='$option_val' id='$option_val'";

						// checked ?
						if ( isset( $field['value'] ) && ! empty( $field['value'] ) ) {
							if ( is_array( $field['value'] ) ) {
								if ( in_array( $option_val, $field['value'] ) ) {
									$html .= " checked='checked'";
								}
							} elseif ( $option_val == $field['value'] ) {
									// $html .= " checked='checked'";
									$html .= '';
							}
						}

						// attributes
						if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
							foreach ( $field['attributes'] as $att_name => $att_val ) {
								$html .= " $att_name='" . esc_attr( $att_val ) . "'";
							}
						}

						$html .= " />$option_label</label>";
					}

					// description
					if ( isset( $field['description'] ) && $field['description'] ) {
						$html .= "<span class='field_description'>" . $field['description'] . '</span>';
					}

					echo $html;
					break;

				case 'select':
					// label
					$html  = "<label for='$field_id'>" . $field['label'] . '</label>';
					$html .= "<select id='$field_id' name='$field_id'";

					// attributes
					if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
						foreach ( $field['attributes'] as $att_name => $att_val ) {
							$html .= " $att_name='" . esc_attr( $att_val ) . "'";
						}
					}

					$html .= '>';

					foreach ( $field['options'] as $option_val => $option_label ) {
						$html .= "<option value='$option_val' ";

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

						$html .= ">$option_label</option>";
					}

					$html .= '</select>';

					// description
					if ( isset( $field['description'] ) && $field['description'] ) {
						$html .= "<span class='field_description'>" . $field['description'] . '</span>';
					}

					echo $html;
					break;
				case 'textarea':
					// label
					$html = "<label for='$field_id'>" . $field['label'] . '</label>';

					$html .= "<textarea type='text' id='$field_id' name='$field_id' ";
					// attributes
					if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
						foreach ( $field['attributes'] as $att_name => $att_val ) {
							$html .= " $att_name='" . esc_attr( $att_val ) . "'";
						}
					}
					$html .= ' >';

					// selected value
					$field['value'] = esc_textarea( $field['value'] );
					if ( isset( $field['value'] ) && $field['value'] ) {
						$html .= $field['value'];
					}

					$html .= '</textarea>';

					// description
					if ( isset( $field['description'] ) && $field['description'] ) {
						$html .= "<span class='field_description'>" . $field['description'] . '</span>';
					}

					echo $html;
					break;

				case 'wp_editor':
					// label
					$html = "<label for='$field_id'>" . $field['label'] . '</label>';

					$html .= "<textarea type='text' id='$field_id' name='$field_id' ";
					// attributes
					if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
						foreach ( $field['attributes'] as $att_name => $att_val ) {
							if ( 'style' == $att_name ) {
								continue;
							}
							$html .= " $att_name='" . esc_attr( $att_val ) . "'";
						}
					}

					// $html .= " style='display:none'";
					$html .= ' >';

					// selected value
					$field['value'] = esc_textarea( $field['value'] );
					if ( isset( $field['value'] ) && $field['value'] ) {
						$html .= $field['value'];
					}

					$html .= '</textarea>';

					// $html .= "<div class='content-editor' id='editor-$field_id' data-for='$field_id'>" . $field['value'] . "</div>";
					// description
					if ( isset( $field['description'] ) && $field['description'] ) {
						$html .= "<span class='field_description'>" . $field['description'] . '</span>';
					}

					echo $html;
					break;

				case 'button':
				case 'submit':
					$html = "<label for='$field_id'>" . $field['label'] . '</label>';

					$field_type = 'submit';
					if ( isset( $field['type'] ) ) {
						$field_type = $field['type'];
					}

					if ( $field_type == 'button' ) {
						$html .= "<button id='$field_id' name='$field_id'";
					} else {
						$html .= "<input type='$field_type' id='$field_id' name='$field_id'";
					}

					// attributes
					if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
						foreach ( $field['attributes'] as $att_name => $att_val ) {
							$html .= " $att_name='" . esc_attr( $att_val ) . "'";
						}
					}

					if ( $field_type == 'button' ) {
						$html .= '>';
						if ( isset( $field['value'] ) && $field['value'] ) {
							$html .= $field['value'];
						}
						$html .= '</button>';
					} else {
						if ( isset( $field['value'] ) && $field['value'] ) {
							$html .= " value='" . esc_attr( $field['value'] ) . "'";
						}
						$html .= ' />';
					}

					// description
					if ( isset( $field['description'] ) && $field['description'] ) {
						$html .= "<span class='field_description'>" . $field['description'] . '</span>';
					}

					echo $html;
					break;

				case 'repeater':
					// label

					$html = '';

					$html .= "<input type='hidden' data-isrepeater='1' id='$field_id' name='$field_id'";

					// attributes
					if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
						foreach ( $field['attributes'] as $att_name => $att_val ) {
							$html .= " $att_name='" . esc_attr( $att_val ) . "'";
						}
					}

					// selected value
					if ( isset( $field['value'] ) && $field['value'] ) {
						$html .= " value='" . esc_attr( $field['value'] ) . "'";
					}

					$html .= ' />';
					echo $html;

					if ( function_exists( 'do_action' ) ) {
						$actionname = 'emi_generate_form_repeater_' . $field_id;
						do_action( $actionname, $field, $args['form_id'] );
					}

					break;

				case 'label':
					echo $html = "<label for='$field_id'>" . $field['label'] . '</label>';
					break;

				default:
					// label
					$html = "<label for='$field_id'>" . $field['label'] . '</label>';

					$html .= "<input type='{$field[ 'type' ]}' id='$field_id' name='$field_id'";

					// attributes
					if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
						foreach ( $field['attributes'] as $att_name => $att_val ) {
							$html .= " $att_name='" . esc_attr( $att_val ) . "'";
						}
					}

					// selected value
					$field['value'] = $field['value'];
					if ( isset( $field['value'] ) && $field['value'] ) {
						$html .= " value='" . esc_attr( $field['value'] ) . "'";
					}

					$html .= ' />';

					// description
					if ( isset( $field['description'] ) && $field['description'] ) {
						$html .= "<span class='field_description'>" . $field['description'] . '</span>';
					}

					echo $html;
					break;
			}

			echo $field['after_inside'];
			echo '</div><!-- .field -->';

			echo $field['after'];
			echo $args['after_field'];
		}

		echo $args['after_list'];
	}



endif;