<?php
/**
 * Twitter profile feed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RB\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) || exit;

/**
 *  Embed twitter feed.
 */
class TwitterProfile extends WidgetType {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->type        = 'twitterprofile';
		$this->name        = __( 'Twitter Profile Feed', 'frontpage-buddy' );
		$this->description = __( 'Display any X/Twitter profile\'s feed.', 'frontpage-buddy' );
		$this->icon_image  = '<i class="gg-twitter"></i>';

		$this->description_admin  = '<p>' . esc_html__( 'Enables your users display an X/Twitter profile\'s feed.', 'frontpage-buddy' ) . '</p>';
		$this->description_admin .= '<br>';

		$this->description_admin .= '<div class="notice notice-warning inline">';
		$this->description_admin .= '</p><strong>' . esc_html__( 'Use of 3rd party service.', 'frontpage-buddy' ) . '</strong><hr>';
		$this->description_admin .= esc_html__( 'This widget makes use of an external API which may track your website visitor\'s data and may add cookies on their devices.', 'frontpage-buddy' ) . ' ';
		$this->description_admin .= esc_html__( 'Please update your privacy and cookie policies accordingly. It befalls on you ( the website administrator ) to collect opt-in consent beforehand.', 'frontpage-buddy' );

		$this->description_admin .= '</p><p>';

		$this->description_admin .= '<strong>' . esc_html__( 'Data Usage', 'frontpage-buddy' ) . ': </strong>';
		$this->description_admin .= esc_html__( 'The "https://platform.twitter.com/widgets.js" script fetches publicly available Twitter content for display. No personal user data is collected or stored by the plugin.', 'frontpage-buddy' );
		$this->description_admin .= '<br>';

		$this->description_admin .= '<strong>' . esc_html__( 'Privacy Note', 'frontpage-buddy' ) . ': </strong>';
		$this->description_admin .= sprintf(
			/* translators: 1: Link to Twitter's privacy policy */
			esc_html__( 'When embedding Twitter content, Twitter may collect data such as IP addresses, browser details, and interaction metrics. For more information, refer to %s.', 'frontpage-buddy' ),
			'<a href="https://twitter.com/en/privacy">' . esc_html__( 'Twitter\'s Privacy Policy', 'frontpage-buddy' ) . '</a>'
		);

		$this->description_admin .= '</p>';

		$this->description_admin .= '<p>' . esc_html__( 'If you have concerns, you should keep this widget disabled.', 'frontpage-buddy' ) . '</p>';
		$this->description_admin .= '</div>';

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

		$attrs_dark_theme = array();
		if ( 'yes' === $widget->get_data( 'dark_theme', 'edit' ) ) {
			$attrs_dark_theme['checked'] = 'checked';
		}
		$fields['username']   = array(
			'type'        => 'text',
			'label'       => __( 'X/Twitter Handle', 'frontpage-buddy' ),
			'value'       => ! empty( $widget->get_data( 'username', 'edit' ) ) ? $widget->get_data( 'username', 'edit' ) : '',
			'attributes'  => array( 'placeholder' => __( 'E.g: @johndoe', 'frontpage-buddy' ) ),
			'is_required' => true,
		);
		$fields['width']      = array(
			'type'       => 'number',
			'label'      => __( 'Width', 'frontpage-buddy' ),
			'value'      => ! empty( $widget->get_data( 'twidth', 'edit' ) ) ? $widget->get_data( 'twidth', 'edit' ) : '',
			'attributes' => array( 'placeholder' => __( 'Width in pixels (optional)', 'frontpage-buddy' ) ),
		);
		$fields['height']     = array(
			'type'       => 'number',
			'label'      => __( 'Height', 'frontpage-buddy' ),
			'value'      => ! empty( $widget->get_data( 'theight', 'edit' ) ) ? $widget->get_data( 'theight', 'edit' ) : '',
			'attributes' => array( 'placeholder' => __( 'Height in pixels (optional)', 'frontpage-buddy' ) ),
		);
		$fields['dark_theme'] = array(
			'type'       => 'switch',
			'label'      => __( 'Use dark theme', 'frontpage-buddy' ),
			'label_off'  => __( 'No', 'frontpage-buddy' ),
			'label_on'   => __( 'Yes', 'frontpage-buddy' ),
			'attributes' => $attrs_dark_theme,
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
		$twitter_id = $widget->get_data( 'username', 'view' );
		if ( empty( $twitter_id ) ) {
			return '';
		}
		$twitter_id = trim( $twitter_id, ' /@' );
		if ( empty( $twitter_id ) ) {
			return '';
		}

		$profile_url = 'https://twitter.com/' . $twitter_id;
		$width       = (int) $widget->get_data( 'width', 'view' );
		if ( $width < 100 ) {
			$width = 500;
		}

		$height = (int) $widget->get_data( 'height', 'view' );
		if ( $height < 100 ) {
			$height = 800;
		}

		$theme = 'yes' === $widget->get_data( 'dark_theme', 'view' ) ? 'dark' : '';

		$html  = '<div align="center">';
		$html .= sprintf(
			'<a class="twitter-timeline" data-height="%1$d" data-width="%2$d" data-dnt="true" data-theme="%3$s" href="%4$s"></a>',
			$height,
			$width,
			$theme,
			esc_attr( $profile_url )
		);
		$html .= '</div>';

		wp_enqueue_script( 'twitter-widget', 'https://platform.twitter.com/widgets.js', array(), '1.0', array( 'in_footer' => true ) );

		return apply_filters( 'frontpage_buddy_widget_output', $this->output_start( $widget ) . $html . $this->output_end( $widget ), $this );
	}
}
