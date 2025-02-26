<?php
/**
 * Richcontent widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) || exit;

/**
 *  Richcontent widget.
 */
class RichContent extends WidgetType {
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->type              = 'richcontent';
		$this->name              = __( 'Rich Text', 'frontpage-buddy' );
		$this->description       = __( 'Add text/copy, headings, links, lists etc.', 'frontpage-buddy' );
		$this->description_admin = __( 'Displays a rich-text-editor, allowing users to enter text, links, etc. Also has basic formatting options like "bold", "italics", etc.', 'frontpage-buddy' );

		parent::__construct();
	}

	/**
	 * Get the fields for specific settings of this widget type, if any.
	 *
	 * @return array
	 */
	public function get_settings_fields() {
		$fields = parent::get_settings_fields();

		$fields['editor_elements'] = array(
			'type'        => 'checkbox',
			'label'       => __( 'Editor elements', 'frontpage-buddy' ),
			'value'       => $this->get_option( 'editor_elements' ),
			'options'     => array(
				'undo-redo'     => __( 'Undo & Redo', 'frontpage-buddy' ),
				'formatting'    => __( 'Formatting - Quote/Paragraph/Headers', 'frontpage-buddy' ),
				'justifyLeft'   => __( 'Align left', 'frontpage-buddy' ),
				'justifyCenter' => __( 'Align center', 'frontpage-buddy' ),
				'justifyRight'  => __( 'Align right', 'frontpage-buddy' ),
				'em'            => __( 'Emphasis/Italicize', 'frontpage-buddy' ),
				'strong'        => __( 'Bolden', 'frontpage-buddy' ),
				'del'           => __( 'Strike through', 'frontpage-buddy' ),
				'a'             => __( 'Link', 'frontpage-buddy' ),
				'ul'            => __( 'Unorderd list', 'frontpage-buddy' ),
				'ol'            => __( 'Ordered list', 'frontpage-buddy' ),
				'hr'            => __( 'Horizontal Rule', 'frontpage-buddy' ),
				'fullscreen'    => __( 'Full screen', 'frontpage-buddy' ),
			),

			'description' => __( 'Choose the buttons/options allowed in rich text editors.', 'frontpage-buddy' ),
		);

		return $fields;
	}

	/**
	 * Get an option's/setting's default value.
	 * This function is to be overloaded by widgets.
	 *
	 * @param mixed                      $option_value value of the option.
	 * @param string                     $option_name  name of the option.
	 * @param \FrontPageBuddy\WidgetType $widget_type  Widget type object.
	 *
	 * @return mixed null if no default value is to be provided.
	 */
	public function filter_option_value( $option_value, $option_name, $widget_type ) {
		if ( $widget_type->type !== $this->type ) {
			return $option_value;
		}

		switch ( $option_name ) {
			case 'editor_elements':
				$option_value = null !== $option_value && ! empty( $option_value ) ? $option_value : array();
				if ( empty( $option_value ) ) {
					$option_value = array(
						'undo-redo',
						'formatting',
						'removeformat',
						'em',
						'strong',
						'del',
						'ul',
						'ol',
						'hr',
						'fullscreen',
					);
				}
				break;
		}

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
		$trumbowyg_btns  = array();
		$editor_elements = $this->get_option( 'editor_elements' );
		if ( ! empty( $editor_elements ) ) {
			if ( in_array( 'undo-redo', $editor_elements, true ) ) {
				$trumbowyg_btns[] = array( 'undo', 'redo' );
			}

			if ( in_array( 'formatting', $editor_elements, true ) ) {
				$trumbowyg_btns[] = array( 'formatting', 'removeformat' );
			}

			$new_group = array();
			if ( in_array( 'strong', $editor_elements, true ) ) {
				$new_group[] = 'strong';
			}
			if ( in_array( 'em', $editor_elements, true ) ) {
				$new_group[] = 'em';
			}
			if ( in_array( 'del', $editor_elements, true ) ) {
				$new_group[] = 'del';
			}
			if ( ! empty( $new_group ) ) {
				$trumbowyg_btns[] = $new_group;
			}

			$new_group = array();
			if ( in_array( 'justifyLeft', $editor_elements, true ) ) {
				$new_group[] = 'justifyLeft';
			}
			if ( in_array( 'justifyCenter', $editor_elements, true ) ) {
				$new_group[] = 'justifyCenter';
			}
			if ( in_array( 'justifyRight', $editor_elements, true ) ) {
				$new_group[] = 'justifyRight';
			}
			if ( ! empty( $new_group ) ) {
				$trumbowyg_btns[] = $new_group;
			}

			if ( in_array( 'a', $editor_elements, true ) ) {
				$trumbowyg_btns[] = array( 'link' );
			}

			$new_group = array();
			if ( in_array( 'ul', $editor_elements, true ) ) {
				$new_group[] = 'unorderedList';
			}
			if ( in_array( 'ol', $editor_elements, true ) ) {
				$new_group[] = 'orderedList';
			}
			if ( ! empty( $new_group ) ) {
				$trumbowyg_btns[] = $new_group;
			}

			if ( in_array( 'hr', $editor_elements, true ) ) {
				$trumbowyg_btns[] = array( 'horizontalRule' );
			}

			if ( in_array( 'fullscreen', $editor_elements, true ) ) {
				$trumbowyg_btns[] = array( 'fullscreen' );
			}
		}

		$data['rich_content'] = array(
			'editor_btns' => $trumbowyg_btns,
		);
		return $data;
	}

	/**
	 * Get all the data 'fields' for the settings/options screen for this widget.
	 *
	 * @param \FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 *
	 * @return array
	 */
	public function get_data_fields( $widget ) {
		$fields = $this->get_default_data_fields( $widget );

		$fields['content'] = array(
			'type'        => 'richtext_editor',
			'label'       => '',
			'value'       => ! empty( $widget->get_data( 'content', 'edit' ) ) ? $widget->get_data( 'content', 'edit' ) : '',
			'is_required' => true,
			'attributes'  => array(
				'placeholder' => __( 'Enter text here..', 'frontpage-buddy' ),
			),
		);

		return $fields;
	}

	/**
	 * Get the html output for this widget.
	 *
	 * @param \FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 * @return string
	 */
	public function get_output( $widget ) {
		$html = $widget->get_data( 'content', 'view' );
		if ( ! empty( $html ) ) {
			$html = wp_kses( $html, \FrontPageBuddy\visual_editor_allowed_html_tags() );
		}

		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start( $widget ) . $html . $this->output_end( $widget ), $this, $widget );
	}
}
