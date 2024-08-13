<?php
/**
 * Youtube video embed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Embed facebook page widget.
 */
class YoutubeEmbed extends WidgetType {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->type        = 'youtubeembed';
		$this->name        = __( 'Youtube Video', 'frontpage-buddy' );
		$this->description = __( 'Embed a youtube video.', 'frontpage-buddy' );
		$this->icon_image  = '<i class="gg-youtube"></i>';

		$this->description_admin  = '<p>' . esc_html__( 'Enables your users to embed a youtube video.' ) . '</p>';
		$this->description_admin .= '<div class="notice notice-warning inline">';
		$this->description_admin .= '<p><strong>' . esc_html__( 'Use of 3rd party service.', 'frontpage-buddy' ) . '</strong><br>';
		$this->description_admin .= esc_html__( 'This widget makes use of an external API which may track your website visitor\'s data and may add cookies on their devices.', 'frontpage-buddy' ) . ' ';
		$this->description_admin .= esc_html__( 'Please update your privacy and cookie policies accordingly. It befalls on you ( the website administrator ) to collect opt-in consent beforehand.', 'frontpage-buddy' ) . ' ';
		$this->description_admin .= '</p>';

		$this->description_admin .= '<p>';
		$this->description_admin .= sprintf(
			/* translators: 1: https://www.youtube.com/embed/Youtube-video-id 2: link to https://www.youtube.com/static?gl=CA&template=terms */
			esc_html__( 'It makes use of an iframe to embed a youtube video. The iframe source is set to %1$s. Please check the policy at %2$s and ascertain what kind of information is sent to third party servers.', 'frontpage-buddy' ) . ' ',
			'https://www.youtube.com/embed/<strong>Youtube-video-id</strong>',
			'<a href="https://www.youtube.com/static?gl=CA&template=terms">https://www.youtube.com/static?gl=CA&template=terms</a>'
		);
		$this->description_admin .= esc_html__( 'If you have concerns, you should keep this widget disabled.', 'frontpage-buddy' );
		$this->description_admin .= '</p></div>';

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

		$attrs_fluid_width = array();
		if ( 'yes' === $widget->get_data( 'width', 'edit' ) ) {
			$attrs_fluid_width['checked'] = 'checked';
		}

		$fields['url'] = array(
			'type'        => 'url',
			'label'       => __( 'Video url', 'frontpage-buddy' ),
			'description' => __( 'Enter the youtube video url.', 'frontpage-buddy' ),
			'value'       => ! empty( $widget->get_data( 'url', 'edit' ) ) ? $widget->get_data( 'url', 'edit' ) : '',
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
	 * @param \RB\FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 * @return string
	 */
	public function get_output( $widget ) {
		$video_url = $widget->get_data( 'url', 'view' );
		if ( empty( $video_url ) ) {
			return '';
		}

		$youtube_id = trim( $this->getYoutubeIdFromUrl( $video_url ) );
		if ( ! $youtube_id ) {
			return '';
		}

		$full_embed_url = 'https://www.youtube.com/embed/' . $youtube_id;
		$wh_attr        = 'width="560" height="315"';

		$fluid_width      = $widget->get_data( 'fluid_width', 'view' );
		$full_width_class = ! empty( $fluid_width ) && 'yes' === $fluid_width ? 'fr-full-width' : '';

		$yt_attr = '?disablekb=1&rel=0';

		$html = '<div class="youtube-video-container ' . $full_width_class . '"><iframe ' . $wh_attr . ' style="max-width: 100%" type="text/html" src="' . esc_attr( $full_embed_url ) . esc_url( $yt_attr ) . '" frameborder="0" allowfullscreen></iframe></div>';
		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start( $widget ) . $html . $this->output_end( $widget ), $this );
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
