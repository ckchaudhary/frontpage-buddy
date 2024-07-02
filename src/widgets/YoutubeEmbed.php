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
		$this->icon_image_url = FPBUDDY_PLUGIN_URL . 'assets/images/icon-youtube-embed.png';

		$this->setup( $args );
	}

	/**
	 * Get the fields for setting up this widget.
	 *
	 * @return array
	 */
	public function get_fields() {
		return array(
			'url' => array(
				'type'        => 'url',
				'label'       => __( 'Video url', 'frontpage-buddy' ),
				'description' => __( 'Enter the youtube video url.', 'frontpage-buddy' ),
				'value'       => ! empty( $this->edit_field_value( 'url' ) ) ? $this->edit_field_value( 'url' ) : '',
				'attributes'  => array( 'placeholder' => 'https://www.youtube.com/watch?v=hdcTmpvDO0I' ),
				'is_required' => true,
			),

			'fluid_width' => array(
				'type'      => 'switch',
				'label'     => __( 'Player width', 'frontpage-buddy' ),
				'value'     => ! empty( $this->edit_field_value( 'width' ) ) ? $this->edit_field_value( 'width' ) : '',
				'label_off' => __( 'Fixed', 'frontpage-buddy' ),
				'label_on'  => __( 'Fluid', 'frontpage-buddy' ),
			),

			/*
			'width'	=> array(
				'type'		  => 'select',
				'label'		  => __( 'Width', 'frontpage-buddy' ),
				'value'		  => ! empty( $this->edit_field_value( 'width' ) ) ? $this->edit_field_value( 'width' ) : 'full',
				'is_required' => true,
				'options'	  => array(
					'full'   => __( 'Full Width', 'frontpage-buddy' ),
					'large'  => __( 'Large', 'frontpage-buddy' ),
					'medium' => __( 'Medium', 'frontpage-buddy' ),
					'small'  => __( 'Small', 'frontpage-buddy' ),
				),
			)
			*/
		);
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

		$fluid_width = $this->view_field_val( 'fluid_width' );
		$full_width_class = ! empty( $fluid_width ) && 'yes' === $fluid_width ? 'fr-full-width' : '';

		$yt_attr = '?disablekb=1&rel=0';

		return '<div class="youtube-video-container ' . $full_width_class . '"><iframe ' . $wh_attr . ' style="max-width: 100%" type="text/html" src="' . esc_attr( $full_embed_url ) . esc_url( $yt_attr ) . '" frameborder="0" allowfullscreen></iframe></div>';
	}

	/**
	 * Get video id from url
	 *
	 * @param string $url Youtube video url.
	 * @return string
	 */
	public function getYoutubeIdFromUrl( $url ) {
		$parts = parse_url( $url );
		if ( isset( $parts['query'] ) ) {
			parse_str( $parts['query'], $qs );
			if ( isset( $qs['v'] ) ) {
				return $qs['v'];
			} elseif ( isset( $qs['vi'] ) ) {
				return $qs['vi'];
			}
		}
		if ( isset( $parts['path'] ) ) {
			$path = explode( '/', trim( $parts['path'], '/' ) );
			$count_path = count( $path );
			return $path[ $count_path - 1 ];
		}
		return false;
	}
}
