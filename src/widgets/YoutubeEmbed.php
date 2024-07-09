<?php
/**
 * Youtube video embed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Embed facebook page widget.
 */
class YoutubeEmbed extends Widget {
	/**
	 * Constructor.
	 *
	 * @param string $type A unique identifier.
	 * @param mixed  $args Initial data for the widget. e.g: id, options etc.
	 */
	public function __construct( $type, $args = '' ) {
		$this->type           = $type;
		$this->name           = __( 'Youtube Video', 'frontpage-buddy' );
		$this->description    = __( 'Embed a youtube video.', 'frontpage-buddy' );
		$this->icon_image     = '<i class="gg-youtube"></i>';

		$this->setup( $args );
	}

	/**
	 * Get the fields for setting up this widget.
	 *
	 * @return array
	 */
	public function get_fields() {
		$fields = $this->get_default_fields();

		$attrs_fluid_width = array();
		if ( 'yes' === $this->edit_field_value( 'width' ) ) {
			$attrs_fluid_width['checked'] = 'checked';
		}

		$fields['url'] = array(
			'type'        => 'url',
			'label'       => __( 'Video url', 'frontpage-buddy' ),
			'description' => __( 'Enter the youtube video url.', 'frontpage-buddy' ),
			'value'       => ! empty( $this->edit_field_value( 'url' ) ) ? $this->edit_field_value( 'url' ) : '',
			'attributes'  => array( 'placeholder' => 'https://www.youtube.com/watch?v=hdcTmpvDO0I' ),
			'is_required' => true,
		);

		$fields['fluid_width'] = array(
			'type'       => 'switch',
			'label'      => __( 'Player width', 'frontpage-buddy' ),
			'label_off'  => __( 'Fixed', 'frontpage-buddy' ),
			'label_on'   => __( 'Fluid', 'frontpage-buddy' ),
			'attributes' => $attrs_fluid_width,
		);

		return $fields;
	}

	/**
	 * Get the html output for this widget.
	 *
	 * @return string
	 */
	public function get_output() {
		$video_url = $this->view_field_val( 'url' );
		if ( empty( $video_url ) ) {
			return '';
		}

		$youtube_id = trim( $this->getYoutubeIdFromUrl( $video_url ) );
		if ( ! $youtube_id ) {
			return '';
		}

		$full_embed_url = 'https://www.youtube.com/embed/' . $youtube_id;
		$wh_attr        = 'width="560" height="315"';

		$fluid_width      = $this->view_field_val( 'fluid_width' );
		$full_width_class = ! empty( $fluid_width ) && 'yes' === $fluid_width ? 'fr-full-width' : '';

		$yt_attr = '?disablekb=1&rel=0';

		$html = '<div class="youtube-video-container ' . $full_width_class . '"><iframe ' . $wh_attr . ' style="max-width: 100%" type="text/html" src="' . esc_attr( $full_embed_url ) . esc_url( $yt_attr ) . '" frameborder="0" allowfullscreen></iframe></div>';
		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start() . $html . $this->output_end(), $this );
	}

	/**
	 * Get video id from url
	 *
	 * @param string $url Youtube video url.
	 * @return string
	 */
	public function getYoutubeIdFromUrl( $url ) {
		$parts = wp_parse_url( $url );
		if ( isset( $parts['query'] ) ) {
			parse_str( $parts['query'], $qs );
			if ( isset( $qs['v'] ) ) {
				return $qs['v'];
			} elseif ( isset( $qs['vi'] ) ) {
				return $qs['vi'];
			}
		}
		if ( isset( $parts['path'] ) ) {
			$path       = explode( '/', trim( $parts['path'], '/' ) );
			$count_path = count( $path );
			return $path[ $count_path - 1 ];
		}
		return false;
	}
}
