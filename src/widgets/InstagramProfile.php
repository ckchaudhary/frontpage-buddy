<?php
/**
 * Instagram profile embed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Embed twitter feed.
 */
class InstagramProfile extends Widget {
	/**
	 * Constructor.
	 *
	 * @param string $type A unique identifier.
	 * @param mixed  $args Initial data for the widget. e.g: id, options etc.
	 */
	public function __construct( $type, $args = '' ) {
		$this->type           = $type;
		$this->name           = __( 'Instagram Profile', 'frontpage-buddy' );
		$this->description    = __( 'Showcase an instagram profile.', 'frontpage-buddy' );
		$this->icon_image_url = FPBUDDY_PLUGIN_URL . 'assets/images/icon-instagram-feed.png';

		$this->setup( $args );
	}

	/**
	 * Get the fields for setting up this widget.
	 *
	 * @return array
	 */
	public function get_fields() {
		$fields = $this->get_default_fields();

		$fields['insta_id'] = array(
			'type'        => 'text',
			'label'       => 'Instagram Id',
			'value'       => ! empty( $this->edit_field_value( 'insta_id' ) ) ? $this->edit_field_value( 'insta_id' ) : '',
			'is_required' => true,
		);

		return $fields;
	}

	/**
	 * Get the html output for this widget.
	 *
	 * @return string
	 */
	public function get_output() {
		$insta_id = $this->view_field_val( 'insta_id' );
		$insta_id = trim( $insta_id, ' /@' );
		if ( empty( $insta_id ) ) {
			return '';
		}

		$instagram_url = 'https://www.instagram.com/' . $insta_id . '/';

		wp_enqueue_script( 'instagram-embed', 'https://www.instagram.com/embed.js', array(), '12', array( 'in_footer' => true ) );

		/* setting width 100% is mandatory so that the instagram widget can take up full space of its container */
		$html = sprintf( "<blockquote class='instagram-media' data-instgrm-permalink='%s' data-instgrm-version='12' style='width:100%%;'></blockquote>", esc_attr( $instagram_url ) );

		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start() . $html . $this->output_end(), $this );
	}
}
