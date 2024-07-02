<?php
/**
 * Facebook page embed widget.
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
	/**
	 * Constructor.
	 *
	 * @param string $type A unique identifier.
	 * @param mixed  $args Initial data for the widget. e.g: id, options etc.
	 */
	public function __construct( $type, $args = '' ) {
		$this->type           = $type;
		$this->name           = __( 'Facebook Page', 'frontpage-buddy' );
		$this->description    = __( 'Embed and promote any Facebook Page.', 'frontpage-buddy' );
		$this->icon_image_url = FPBUDDY_PLUGIN_URL . 'assets/images/icon-fb-pages.png';

		$this->setup( $args );
	}

	/**
	 * Get the html output for this widget.
	 *
	 * @return string
	 */
	public function get_output() {
		$fp_page_url = $this->view_field_val( 'url' );
		$fp_page_url = trim( $fp_page_url, ' /' );
		if ( empty( $fp_page_url ) ) {
			return '';
		}

		$use_small_header = $this->view_field_val( 'smallheader' );
		$use_small_header = ! empty( $use_small_header ) && in_array( 'yes', $use_small_header, true ) ? 'true' : '';
		$showposts        = $this->view_field_val( 'showposts' );
		$showposts        = ! empty( $showposts ) && in_array( 'yes', $showposts, true ) ? 'true' : '';
		$hidecover        = $this->view_field_val( 'hidecover' );
		$hidecover        = ! empty( $hidecover ) && in_array( 'yes', $hidecover, true ) ? 'true' : '';

		$html  = '<div id="fb-root"></div>';
		$html .= '<div class="fb-page" ';
		$html .= 'data-href="' . esc_attr( $fp_page_url ) . '" ';

		$html .= 'data-small-header="' . esc_attr( $use_small_header ) . '" ';
		$html .= 'data-hide-cover="' . esc_attr( $hidecover ) . '" ';
		$html .= 'data-show-facepile="" ';
		$html .= 'data-show-posts="' . esc_attr( $showposts ) . '" ';

		$html .= 'data-adapt-container-width="1" ';
		$html .= 'data-width="600"';
		$html .= 'data-height="500" >';

		$html .= '<blockquote cite="' . esc_attr( $fp_page_url ) . '" class="fb-xfbml-parse-ignore">';
		$html .= '<a href="' . esc_attr( $fp_page_url ) . '"></a>';
		$html .= '</blockquote>';
		$html .= '</div>';

		wp_enqueue_script( 'facebook-sdk', 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v20.0', array(), '20.0', array( 'in_footer' => true ) );

		return $html;
	}

	/**
	 * Get the fields for setting up this widget.
	 *
	 * @return array
	 */
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
			'showposts'   => array(
				'type'    => 'checkbox',
				'label'   => '',
				'value'   => ! empty( $this->edit_field_value( 'showposts' ) ) ? $this->edit_field_value( 'showposts' ) : '',
				'options' => array( 'yes' => __( 'Show Recent Posts', 'frontpage-buddy' ) ),
			),
		);
	}
}
