<?php
/**
 * Richcontent widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

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
		$this->type        = 'richcontent';
		$this->name        = __( 'Rich Text', 'frontpage-buddy' );
		$this->description = __( 'Add text/copy, headings, links, lists etc.', 'frontpage-buddy' );
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
			'type'    => 'checkbox',
			'label'   => __( 'Editor elements', 'frontpage-buddy' ),
			'value'   => $this->get_option( 'editor_elements' ),
			'options' => array(
				'h2'     => __( 'Heading 2', 'frontpage-buddy' ),
				'h3'     => __( 'Heading 3', 'frontpage-buddy' ),
				'h4'     => __( 'Heading 4', 'frontpage-buddy' ),

				'ul'     => __( 'Unorderd list', 'frontpage-buddy' ),
				'ol'     => __( 'Ordered list', 'frontpage-buddy' ),
				'p'      => __( 'Paragraph', 'frontpage-buddy' ),

				'br'     => __( 'Line Break', 'frontpage-buddy' ),
				'hr'     => __( 'Horizontal Rule', 'frontpage-buddy' ),

				'a'      => __( 'Anchor/Link', 'frontpage-buddy' ),

				'em'     => __( 'Emphasis/Italicize', 'frontpage-buddy' ),
				'strong' => __( 'Bolden', 'frontpage-buddy' ),
				'del'    => __( 'Strike through', 'frontpage-buddy' ),
			),

			'description' => __( 'Choose the list of elements allowed in rich text editors.', 'frontpage-buddy' ),
		);

		return $fields;
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

		switch ( $option_name ) {
			case 'editor_elements':
				$option_value = null !== $option_value && ! empty( $option_value ) ? $option_value : array();
				if ( empty( $option_value ) ) {
					$option_value = array(
						'h2',
						'h3',
						'h4',

						'ul',
						'ol',
						'p',

						'br',
						'hr',

						'em',
						'strong',
						'del',
					);
				}
				break;
		}

		return $option_value;
	}

	/**
	 * Get all the data 'fields' for the settings/options screen for this widget.
	 *
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 *
	 * @return array
	 */
	public function get_data_fields( $widget ) {
		$fields = $this->get_default_data_fields( $widget );

		$fields['content'] = array(
			'type'        => 'wp_editor',
			'label'       => '',
			'value'       => ! empty( $widget->get_data( 'content', 'edit' ) ) ? $widget->get_data( 'content', 'edit' ) : '',
			'is_required' => true,
		);

		return $fields;
	}

	/**
	 * Get the html output for this widget.
	 *
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 * @return string
	 */
	public function get_output( $widget ) {
		$html = $widget->get_data( 'content', 'view' );

		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start( $widget ) . $html . $this->output_end( $widget ), $this, $widget );
	}
}
