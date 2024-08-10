<?php
/**
 * Instagram profile embed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Embed twitter feed.
 */
class InstagramProfile extends WidgetType {
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->type        = 'instagramprofile';
		$this->name        = __( 'Instagram Profile', 'frontpage-buddy' );
		$this->description = __( 'Showcase an instagram profile.', 'frontpage-buddy' );
		$this->icon_image  = '<i class="gg-instagram"></i>';

		parent::__construct();
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

		$fields['insta_id'] = array(
			'type'        => 'text',
			'label'       => 'Instagram Id',
			'value'       => ! empty( $widget->get_data( 'insta_id', 'edit' ) ) ? $widget->get_data( 'insta_id', 'edit' ) : '',
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
		$insta_id = $widget->get_data( 'insta_id', 'view' );
		$insta_id = trim( $insta_id, ' /@' );
		if ( empty( $insta_id ) ) {
			return '';
		}

		$instagram_url = 'https://www.instagram.com/' . $insta_id . '/';

		wp_enqueue_script( 'instagram-embed', 'https://www.instagram.com/embed.js', array(), '12', array( 'in_footer' => true ) );

		/* setting width 100% is mandatory so that the instagram widget can take up full space of its container */
		$html = sprintf( "<blockquote class='instagram-media' data-instgrm-permalink='%s' data-instgrm-version='12' style='width:100%%;'></blockquote>", esc_attr( $instagram_url ) );

		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start( $widget ) . $html . $this->output_end( $widget ), $this );
	}
}
