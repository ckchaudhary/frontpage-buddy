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
 *  Embed facebook page widget.
 */
class FacebookPage extends Widget {
	public function __construct( $args = '' ) {
		$this->type           = 'facebookpageembed';
		$this->name           = __( 'Facebook Page', 'frontpage-buddy' );
		$this->description    = __( 'Embed and promote any Facebook Page.', 'frontpage-buddy' );
		$this->icon_image_url = FPBUDDY_PLUGIN_URL . 'assets/images/icon-fb-pages.png';

		$this->setup( $args );
	}

	public function get_output() {
		$fp_page_url = $this->view_field_val( 'url' );
		$fp_page_url = trim( $fp_page_url, ' /' );
		if ( empty( $fp_page_url ) ) {
			return '';
		}

		wp_enqueue_script( 'facebook-sdk', 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v20.0', array(), '20.0' );

		$html  = '<div id="fb-root"></div>';
		$html .= '<div class="fb-page" ';
		$html .= 'data-href="' . esc_attr( $fp_page_url ) . '" ';
		$html .= 'data-height="500" ';
		$html .= 'data-small-header="" ';
		$html .= 'data-adapt-container-width="1" ';
		$html .= 'data-hide-cover="" ';
		$html .= 'data-show-facepile="true" ';
		$html .= 'data-show-posts="true" ';
		$html .= 'data-width="600">';
		$html .= '<blockquote cite="' . esc_attr( $fp_page_url ) . '" class="fb-xfbml-parse-ignore">';
		$html .= '<a href="' . esc_attr( $fp_page_url ) . '"></a>';
		$html .= '</blockquote>';
		$html .= '</div>';

		return $html;
	}

	public function get_fields() {
		return array(
			'url'         => array(
				'type'       => 'url',
				'label'      => __( 'Facebook Page URL', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'url' ) ) ? $this->edit_field_value( 'url' ) : '',
				'attributes' => array( 'placeholder' => __( 'The url of the facebook page', 'frontpage-buddy' ) ),
			),
			'smallheader' => array(
				'type'    => 'checkbox',
				'label'   => '',
				'value'   => ! empty( $this->edit_field_value( 'smallheader' ) ) ? $this->edit_field_value( 'smallheader' ) : '',
				'options' => array( 'yes' => __( 'Use Small Header', 'frontpage-buddy' ) ),
			),
			'hidecover'   => array(
				'type'    => 'checkbox',
				'label'   => '',
				'value'   => ! empty( $this->edit_field_value( 'hidecover' ) ) ? $this->edit_field_value( 'hidecover' ) : '',
				'options' => array( 'yes' => __( 'Hide Cover Photo', 'frontpage-buddy' ) ),
			),
			'facepile'    => array(
				'type'    => 'checkbox',
				'label'   => '',
				'value'   => ! empty( $this->edit_field_value( 'facepile' ) ) ? $this->edit_field_value( 'facepile' ) : '',
				'options' => array( 'yes' => __( 'Show Friend\'s Faces', 'frontpage-buddy' ) ),
			),
		);
	}
}

/*
<div id="fb-root"></div>
<script async="1" defer="1" crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v20.0" nonce="S74E1sWu"></script>
<div class="fb-page" 
	data-href="https://www.facebook.com/natgeo" 
	data-height="500" 
	data-small-header="" 
	data-adapt-container-width="1" 
	data-hide-cover="" 
	data-show-facepile="true" 
	data-show-posts="true" 
	data-width="600">
	<blockquote cite="https://www.facebook.com/natgeo" class="fb-xfbml-parse-ignore">
		<a href="https://www.facebook.com/natgeo">National Geographic</a>
	</blockquote>
</div>
*/
