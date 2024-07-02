<?php
/**
 * Richcontent widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Richcontent widget.
 */
class RichContent extends Widget {
	/**
	 * Constructor.
	 *
	 * @param string $type A unique identifier.
	 * @param mixed  $args Initial data for the widget. e.g: id, options etc.
	 */
	public function __construct( $type, $args = '' ) {
		$this->type        = $type;
		$this->name        = __( 'Rich Text', 'frontpage-buddy' );
		$this->description = __( 'Add text/copy, headings, links, lists, insert images, etc.', 'frontpage-buddy' );
		$link              = '<a href="https://blogs.recycleb.in/2024/07/frontpage-buddy-custom-front-pages-for-buddypress-users-groups#widget-richtext">' . __( 'Know More', 'frontpage-buddy' ) . '</a>';
		// translators: external link to read technical details about the widget.
		$this->description_admin = sprintf( __( "Displays a rich-text-editor, allowing users to enter text, links, images etc. Also has basic formatting options like 'bold', 'italics', etc. %s", "frontpage-buddy" ), $link );
		$this->icon_image_url    = FPBUDDY_PLUGIN_URL . 'assets/images/icon-richtext.png';

		$this->setup( $args );
	}

	public function get_fields() {
		$fields = array();

		$fields['content'] = array(
			'type'        => 'wp_editor',
			'label'       => '',
			'value'       => ! empty( $this->edit_field_value( 'content' ) ) ? $this->edit_field_value( 'content' ) : '',
			'is_required' => true,
		);

		return $fields;
	}

	public function get_output() {
		return $this->view_field_val( 'content' );
	}
}
