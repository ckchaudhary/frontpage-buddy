<?php
/**
 * Facebook page embed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) || exit;

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

		$this->description_admin = '<p>' . esc_html__( 'Enables your users to embed a facebook page.', 'frontpage-buddy' ) . '</p>';

		$this->description_admin .= '<div class="notice notice-warning inline">';
		$this->description_admin .= '<p><strong>' . esc_html__( 'Use of 3rd party service.', 'frontpage-buddy' ) . '</strong></p><hr>';
		$this->description_admin .= esc_html__( 'This widget makes use of an external API which may track your website visitor\'s data and may add cookies on their devices.', 'frontpage-buddy' ) . ' ';
		$this->description_admin .= esc_html__( 'Please update your privacy and cookie policies accordingly. It befalls on you ( the website administrator ) to collect opt-in consent beforehand.', 'frontpage-buddy' );

		$this->description_admin .= '<p>';

		$this->description_admin .= '<strong>' . esc_html__( 'Data Usage', 'frontpage-buddy' ) . ': </strong>';
		$this->description_admin .= esc_html__( 'This widget uses the Facebook iframe API to fetch and display publicly available Facebook content. No personal user data is stored or transmitted by the plugin.', 'frontpage-buddy' );
		$this->description_admin .= '<br>';

		$this->description_admin .= '<strong>' . esc_html__( 'Privacy Note', 'frontpage-buddy' ) . ': </strong>';
		$this->description_admin .= sprintf(
			/* translators: 1: link to https://www.facebook.com/privacy/policy */
			esc_html__( 'Facebook may collect information such as IP addresses and user interaction data when the feed is displayed. For details, refer to %s.', 'frontpage-buddy' ),
			'<a href="https://www.facebook.com/privacy/policy">' . esc_html__( 'Facebook\'s Privacy Policy', 'frontpage-buddy' ) . '</a>'
		);

		$this->description_admin .= '</p>';

		$this->description_admin .= '<p>' . esc_html__( 'If you have concerns, you should keep this widget disabled.', 'frontpage-buddy' ) . '</p>';
		$this->description_admin .= '</div>';

		parent::__construct();
	}

	/**
	 * Get all the data 'fields' for the settings/options screen for this widget.
	 *
	 * @param \FrontPageBuddy\Widgets\Widget $widget The current widget object.
	 *
	 * @return array
	 */
	public function get_data_fields( $widget ) {
		$fields = $this->get_default_data_fields( $widget );

		$fields['url'] = array(
			'type'        => 'url',
			'label'       => __( 'Facebook Page URL', 'frontpage-buddy' ),
			'value'       => ! empty( $widget->get_data( 'url', 'edit' ) ) ? $widget->get_data( 'url', 'edit' ) : '',
			'attributes'  => array( 'placeholder' => __( 'The url of the facebook page', 'frontpage-buddy' ) ),
			'is_required' => true,
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

		return $fields;
	}

	/**
	 * Get the html output for this widget.
	 *
	 * @param \FrontPageBuddy\Widgets\Widget $widget The current widget object.
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
		$hidecover        = $widget->get_data( 'hidecover', 'view' );
		$hidecover        = ! empty( $hidecover ) && in_array( 'yes', $hidecover, true ) ? 'true' : '';

		$html = '
		<iframe 
			src="https://www.facebook.com/plugins/page.php?href=' . rawurlencode( $fp_page_url ) . '&tabs=timeline&small_header=' . esc_attr( $use_small_header ) . '&height=500&adapt_container_width=true&hide_cover=' . esc_attr( $hidecover ) . '&show_facepile=false"
			width="100%" 
			height="500" 
			style="border:none;overflow:hidden" 
			scrolling="no" 
			frameborder="0" 
			allowfullscreen="true" 
			allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
		</iframe>';

		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start( $widget ) . $html . $this->output_end( $widget ), $this, $widget );
	}
}
