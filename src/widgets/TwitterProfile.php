<?php
/**
 * Twitter profile feed widget.
 *
 * @package FrontPage Buddy
 * @since 1.0.0
 */

namespace RecycleBin\FrontPageBuddy\Widgets;

defined( 'ABSPATH' ) ? '' : exit();

/**
 *  Embed twitter feed.
 */
class TwitterProfile extends Widget {
	/**
	 * Constructor.
	 *
	 * @param string $type A unique identifier.
	 * @param mixed  $args Initial data for the widget. e.g: id, options etc.
	 */
	public function __construct( $type, $args = '' ) {
		$this->type           = $type;
		$this->name           = __( 'Twitter Profile Feed', 'frontpage-buddy' );
		$this->description    = __( 'Display any X/Twitter profile\'s feed.', 'frontpage-buddy' );
		$this->icon_image_url = FPBUDDY_PLUGIN_URL . 'assets/images/icon-x-feed.png';

		$this->setup( $args );
	}

	/**
	 * Get the fields for setting up this widget.
	 *
	 * @return array
	 */
	public function get_fields() {
		$attrs_dark_theme = array();
		if ( 'yes' == $this->edit_field_value( 'dark_theme' ) ) {
			$attrs_dark_theme['checked'] = 'checked';
		}
		return array(
			'username'   => array(
				'type'        => 'text',
				'label'       => __( 'X/Twitter Handle', 'frontpage-buddy' ),
				'value'       => ! empty( $this->edit_field_value( 'username' ) ) ? $this->edit_field_value( 'username' ) : '',
				'attributes'  => array( 'placeholder' => __( 'E.g: @johndoe', 'frontpage-buddy' ) ),
				'is_required' => true,
			),
			'width'      => array(
				'type'       => 'number',
				'label'      => __( 'Width', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'twidth' ) ) ? $this->edit_field_value( 'twidth' ) : '',
				'attributes' => array( 'placeholder' => __( 'Width in pixels (optional)', 'frontpage-buddy' ) ),
			),
			'height'     => array(
				'type'       => 'number',
				'label'      => __( 'Height', 'frontpage-buddy' ),
				'value'      => ! empty( $this->edit_field_value( 'theight' ) ) ? $this->edit_field_value( 'theight' ) : '',
				'attributes' => array( 'placeholder' => __( 'Height in pixels (optional)', 'frontpage-buddy' ) ),
			),
			'dark_theme' => array(
				'type'       => 'switch',
				'label'      => __( 'Use dark theme', 'frontpage-buddy' ),
				'label_off'  => __( 'No', 'frontpage-buddy' ),
				'label_on'   => __( 'Yes', 'frontpage-buddy' ),
				'attributes' => $attrs_dark_theme,
			),
		);
	}

	/**
	 * Get the html output for this widget.
	 *
	 * @return string
	 */
	public function get_output() {
		$twitter_id = $this->view_field_val( 'username' );
		if ( empty( $twitter_id ) ) {
			return '';
		}
		$twitter_id = trim( $twitter_id, ' /@' );
		if ( empty( $twitter_id ) ) {
			return '';
		}

		$profile_url = 'https://twitter.com/' . $twitter_id;
		$width       = (int) $this->view_field_val( 'width' );
		if ( $width < 100 ) {
			$width = 500;
		}

		$height = (int) $this->view_field_val( 'height' );
		if ( $height < 100 ) {
			$height = 800;
		}

		$theme = 'yes' === $this->view_field_val( 'dark_theme' ) ? 'dark' : '';

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

		return $html;
	}
}
