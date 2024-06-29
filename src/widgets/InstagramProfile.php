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
	public function __construct( $args = '' ) {
		$this->type           = 'instagramprofileembed';
		$this->name           = __( 'Instagram Profile', 'frontpage-buddy' );
		$this->description    = __( 'Showcase an instagram profile.', 'frontpage-buddy' );
		$this->icon_image_url = FPBUDDY_PLUGIN_URL . 'assets/images/icon-richtext.png';

		$this->setup( $args );
	}

	public function get_fields() {
		$fields = array();

		$fields['insta_id'] = array(
			'type'        => 'textbox',
			'label'       => 'Instagram Id',
			'value'       => ! empty( $this->edit_field_value( 'insta_id' ) ) ? $this->edit_field_value( 'insta_id' ) : '',
			'is_required' => true,
		);

		return $fields;
	}

	public function get_output() {
		$insta_id = $this->view_field_val( 'insta_id' );
		$insta_id = trim( $insta_id, ' /@' );
		if ( empty( $insta_id ) ) {
			return '';
		}

		$instagram_url = 'https://www.instagram.com/' . $insta_id . '/';

		wp_enqueue_script( 'instagram-embed', 'https://www.instagram.com/embed.js', array(), '12' );

		/* setting width 100% is mandatory so that the instagram widget can take up full space of its container */
		return sprintf( "<blockquote class='instagram-media' data-instgrm-permalink='%s' data-instgrm-version='12' style='width:100%%;'></blockquote>", esc_attr( $instagram_url ) );
	}
}
