<?php
/**
 * Facebook page embed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Embed facebook page widget.
 */
class FacebookPage extends WidgetType {
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->type        = 'facebookpage';
		$this->name        = __( 'Facebook Page', 'frontpage-buddy' );
		$this->description = __( 'Embed and promote any Facebook Page.', 'frontpage-buddy' );
		$this->icon_image  = '<i class="gg-facebook"></i>';

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

		$fields['url'] = array(
			'type'       => 'url',
			'label'      => __( 'Facebook Page URL', 'frontpage-buddy' ),
			'value'      => ! empty( $widget->get_data( 'url', 'edit' ) ) ? $widget->get_data( 'url', 'edit' ) : '',
			'attributes' => array( 'placeholder' => __( 'The url of the facebook page', 'frontpage-buddy' ) ),
		);

		$fields['smallheader'] = array(
			'type'    => 'checkbox',
			'label'   => '',
			'value'   => ! empty( $widget->get_data( 'smallheader', 'edit' ) ) ? $widget->get_data( 'smallheader', 'edit' ) : '',
			'options' => array( 'yes' => __( 'Use Small Header', 'frontpage-buddy' ) ),
		);

		$fields['hidecover'] = array(
			'type'    => 'checkbox',
			'label'   => '',
			'value'   => ! empty( $widget->get_data( 'hidecover', 'edit' ) ) ? $widget->get_data( 'hidecover', 'edit' ) : '',
			'options' => array( 'yes' => __( 'Hide Cover Photo', 'frontpage-buddy' ) ),
		);

		$fields['showposts'] = array(
			'type'    => 'checkbox',
			'label'   => '',
			'value'   => ! empty( $widget->get_data( 'showposts', 'edit' ) ) ? $widget->get_data( 'showposts', 'edit' ) : '',
			'options' => array( 'yes' => __( 'Show Recent Posts', 'frontpage-buddy' ) ),
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
		$fp_page_url = $widget->get_data( 'url', 'view' );
		$fp_page_url = trim( $fp_page_url, ' /' );
		if ( empty( $fp_page_url ) ) {
			return '';
		}

		$use_small_header = $widget->get_data( 'smallheader', 'view' );
		$use_small_header = ! empty( $use_small_header ) && in_array( 'yes', $use_small_header, true ) ? 'true' : '';
		$showposts        = $widget->get_data( 'showposts', 'view' );
		$showposts        = ! empty( $showposts ) && in_array( 'yes', $showposts, true ) ? 'true' : '';
		$hidecover        = $widget->get_data( 'hidecover', 'view' );
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

		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start( $widget ) . $html . $this->output_end( $widget ), $this, $widget );
	}
}
